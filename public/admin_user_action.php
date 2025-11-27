<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();

$action = $_GET['action'] ?? 'save';

// --- LOGICA DE ELIMINAR ---
if ($action === 'delete') {
    $id = (int)$_POST['id'];
    // Evitar que el admin se elimine a sí mismo (opcional pero recomendado)
    if ($id === current_user()['id']) {
        header("Location: admin_users.php?error=No puedes eliminar tu propia cuenta");
        exit;
    }
    
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: admin_users.php?msg=Usuario eliminado");
    exit;
}

// --- LOGICA DE GUARDADO (CREAR / EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id']; // Si es 0, es CREAR. Si tiene valor, es EDITAR.
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password']; // La contraseña tal cual viene
    $role = $_POST['role'];
    
    // Manejo del departamento (si viene vacío, se guarda como NULL)
    $dept_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

    // ==========================================
    // 1. VALIDACIONES OBLIGATORIAS
    // ==========================================

    // Validar Usuario
    if (empty($username)) {
        header("Location: admin_users.php?error=El nombre de usuario es obligatorio");
        exit;
    }

    // Validar Contraseña (CRÍTICO): 
    // Si es NUEVO usuario ($id == 0), la contraseña NO puede estar vacía.
    if ($id === 0 && empty($password)) {
        header("Location: admin_users.php?error=La contraseña es obligatoria para nuevos usuarios");
        exit;
    }

    // Validar Duplicados:
    // Verificar si el nombre de usuario ya existe en otro ID
    $sql_check = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $username, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: admin_users.php?error=El nombre de usuario '$username' ya existe");
        exit;
    }
    $stmt_check->close();

    // ==========================================
    // 2. GUARDADO EN BASE DE DATOS
    // ==========================================

    if ($id === 0) {
        // --- CREAR USUARIO ---
        $sql = "INSERT INTO users (username, full_name, password, role, department_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $full_name, $password, $role, $dept_id);
        
        if ($stmt->execute()) {
            header("Location: admin_users.php?msg=Usuario creado exitosamente");
        } else {
            header("Location: admin_users.php?error=Error al crear: " . $conn->error);
        }

    } else {
        // --- EDITAR USUARIO ---
        // Si el password está vacío, NO lo actualizamos (se mantiene el viejo)
        if (!empty($password)) {
            $sql = "UPDATE users SET username=?, full_name=?, password=?, role=?, department_id=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $username, $full_name, $password, $role, $dept_id, $id);
        } else {
            $sql = "UPDATE users SET username=?, full_name=?, role=?, department_id=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $username, $full_name, $role, $dept_id, $id);
        }

        if ($stmt->execute()) {
            header("Location: admin_users.php?msg=Usuario actualizado correctamente");
        } else {
            header("Location: admin_users.php?error=Error al actualizar: " . $conn->error);
        }
    }
    $stmt->close();
    exit;
}

// Si entra directo sin POST
header("Location: admin_users.php");
exit;