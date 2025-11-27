<?php
// Edita según tu entorno
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'adornos';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>
