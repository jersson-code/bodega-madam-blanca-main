<?php
$host = 'localhost';
$db = 'pruebabodega';
$user = 'root';
$password = '';

$conexion = new mysqli($host, $user, $password, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
