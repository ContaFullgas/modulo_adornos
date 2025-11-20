<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

// Solo admin puede crear/editar/eliminar departamentos
if(current_user()['role'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado";
    exit;
}

$action = $_GET['action'] ?? '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if($name === '') {
            header("Location: departments.php");
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if($stmt->execute()){
            header("Location: departments.php");
            exit;
        } else {
            echo "Error al crear departamento: " . htmlspecialchars($conn->error);
            exit;
        }
    }

    if($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if($id <= 0 || $name === '') {
            header("Location: departments.php");
            exit;
        }
        $stmt = $conn->prepare("UPDATE departments SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if($stmt->execute()){
            header("Location: departments.php");
            exit;
        } else {
            echo "Error al actualizar: " . htmlspecialchars($conn->error);
            exit;
        }
    }

    if($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if($id <= 0) {
            header("Location: departments.php");
            exit;
        }
        // optional: check foreign keys (items/reservations) before deleting
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            header("Location: departments.php");
            exit;
        } else {
            echo "Error al eliminar: " . htmlspecialchars($conn->error);
            exit;
        }
    }
}

// si no es POST o acción desconocida, redirigir
header("Location: departments.php");
exit;
