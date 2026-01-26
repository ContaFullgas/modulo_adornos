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
    
    // ============================================
    // CREAR CELEBRACIÓN
    // ============================================
    if($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if($name === '') {
            $_SESSION['error'] = 'El nombre de la celebración es obligatorio';
            header("Location: celebrations.php");
            exit;
        }
        
        // Crear celebración desactivada por defecto
        $stmt = $conn->prepare("INSERT INTO celebrations (name, is_active) VALUES (?, 0)");
        $stmt->bind_param("s", $name);
        
        if($stmt->execute()){
            $_SESSION['success'] = 'Celebración creada exitosamente';
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        } else {
            $_SESSION['error'] = "Error al crear: " . htmlspecialchars($conn->error);
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        }
    }

    // ============================================
    // EDITAR CELEBRACIÓN
    // ============================================
    if($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        
        if($id <= 0 || $name === '') {
            $_SESSION['error'] = 'Datos inválidos';
            header("Location: celebrations.php");
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE celebrations SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        
        if($stmt->execute()){
            $_SESSION['success'] = 'Celebración actualizada correctamente';
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        } else {
            $_SESSION['error'] = "Error al actualizar: " . htmlspecialchars($conn->error);
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        }
    }

    // ============================================
    // ELIMINAR CELEBRACIÓN
    // ============================================
    if($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if($id <= 0) {
            $_SESSION['error'] = 'ID inválido';
            header("Location: celebrations.php");
            exit;
        }
        
        // Verificar si hay items relacionados
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM items WHERE celebration_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();
        
        if($cnt > 0) {
            $_SESSION['error'] = "No se puede eliminar: hay {$cnt} artículo(s) asociado(s). Reasigna o elimina esos items primero.";
            header("Location: celebrations.php");
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM celebrations WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()){
            $_SESSION['success'] = 'Celebración eliminada correctamente';
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        } else {
            $_SESSION['error'] = "Error al eliminar: " . htmlspecialchars($conn->error);
            $stmt->close();
            header("Location: celebrations.php");
            exit;
        }
    }

    // ============================================
    // ACTIVAR/DESACTIVAR CELEBRACIÓN (TOGGLE)
    // ============================================
    if($action === 'toggle_active') {
        $id = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);
        
        if($id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        // Si se va a ACTIVAR esta celebración, primero DESACTIVAR todas las demás
        if($is_active === 1) {
            $conn->query("UPDATE celebrations SET is_active = 0");
        }

        // Ahora actualizar esta celebración
        $stmt = $conn->prepare("UPDATE celebrations SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_active, $id);
        
        if($stmt->execute()) {
            $status_text = $is_active ? 'activada' : 'desactivada';
            $stmt->close();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => "Celebración {$status_text} correctamente. El tema se ha actualizado.",
                'is_active' => $is_active
            ]);
            exit;
        } else {
            $error_msg = htmlspecialchars($conn->error);
            $stmt->close();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => "Error al actualizar el estado: {$error_msg}"
            ]);
            exit;
        }
    }
}

// Si no es una acción válida, redirigir
$_SESSION['error'] = 'Acción no válida';
header("Location: celebrations.php");
exit;
?>