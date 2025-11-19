<?php
// add_department.php
require_once __DIR__ . '/../config/auth.php';
require_login();
if(current_user()['role'] !== 'admin') { echo "Acceso denegado"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST["name"] ?? '');
    if($name === ''){
        // podrías redirigir con mensaje flash; aquí simple exit
        header("Location: departments.php");
        exit;
    }
    // Prepared statement
    $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if($stmt->execute()){
        header("Location: departments.php");
        exit;
    } else {
        // fallback: mostrar error (útil si algo falla)
        echo "Error al insertar: " . htmlspecialchars($conn->error);
        exit;
    }
}
header("Location: departments.php");
exit;
