<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();

$action = $_GET['action'] ?? '';

if($action === 'create' && $_POST){
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']); // **texto plano**
    $role = $conn->real_escape_string($_POST['role']);
    $department_id = empty($_POST['department_id']) ? 'NULL' : (int)$_POST['department_id'];
    $sql = "INSERT INTO users (username,password,role,department_id) VALUES ('$username','$password','$role', $department_id)";
    $conn->query($sql);
    header("Location: admin_users.php");
    exit;
}

if($action === 'delete' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: admin_users.php");
    exit;
}

header("Location: admin_users.php");
exit;
