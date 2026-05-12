<?php
session_start();
include 'db.php';

$id_proyecto = $_SESSION['id_proyecto'] ?? null; // Proyecto asociado
$id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : null; // ID del producto a eliminar

// Validar que el carrito y el ID del producto existan
if (!isset($_SESSION['carrito'])) {
    echo "<script>alert('Carrito no encontrado.'); window.location.href = 'carrito.php';</script>";
} elseif (!$id_producto) {
    echo "<script>alert('ID del producto no válido.'); window.location.href = 'carrito.php';</script>";
} elseif (!$id_proyecto) {
    echo "<script>alert('ID del proyecto no válido.'); window.location.href = 'carrito.php';</script>";
} else {
    echo "<script>alert('Datos inválidos o carrito vacío.'); window.location.href = 'carrito.php';</script>";
}


// Identificar la clave del producto en el carrito
$carrito = $_SESSION['carrito'];
foreach ($carrito as $key => $producto) {
    if ($producto['id_producto'] === $id_producto) {
        // Restaurar el stock en la tabla stock_Proyectos
        $cantidad = $producto['cantidad'];
        $query = $conexion->prepare("
            UPDATE stock_Proyectos 
            SET cantidad = cantidad + ? 
            WHERE id_proyecto = ? AND id_producto = ?
        ");
        $query->bind_param("iii", $cantidad, $id_proyecto, $id_producto);
        $query->execute();

        if ($query->affected_rows > 0) {
            // Eliminar el producto del carrito
            unset($_SESSION['carrito'][$key]);
            echo "<script>alert('Producto eliminado del carrito.'); window.location.href = 'carrito.php';</script>";
        } else {
            echo "<script>alert('Error al restaurar el stock. Inténtelo de nuevo.'); window.location.href = 'carrito.php';</script>";
        }
        exit;
    }
}

// Si no se encontró el producto en el carrito
echo "<script>alert('Producto no encontrado en el carrito.'); window.location.href = 'carrito.php';</script>";
