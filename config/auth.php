<?php
// auth.php - sesiones y helpers (contraseñas en texto plano según tu petición)
session_start();
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    global $conn;
    if(!is_logged_in()) return null;
    $id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT u.*, d.name as department_name FROM users u LEFT JOIN departments d ON u.department_id=d.id WHERE u.id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

function require_login() {
    if(!is_logged_in()){
        header("Location: /public/login.php");
        exit;
    }
}

function require_admin() {
    $u = current_user();
    if(!$u || $u['role'] !== 'admin') {
        http_response_code(403);
        echo "Acceso denegado. Requiere rol admin.";
        exit;
    }
}
?>
