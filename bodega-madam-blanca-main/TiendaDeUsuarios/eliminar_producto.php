<?php
session_start();

// Verificar si se recibió un ID de producto
if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    // Verificar si el producto existe en el carrito
    if (isset($_SESSION['carrito'][$id_producto])) {
        // Eliminar el producto del carrito
        unset($_SESSION['carrito'][$id_producto]);
        
        // Mensaje de éxito
        echo "<script>
                alert('Producto eliminado del carrito');
                window.location.href = 'carrito.php';
              </script>";
    } else {
        // Mensaje de error si el producto no existe en el carrito
        echo "<script>
                alert('El producto no existe en el carrito');
                window.location.href = 'carrito.php';
              </script>";
    }
} else {
    // Redireccionar si no se proporcionó un ID
    header("Location: carrito.php");
    exit;
}
?> 