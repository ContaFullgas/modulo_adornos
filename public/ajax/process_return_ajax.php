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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(false, 'Método no permitido.');
}

$res_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
$new_status = isset($_POST['new_status']) ? trim((string)$_POST['new_status']) : '';

if ($res_id <= 0 || $new_status === '') {
    json_out(false, 'Datos inválidos.');
}

// Normaliza compatibilidad: devuelto => finalizado
if ($new_status === 'devuelto') $new_status = 'finalizado';

$current_role = $user['role'] ?? '';
$current_dept = isset($user['department_id']) ? (int)$user['department_id'] : 0;

try {
    // Bloqueo de la reserva para evitar duplicar devoluciones/stock
    $conn->begin_transaction();

    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $res_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$res) {
        $conn->rollback();
        json_out(false, 'Reserva no encontrada.');
    }

    $res_status = strtolower((string)$res['status']);
    $res_dept   = (int)$res['dept_id'];
    $item_id    = (int)$res['item_id'];
    $qty        = (int)$res['quantity'];

    // =========================
    // CASO 1: usuario/admin solicita devolución (reservado -> en_proceso)
    // =========================
    if ($new_status === 'en_proceso') {

        // Permisos: admin o dueño del dept
        if ($current_role !== 'admin' && $current_dept !== $res_dept) {
            $conn->rollback();
            http_response_code(403);
            json_out(false, 'No tienes permiso para modificar esta reserva.');
        }

        // Cambiar estado
        $upd = $conn->prepare("UPDATE reservations SET status = 'en_proceso' WHERE id = ?");
        $upd->bind_param("i", $res_id);
        if (!$upd->execute()) {
            $err = $conn->error;
            $upd->close();
            $conn->rollback();
            json_out(false, 'Error al actualizar: ' . $err);
        }
        $upd->close();

        $conn->commit();

        json_out(true, 'Devolución solicitada correctamente.', [
            'reservation_id' => $res_id,
            'status' => 'en_proceso'
        ]);
    }

    // =========================
    // CASO 2: admin confirma recepción (en_proceso / reservado -> finalizado)
    // => devuelve stock + inserta returns + set returned_at + EVALÚA CONDICIÓN
    // =========================
    if ($new_status === 'finalizado') {

        if ($current_role !== 'admin') {
            $conn->rollback();
            http_response_code(403);
            json_out(false, 'Solo el administrador puede confirmar recepciones.');
        }

        // Evitar duplicar stock
        if ($res_status === 'finalizado' || $res_status === 'devuelto') {
            $conn->rollback();
            json_out(false, 'Esta reserva ya fue finalizada anteriormente.');
        }

        // **NUEVOS CAMPOS DE CONDICIÓN**
        $return_condition = isset($_POST['return_condition']) ? trim($_POST['return_condition']) : '';
        $condition_notes = isset($_POST['condition_notes']) ? trim($_POST['condition_notes']) : null;

        // Validar que se haya seleccionado una condición
        $valid_conditions = ['buen_estado', 'roto', 'incompleto'];
        if (!in_array($return_condition, $valid_conditions)) {
            $conn->rollback();
            json_out(false, 'Debe seleccionar el estado de los artículos devueltos.');
        }

        // Si está roto o incompleto, las notas son obligatorias
        if (($return_condition === 'roto' || $return_condition === 'incompleto') && empty($condition_notes)) {
            $conn->rollback();
            json_out(false, 'Debe especificar qué está roto o qué falta en los comentarios.');
        }

        // Si las notas están vacías, usar NULL
        if (empty($condition_notes)) {
            $condition_notes = null;
        }

        // 1) actualizar reserva + returned_at
        $updRes = $conn->prepare("UPDATE reservations SET status = 'finalizado', returned_at = NOW() WHERE id = ?");
        $updRes->bind_param("i", $res_id);
        if (!$updRes->execute()) {
            $err = $conn->error;
            $updRes->close();
            $conn->rollback();
            json_out(false, 'Error al actualizar reserva: ' . $err);
        }
        $updRes->close();

        // 2) devolver stock al item
        $updItem = $conn->prepare("UPDATE items SET available_quantity = available_quantity + ? WHERE id = ?");
        $updItem->bind_param("ii", $qty, $item_id);
        if (!$updItem->execute()) {
            $err = $conn->error;
            $updItem->close();
            $conn->rollback();
            json_out(false, 'Error al devolver stock: ' . $err);
        }
        $updItem->close();

        // 3) registrar en historial returns CON CONDICIÓN
        $admin_id = (int)($user['id'] ?? 0);

        $insHist = $conn->prepare("
            INSERT INTO returns
            (reservation_id, item_id, dept_id, quantity, return_condition, condition_notes, returned_at, handled_by)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $insHist->bind_param("iiiissi", $res_id, $item_id, $res_dept, $qty, $return_condition, $condition_notes, $admin_id);
        if (!$insHist->execute()) {
            $err = $conn->error;
            $insHist->close();
            $conn->rollback();
            json_out(false, 'Error al insertar historial: ' . $err);
        }
        $insHist->close();

        // leer returned_at y available_quantity actuales para UI
        $stmt = $conn->prepare("SELECT returned_at FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $res_id);
        $stmt->execute();
        $r2 = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("SELECT available_quantity FROM items WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $i2 = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $conn->commit();

        // Mensaje personalizado según condición
        $msg = 'Devolución confirmada y stock actualizado.';
        if ($return_condition === 'roto') {
            $msg = 'Devolución confirmada. ⚠️ Artículos reportados como rotos/dañados.';
        } elseif ($return_condition === 'incompleto') {
            $msg = 'Devolución confirmada. ⚠️ Devolución incompleta registrada.';
        } else {
            $msg = 'Devolución confirmada. ✅ Artículos en buen estado.';
        }

        json_out(true, $msg, [
            'reservation_id' => $res_id,
            'status' => 'finalizado',
            'returned_at' => $r2['returned_at'] ?? null,
            'item_id' => $item_id,
            'available_quantity' => isset($i2['available_quantity']) ? (int)$i2['available_quantity'] : null,
            'return_condition' => $return_condition,
            'condition_notes' => $condition_notes
        ]);
    }

    // =========================
    // CASO 3: revertir a reservado (solo admin)
    // (opcional: limpiar returned_at)
    // =========================
    if ($new_status === 'reservado') {

        if ($current_role !== 'admin') {
            $conn->rollback();
            http_response_code(403);
            json_out(false, 'Solo admin puede revertir el estado.');
        }

        $upd = $conn->prepare("UPDATE reservations SET status = 'reservado', returned_at = NULL WHERE id = ?");
        $upd->bind_param("i", $res_id);
        if (!$upd->execute()) {
            $err = $conn->error;
            $upd->close();
            $conn->rollback();
            json_out(false, 'Error al revertir: ' . $err);
        }
        $upd->close();

        $conn->commit();

        json_out(true, 'Estado revertido a reservado.', [
            'reservation_id' => $res_id,
            'status' => 'reservado'
        ]);
    }

    $conn->rollback();
    json_out(false, 'Estado inválido.');

} catch (Throwable $e) {
    if (isset($conn)) $conn->rollback();
    json_out(false, 'Error: ' . $e->getMessage());
}