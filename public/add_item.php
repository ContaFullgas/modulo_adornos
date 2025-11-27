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
    $celebration_id = (isset($_POST['celebration_id']) && $_POST['celebration_id'] !== '') ? (int)$_POST['celebration_id'] : null;
    $category_id = (isset($_POST['category_id']) && $_POST['category_id'] !== '') ? (int)$_POST['category_id'] : null;

    // Validación: código requerido
    if($code === ''){
        $err = "Código requerido.";
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
        $avail = $total;

        // Construimos 4 variantes según si celebration_id / category_id son NULL o tienen valor
        if ($celebration_id === null && $category_id === null) {
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id, category_id)
                VALUES (?, ?, ?, ?, ?, NULL, NULL)
            ");
            // tipos: s (code), s (desc), i (total), i (avail), s (image)
            $stmt->bind_param("ssiis", $code, $desc, $total, $avail, $image_name);

        } elseif ($celebration_id === null && $category_id !== null) {
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id, category_id)
                VALUES (?, ?, ?, ?, ?, NULL, ?)
            ");
            // tipos: s,s,i,i,s,i
            $stmt->bind_param("ssii si", $code, $desc, $total, $avail, $image_name, $category_id);
            // Note: the space inserted in type string above is silly — fix below

            // Fixing bind types properly:
            // (we'll rebind correctly by closing and recreating the stmt)
            $stmt->close();
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id, category_id)
                VALUES (?, ?, ?, ?, ?, NULL, ?)
            ");
            $stmt->bind_param("ssiisi", $code, $desc, $total, $avail, $image_name, $category_id);

        } elseif ($celebration_id !== null && $category_id === null) {
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id, category_id)
                VALUES (?, ?, ?, ?, ?, ?, NULL)
            ");
            $stmt->bind_param("ssiisi", $code, $desc, $total, $avail, $image_name, $celebration_id);

        } else { // both present
            $stmt = $conn->prepare("
                INSERT INTO items (code, description, total_quantity, available_quantity, image, celebration_id, category_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssiisii", $code, $desc, $total, $avail, $image_name, $celebration_id, $category_id);
        }

        // Ejecutar
        if($stmt->execute()) {
            $stmt->close();
            header("Location: items.php");
            exit;
        } else {
            $err = "Error al guardar en la base de datos: " . $stmt->error;
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
