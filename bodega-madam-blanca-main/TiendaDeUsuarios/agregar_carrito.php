<?php
session_start();
include '../admin/conexion.php';

// Asegurarse de que el carrito sea un array
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_POST['id_producto']) && isset($_POST['cantidad'])) {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    // Verificar el stock del producto
    $query = $conexion->prepare("SELECT * FROM Productos WHERE id_producto = ?");
    $query->bind_param("i", $id_producto);
    $query->execute();
    $result = $query->get_result();
    $producto = $result->fetch_assoc();

    if ($producto) {
        $stock_disponible = $producto['stock'];
        $cantidad_en_carrito = isset($_SESSION['carrito'][$id_producto]['cantidad'])
            ? $_SESSION['carrito'][$id_producto]['cantidad']
            : 0;

        // Verificar si la cantidad solicitada supera el stock disponible
        if ($cantidad + $cantidad_en_carrito > $stock_disponible) {

            $cantidad_restante = $stock_disponible - $cantidad_en_carrito;

            echo "<script>alert('Stock insuficiente. Solo quedan {$cantidad_restante} unidades disponibles.'); window.location.href='tienda.php';</script>";
        } else {
            // Si el producto ya está en el carrito, actualizamos la cantidad
            if ($cantidad_en_carrito > 0) {
                $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
            } else {
                // Si no está en el carrito, lo añadimos
                $_SESSION['carrito'][$id_producto] = [
                    'id' => $producto['id_producto'],
                    'nombre' => $producto['nombre_producto'],
                    'precio' => $producto['precio'],
                    'cantidad' => $cantidad
                ];
            }

            // Redirigir al carrito
            header("Location: carrito.php");
        }
    } else {
        echo "<script>alert('Producto no encontrado'); window.location.href='index.php';</script>";
    }
    exit;
}
