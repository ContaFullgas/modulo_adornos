<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: reservations.php");
    exit;
}

$reservation_id = (int)($_POST['reservation_id'] ?? 0);
$item_id = (int)($_POST['item_id'] ?? 0);
$dept_id = (int)($_POST['dept_id'] ?? 0);
$qty = max(1, (int)($_POST['quantity'] ?? 0));

if ($reservation_id <= 0 || $item_id <= 0 || $qty <= 0) {
    header("Location: reservations.php");
    exit;
}

// Verificar permisos: admin puede devolver cualquier reserva, usuario solo las de su departamento
$user = current_user();
if ($user['role'] !== 'admin') {
    $user_dept_id = (int)($user['department_id'] ?? 0);
    if ($user_dept_id <= 0 || $user_dept_id !== $dept_id) {
        http_response_code(403);
        echo "No tienes permisos para devolver esta reserva.";
        exit;
    }
}

// 1) Comprobar existencia y estado de la reserva
$stmt = $conn->prepare("SELECT id, status, quantity, item_id, dept_id FROM reservations WHERE id = ?");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$resRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$resRow) {
    header("Location: reservations.php");
    exit;
}

// Verificar nuevamente los permisos a nivel de base de datos
if ($user['role'] !== 'admin') {
    $res_dept_id = (int)($resRow['dept_id'] ?? 0);
    if ($res_dept_id !== $user_dept_id) {
        http_response_code(403);
        echo "No tienes permisos para devolver esta reserva.";
        exit;
    }
}

$current_status = strtolower($resRow['status'] ?? '');
if ($current_status === 'devuelto') {
    header("Location: reservations.php");
    exit;
}

// 2) Realizar la devolución en transacción
$conn->begin_transaction();

try {
    // insertar en tabla returns
    $handled_by = (int)($_SESSION['user_id'] ?? 0);
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');
    $stmt = $conn->prepare("INSERT INTO returns (reservation_id, item_id, dept_id, quantity, notes, handled_by, returned_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiisi", $reservation_id, $item_id, $dept_id, $qty, $notes, $handled_by);
    if (!$stmt->execute()) throw new Exception("Error al insertar devolución: " . $stmt->error);
    $stmt->close();

    // actualizar cantidad disponible del item
    $stmt = $conn->prepare("UPDATE items SET available_quantity = available_quantity + ? WHERE id = ?");
    $stmt->bind_param("ii", $qty, $item_id);
    if (!$stmt->execute()) throw new Exception("Error al actualizar item: " . $stmt->error);
    $stmt->close();

    // marcar la reserva como 'devuelto'
    $stmt = $conn->prepare("UPDATE reservations SET status = 'devuelto', returned_at = NOW() WHERE id = ? AND status <> 'devuelto'");
    $stmt->bind_param("i", $reservation_id);
    if (!$stmt->execute()) throw new Exception("Error al actualizar reserva: " . $stmt->error);
    $stmt->close();

    $conn->commit();
    header("Location: reservations.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("process_return error: " . $e->getMessage());
    echo "<p>Error procesando devolución. Contacta al administrador.</p>";
    echo '<p><a href="reservations.php">Volver</a></p>';
    exit;
}
?>
