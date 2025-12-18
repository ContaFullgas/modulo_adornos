<?php
require_once __DIR__ . '/../../config/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

function json_out(bool $ok, string $msg = '', array $data = []): void {
    echo json_encode([
        'ok' => $ok,
        'message' => $msg,
        'data' => $data
    ]);
    exit;
}

$user = current_user();
if (!$user) json_out(false, 'No autenticado.');

global $conn;
if (!isset($conn)) json_out(false, 'Conexión a DB no disponible.');

$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$dept_id = isset($_POST['dept_id']) ? (int)$_POST['dept_id'] : 0;
$qty     = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$notes   = isset($_POST['notes']) ? trim((string)$_POST['notes']) : '';

if ($item_id <= 0) json_out(false, 'Item inválido.');
if ($qty <= 0) json_out(false, 'Cantidad inválida.');

// Si es usuario normal y tiene dept asignado, forzar su dept
$isUser = (isset($user['role']) && $user['role'] === 'usuario');
if ($isUser && !empty($user['department_id'])) {
    $dept_id = (int)$user['department_id'];
}

if ($dept_id <= 0) json_out(false, 'Departamento inválido.');

try {
    $conn->begin_transaction();

    // Bloquear item para evitar carreras
    $stmt = $conn->prepare("SELECT id, total_quantity, available_quantity FROM items WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$item) {
        $conn->rollback();
        json_out(false, 'El artículo no existe.');
    }

    $available = (int)$item['available_quantity'];
    if ($qty > $available) {
        $conn->rollback();
        json_out(false, "Stock insuficiente. Disponible: {$available}");
    }

    // Insert reserva
    $status = 'Reservado';
    $user_id = (int)$user['id'];

    $stmt = $conn->prepare("
        INSERT INTO reservations (item_id, dept_id, user_id, quantity, notes, status, reserved_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiiiss", $item_id, $dept_id, $user_id, $qty, $notes, $status);
    $stmt->execute();
    $stmt->close();

    // Actualizar stock
    $stmt = $conn->prepare("UPDATE items SET available_quantity = available_quantity - ? WHERE id = ?");
    $stmt->bind_param("ii", $qty, $item_id);
    $stmt->execute();
    $stmt->close();

    // Item actualizado
    $stmt = $conn->prepare("SELECT id, total_quantity, available_quantity FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $updated = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Apartado por (por departamento)
    $stmt = $conn->prepare("
        SELECT d.name AS dept_name, SUM(r.quantity) AS qty
        FROM reservations r
        JOIN departments d ON d.id = r.dept_id
        WHERE r.item_id = ? AND LOWER(r.status) = 'reservado'
        GROUP BY d.id, d.name
        ORDER BY d.name
    ");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $rs = $stmt->get_result();
    $reserved_by = [];
    while ($row = $rs->fetch_assoc()) {
        $reserved_by[] = [
            'dept_name' => $row['dept_name'],
            'qty' => (int)$row['qty'],
        ];
    }
    $stmt->close();

    $conn->commit();

    json_out(true, 'Reservado correctamente.', [
        'item_id' => (int)$updated['id'],
        'total_quantity' => (int)$updated['total_quantity'],
        'available_quantity' => (int)$updated['available_quantity'],
        'reserved_by' => $reserved_by
    ]);

} catch (Throwable $e) {
    if (isset($conn) && $conn->errno) $conn->rollback();
    json_out(false, 'Error al reservar: ' . $e->getMessage());
}
