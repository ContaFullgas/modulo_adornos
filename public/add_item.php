<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
if(current_user()['role'] !== 'admin') { echo "Acceso denegado"; exit; }

if ($_POST) {
    $name = $conn->real_escape_string($_POST["name"]);
    $desc = $conn->real_escape_string($_POST["description"]);
    $total = (int)$_POST["total_quantity"];
    $image = null;

    if (!empty($_FILES["image"]["name"])) {
        if(!is_dir("uploads")) mkdir("uploads", 0755, true);
        $image = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $image);
        $image = $conn->real_escape_string($image);
    } else {
        $image = "";
    }

    $conn->query("INSERT INTO items (name, description, total_quantity, available_quantity, image)
                 VALUES ('$name', '$desc', $total, $total, '$image')");
    header("Location: items.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agregar Adorno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2>Agregar Adorno</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Nombre:</label>
        <input name="name" class="form-control" required>

        <label>Descripción:</label>
        <textarea name="description" class="form-control"></textarea>

        <label>Cantidad Total:</label>
        <input type="number" name="total_quantity" class="form-control" required value="1">

        <label>Imagen:</label>
        <input type="file" name="image" class="form-control">

        <br>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
</body>
</html>
