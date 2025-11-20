<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

// Solo admin puede manipular celebraciones
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
            header("Location: celebrations.php");
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO celebrations (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if($stmt->execute()){
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        } else {
            $err = "Error al crear: " . htmlspecialchars($conn->error);
            $stmt->close();
            echo $err;
            exit;
        }
    }

    if($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if($id <= 0 || $name === '') {
            header("Location: celebrations.php");
            exit;
        }
        $stmt = $conn->prepare("UPDATE celebrations SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if($stmt->execute()){
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        } else {
            $err = "Error al actualizar: " . htmlspecialchars($conn->error);
            $stmt->close();
            echo $err;
            exit;
        }
    }

    if($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if($id <= 0) {
            header("Location: celebrations.php");
            exit;
        }
        // Opcional: prevenir borrado si hay items relacionados
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM items WHERE celebration_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();
        if($cnt > 0) {
            // No permitimos borrar si hay decoraciones relacionadas
            echo "No se puede eliminar: hay {$cnt} adorno(s) asociados. Reasigna o elimina esos items primero.";
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM celebrations WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        } else {
            $err = "Error al eliminar: " . htmlspecialchars($conn->error);
            $stmt->close();
            echo $err;
            exit;
        }
    }
}

// Default
header("Location: celebrations.php");
exit;
