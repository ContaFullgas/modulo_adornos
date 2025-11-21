<?php
require_once __DIR__ . '/../config/auth.php';
require_admin();

$action = $_GET['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

$username = $conn->real_escape_string($_POST['username'] ?? '');
$full_name = $conn->real_escape_string($_POST['full_name'] ?? '');
$role = $conn->real_escape_string($_POST['role'] ?? 'usuario');

$department_id = ($_POST['department_id'] === "" ? NULL : intval($_POST['department_id']));
$password = $_POST['password'] ?? '';


/* CREAR O EDITAR */
if ($action === "" && $_POST) {

    if ($id > 0) {
        // EDITAR
        if ($password === "") {
            $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, role=?, department_id=? WHERE id=?");
            $stmt->bind_param("sssii", $username, $full_name, $role, $department_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, role=?, department_id=?, password=? WHERE id=?");
            $stmt->bind_param("sssisi", $username, $full_name, $role, $department_id, $password, $id);
        }
        $stmt->execute();
        $stmt->close();

    } else {
        // CREAR
        $stmt = $conn->prepare("INSERT INTO users (username,password,full_name,role,department_id) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssssi", $username, $password, $full_name, $role, $department_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_users.php");
    exit;
}


/* ELIMINAR */
if ($action === "delete" && $_POST) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_users.php");
    exit;
}

header("Location: admin_users.php");
exit;
