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

if (!isset($user['role']) || $user['role'] !== 'admin') {
    http_response_code(403);
    json_out(false, 'Acceso denegado (solo admin).');
}

global $conn;
if (!isset($conn)) json_out(false, 'Conexión a DB no disponible.');

$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(false, 'Método no permitido.');
}

$uploadsDir = __DIR__ . '/../uploads'; // public/uploads
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}

/**
 * EDITAR
 */
if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $code = isset($_POST['code']) ? strtoupper(trim((string)$_POST['code'])) : '';
    $description = (string)($_POST['description'] ?? '');
    $new_total = max(1, (int)($_POST['total_quantity'] ?? 1));
    $existing_image = (string)($_POST['existing_image'] ?? '');

    $celebration_id = (isset($_POST['celebration_id']) && $_POST['celebration_id'] !== '')
        ? (int)$_POST['celebration_id']
        : null;

    if ($id <= 0 || $code === '') {
        json_out(false, 'Datos inválidos.');
    }

    try {
        $conn->begin_transaction();

        // Unicidad de código (excepto el mismo id)
        $stmt = $conn->prepare("SELECT id FROM items WHERE code = ? AND id <> ?");
        $stmt->bind_param("si", $code, $id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $conn->rollback();
            json_out(false, 'El código ya existe para otro adorno.');
        }

        // Valores actuales
        $stmt = $conn->prepare("SELECT total_quantity, available_quantity, image FROM items WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cur = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$cur) {
            $conn->rollback();
            json_out(false, 'El adorno no existe.');
        }

        $cur_total = (int)$cur['total_quantity'];
        $cur_available = (int)$cur['available_quantity'];
        $cur_image = (string)$cur['image'];

        // reservados = total - disponibles
        $reserved = $cur_total - $cur_available;
        if ($reserved < 0) $reserved = 0;

        // nueva available
        $new_available = $new_total - $reserved;
        if ($new_available < 0) $new_available = 0;

        // Imagen: si suben nueva, reemplazar y borrar anterior
        $new_image_name = $cur_image;

        if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $orig = basename((string)$_FILES['image']['name']);
            $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', $orig);
            $new_image_name = time() . "_" . $safe;

            $target = $uploadsDir . "/" . $new_image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $conn->rollback();
                json_out(false, 'No se pudo subir la nueva imagen.');
            }

            // borrar imagen anterior
            if (!empty($cur_image) && $cur_image !== $new_image_name) {
                $old = $uploadsDir . "/" . $cur_image;
                if (file_exists($old)) @unlink($old);
            }
        } else {
            // si no sube nueva, conserva la existente (y/o la que venga en hidden)
            if (!$new_image_name && $existing_image) {
                $new_image_name = $existing_image;
            }
        }

        // Update (celebration_id puede ser NULL)
        if ($celebration_id === null) {
            $stmt = $conn->prepare("
                UPDATE items
                SET code = ?, description = ?, total_quantity = ?, available_quantity = ?, image = ?, celebration_id = NULL
                WHERE id = ?
            ");
            $stmt->bind_param("ssiisi", $code, $description, $new_total, $new_available, $new_image_name, $id);
        } else {
            $stmt = $conn->prepare("
                UPDATE items
                SET code = ?, description = ?, total_quantity = ?, available_quantity = ?, image = ?, celebration_id = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssiisii", $code, $description, $new_total, $new_available, $new_image_name, $celebration_id, $id);
        }

        if (!$stmt->execute()) {
            $err = $conn->error;
            $stmt->close();
            $conn->rollback();
            json_out(false, 'Error al actualizar: ' . $err);
        }
        $stmt->close();

        // Nombre de celebración (para badge)
        $celebration_name = '';
        if ($celebration_id !== null && $celebration_id > 0) {
            $stmt = $conn->prepare("SELECT name FROM celebrations WHERE id = ?");
            $stmt->bind_param("i", $celebration_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $celebration_name = $row['name'] ?? '';
        }

        $conn->commit();

        json_out(true, 'Actualizado.', [
            'item_id' => $id,
            'code' => $code,
            'description' => $description,
            'total_quantity' => $new_total,
            'available_quantity' => $new_available,
            'image' => $new_image_name, // nombre del archivo (solo nombre)
            'celebration_id' => $celebration_id,
            'celebration_name' => $celebration_name
        ]);

    } catch (Throwable $e) {
        if (isset($conn)) $conn->rollback();
        json_out(false, 'Error al editar: ' . $e->getMessage());
    }
}

/**
 * ELIMINAR
 */
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) json_out(false, 'ID inválido.');

    try {
        $conn->begin_transaction();

        // obtener imagen
        $stmt = $conn->prepare("SELECT image FROM items WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $conn->rollback();
            json_out(false, 'El adorno no existe.');
        }

        $img = (string)($row['image'] ?? '');

        // borrar item
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $err = $conn->error;
            $stmt->close();
            $conn->rollback();
            json_out(false, 'Error al eliminar: ' . $err);
        }
        $stmt->close();

        $conn->commit();

        // borrar imagen fuera de la transacción
        if (!empty($img)) {
            $path = $uploadsDir . '/' . $img;
            if (file_exists($path)) @unlink($path);
        }

        json_out(true, 'Eliminado.', [
            'item_id' => $id
        ]);

    } catch (Throwable $e) {
        if (isset($conn)) $conn->rollback();
        json_out(false, 'Error al eliminar: ' . $e->getMessage());
    }
}

// Acción desconocida
json_out(false, 'Acción inválida.');
