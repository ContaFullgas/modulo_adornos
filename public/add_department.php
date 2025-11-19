<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
if(current_user()['role'] !== 'admin') { echo "Acceso denegado"; exit; }

if ($_POST) {
    $name = $conn->real_escape_string($_POST["name"]);
    $contact = $conn->real_escape_string($_POST["contact"]);
    $conn->query("INSERT INTO departments (name, contact) VALUES ('$name', '$contact')");
    header("Location: departments.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agregar Departamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container">
    <h2>Agregar Departamento</h2>

    <form method="POST">
        <label>Nombre:</label>
        <input name="name" class="form-control" required>

        <label>Contacto:</label>
        <input name="contact" class="form-control">

        <br>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
</body>
</html>
