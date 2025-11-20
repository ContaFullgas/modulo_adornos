<?php
// add_item.php - procesa la creación de un adorno (desde modal en items.php)
require_once __DIR__ . '/../config/auth.php';
require_login();
if(current_user()['role'] !== 'admin') { echo "Acceso denegado"; exit; }

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanitizar
    $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
    $desc = $conn->real_escape_string($_POST["description"] ?? '');
    $total = max(1, (int)($_POST["total_quantity"] ?? 1));
    $celebration_id = isset($_POST['celebration_id']) && $_POST['celebration_id'] !== '' ? (int)$_POST['celebration_id'] : null;

    // Validación de formato
    if(!preg_match('/^\d+[A-Za-z]*$/', $code)) {
        $err = "Código inválido. Debe empezar con números y opcionalmente letras (ej. 2, 2A, 12B).";
    }

    // Check unicidad
    if(!$err) {
        $stmt_check = $conn->prepare("SELECT id FROM items WHERE code = ?");
        $stmt_check->bind_param("s", $code);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if($res_check->fetch_assoc()) {
            $err = "El código ya existe. Debes usar un código/folio irrepetible.";
        }
        $stmt_check->close();
    }

    // Procesar imagen si no hay errores
    $image_name = "";
    if(!$err && !empty($_FILES["image"]["name"])) {
        if(!is_dir(__DIR__ . "/uploads")) mkdir(__DIR__ . "/uploads", 0755, true);
        $origName = basename($_FILES["image"]["name"]);
        $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', $origName);
        $image_name = time() . "_" . $safe;
        $target = __DIR__ . "/uploads/" . $image_name;
        if(!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
            $err = "No se pudo guardar la imagen en el servidor.";
        }
    }

    // Insertar en DB
    if(!$err) {
        // Si celebration_id es null, puedes insertar NULL explícitamente construyendo otra consulta.
        if ($celebration_id === null) {
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id)
                VALUES (?, ?, ?, ?, ?, NULL)
            ");
            // tipos: s (code), s (desc), i (total), i (avail), s (image)
            $avail = $total;
            $stmt->bind_param("ssiis", $code, $desc, $total, $avail, $image_name);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $avail = $total;
            // tipos: s (code), s (desc), i (total), i (avail), s (image), i (celebration_id)
            $stmt->bind_param("ssiisi", $code, $desc, $total, $avail, $image_name, $celebration_id);
        }

        if($stmt->execute()) {
            $stmt->close();
            header("Location: items.php");
            exit;
        } else {
            $err = "Error al guardar en la base de datos: " . $conn->error;
            if($image_name && file_exists(__DIR__ . "/uploads/" . $image_name)) {
                @unlink(__DIR__ . "/uploads/" . $image_name);
            }
            if(isset($stmt) && $stmt) $stmt->close();
        }
    }
}

// Si llegas aquí por GET o por error, mostrar error simple y link de vuelta
if($err){
    echo "<div style='margin:20px;'><div style='color:red;padding:10px;border:1px solid #f00;background:#fee;'>".htmlspecialchars($err)."</div>";
    echo '<p><a href="items.php">Volver a lista</a></p></div>';
    exit;
} else {
    // Si se accede por GET directo, redirigimos a items.php
    header("Location: items.php");
    exit;
}
