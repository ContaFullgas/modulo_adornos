<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

if(current_user()['role'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado";
    exit;
}

$action = $_GET['action'] ?? '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -------------------------
    // EDITAR ADORNO
    // -------------------------
    if($action === 'edit'){
        $id = (int)($_POST['id'] ?? 0);
        $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $new_total = max(1, (int)($_POST['total_quantity'] ?? 1));
        $existing_image = $_POST['existing_image'] ?? '';

        if($id <= 0 || $code === ''){
            header("Location: items.php");
            exit;
        }

        // Validar formato de code (opcional)
        if(!preg_match('/^\d+[A-Za-z]*$/', $code)){
            echo "Código inválido."; exit;
        }

        // Verificar unicidad del code (excepto este id)
        $stmt = $conn->prepare("SELECT id FROM items WHERE code = ? AND id <> ?");
        $stmt->bind_param("si", $code, $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->fetch_assoc()){
            echo "El código ya existe para otro adorno."; exit;
        }

        // Obtener valores actuales: total_quantity, available_quantity
        $stmt = $conn->prepare("SELECT total_quantity, available_quantity, image FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cur = $stmt->get_result()->fetch_assoc();
        if(!$cur){
            header("Location: items.php"); exit;
        }
        $cur_total = (int)$cur['total_quantity'];
        $cur_available = (int)$cur['available_quantity'];
        $cur_image = $cur['image'];

        // Calcular cuántos están reservados (o usados): reserved = cur_total - cur_available
        $reserved = $cur_total - $cur_available;
        if($reserved < 0) $reserved = 0;

        // Calcular nueva available: new_available = new_total - reserved
        $new_available = $new_total - $reserved;
        if($new_available < 0) $new_available = 0;

        // Manejo de imagen: si subieron nueva imagen, guardarla y borrar la antigua
        $new_image_name = $cur_image;
        if(!empty($_FILES['image']['name'])){
            if(!is_dir(__DIR__ . "/uploads")) mkdir(__DIR__ . "/uploads", 0755, true);
            $orig = basename($_FILES['image']['name']);
            $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', $orig);
            $new_image_name = time() . "_" . $safe;
            $target = __DIR__ . "/uploads/" . $new_image_name;
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $target)){
                echo "No se pudo subir la nueva imagen."; exit;
            }
            // borrar imagen anterior si existía y es diferente
            if(!empty($cur_image) && $cur_image !== $new_image_name){
                $oldpath = __DIR__ . "/uploads/" . $cur_image;
                if(file_exists($oldpath)) @unlink($oldpath);
            }
        }

        // Actualizar fila
        $stmt = $conn->prepare("UPDATE items SET code = ?, description = ?, total_quantity = ?, available_quantity = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssissi", $code, $description, $new_total, $new_available, $new_image_name, $id);
        if($stmt->execute()){
            header("Location: items.php");
            exit;
        } else {
            echo "Error al actualizar: " . htmlspecialchars($conn->error);
            exit;
        }
    }

    // -------------------------
    // ELIMINAR ADORNO
    // -------------------------
    if($action === 'delete'){
        $id = (int)($_POST['id'] ?? 0);
        if($id <= 0){
            header("Location: items.php"); exit;
        }

        // Obtener imagen para borrar
        $stmt = $conn->prepare("SELECT image FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $img = $row['image'] ?? '';

        // Borrar fila
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            // borrar imagen si existe
            if(!empty($img)){
                $path = __DIR__ . "/uploads/" . $img;
                if(file_exists($path)) @unlink($path);
            }
            header("Location: items.php");
            exit;
        } else {
            echo "Error al eliminar: " . htmlspecialchars($conn->error);
            exit;
        }
    }
}

// Por defecto redirigir
header("Location: items.php");
exit;
