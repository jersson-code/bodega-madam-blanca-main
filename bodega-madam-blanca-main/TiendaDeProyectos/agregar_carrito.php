<?php
session_start();
include 'db.php';

// Asegurarse de que el carrito sea un array
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Validar los datos enviados por POST
if (isset($_POST['id_producto']) && isset($_POST['cantidad']) && isset($_POST['id_proyecto'])) {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);
    $id_proyecto = intval($_POST['id_proyecto']); // Identificar el proyecto

    // Verificar el stock disponible para el producto en el proyecto seleccionado
    $query = $conexion->prepare("
        SELECT sp.id_proyecto, sp.id_producto, p.nombre_producto, sp.cantidad AS stock_proyecto
        FROM stock_Proyectos sp
        INNER JOIN productos p ON sp.id_producto = p.id_producto
        WHERE sp.id_proyecto = ? AND sp.id_producto = ?
    ");
    $query->bind_param("ii", $id_proyecto, $id_producto);
    $query->execute();
    $result = $query->get_result();
    $producto = $result->fetch_assoc();

    if ($producto) {
        $key = $id_proyecto . '_' . $id_producto; // Clave única para combinar proyecto y producto
        $cantidad_en_carrito = isset($_SESSION['carrito'][$key]['cantidad']) ? $_SESSION['carrito'][$key]['cantidad'] : 0;

        // Validar que la cantidad total en el carrito no supere el stock disponible
        if (($cantidad + $cantidad_en_carrito) <= $producto['stock_proyecto']) {
            if (isset($_SESSION['carrito'][$key]) && is_array($_SESSION['carrito'][$key])) {
                // Si el producto ya está en el carrito, actualizamos la cantidad
                $_SESSION['carrito'][$key]['cantidad'] += $cantidad;
            } else {
                // Si no está en el carrito, lo añadimos
                $_SESSION['carrito'][$key] = [
                    'id_proyecto' => $producto['id_proyecto'],
                    'id_producto' => $producto['id_producto'],
                    'nombre' => $producto['nombre_producto'],
                    'cantidad' => $cantidad
                ];
            }

            // Redirigir al carrito
            header("Location: carrito.php");
        } else {
            // Mostrar mensaje si se supera el stock
            echo "<script>alert('La cantidad solicitada supera el stock disponible. Stock actual: {$producto['stock_proyecto']}'); window.location.href='productosProyecto.php';</script>";
        }
    } else {
        // Mostrar mensaje de error si no se encuentra el producto
        echo "<script>alert('Producto no disponible para este proyecto.'); window.location.href='productosProyecto.php';</script>";
    }
    exit;
} else {
    // Si faltan datos, redirigir con un mensaje de error
    echo "<script>alert('Datos incompletos'); window.location.href='productosProyecto.php';</script>";
    exit;
}
