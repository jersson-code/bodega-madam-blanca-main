<?php
session_start();
include('./conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    $nombre = $_POST['nombre'];
    $ciudad = $_POST['ciudad'];
    $direccion = $_POST['direccion'];
    $codigo_postal = $_POST['codigo_postal'];

    if ($id_usuario) {
        $query = $conexion->prepare("INSERT INTO direcciones (id_usuario, nombre, ciudad, direccion, codigo_postal) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param("issss", $id_usuario, $nombre, $ciudad, $direccion, $codigo_postal);
        
        if ($query->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la dirección.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    }
}
?> 