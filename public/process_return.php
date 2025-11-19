<?php
require_once __DIR__ . '/../config/auth.php';
require_login();

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: reservations.php");
    exit;
}

$reservation_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : null;
$item_id = (int)$_POST['item_id'];
$dept_id = (int)$_POST['dept_id'];
$quantity = max(1, (int)$_POST['quantity']);
$notes = $conn->real_escape_string($_POST['notes'] ?? '');
$handled_by = (int)$_SESSION['user_id'];

// 1) Insert return record
$stmt = $conn->prepare("INSERT INTO returns (reservation_id,item_id,dept_id,quantity,notes,handled_by) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("iiiisi", $reservation_id, $item_id, $dept_id, $quantity, $notes, $handled_by);
$stmt->execute();

// 2) Update item available_quantity
$conn->query("UPDATE items SET available_quantity = available_quantity + $quantity WHERE id = $item_id");

// 3) Optionally update reservation status/returned_at
if($reservation_id){
    $now = date('Y-m-d H:i:s');
    $conn->query("UPDATE reservations SET status='returned', returned_at='$now' WHERE id = $reservation_id");
}

header("Location: returns.php");
exit;
