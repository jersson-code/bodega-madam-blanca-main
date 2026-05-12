<?php
session_start();
include('./admin/conexion.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;

// Verificar si el usuario está logueado antes de proceder
if (!$id_usuario) {
    echo "<script>alert('Por favor, inicie sesión para realizar un pedido.'); window.location.href = 'login.php';</script>";
    exit;
}

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Obtener el ID del producto desde la URL para añadir al carrito
if (isset($_GET['add'])) {
    $id_producto = intval($_GET['add']);

    // Verificar si el producto ya está en el carrito
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]['cantidad']++;
    } else {
        $query = $conexion->prepare("SELECT * FROM Productos WHERE id_producto = ?");
        $query->bind_param("i", $id_producto);
        $query->execute();
        $result = $query->get_result();
        $producto = $result->fetch_assoc();

        if ($producto) {
            $_SESSION['carrito'][$id_producto] = [
                'id' => $producto['id_producto'],
                'nombre' => $producto['nombre_producto'],
                'precio' => $producto['precio'],
                'cantidad' => 1
            ];
        }
    }
    header("Location: carrito.php");
    exit;
}

// Obtener productos del carrito
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Procesar el pedido cuando el usuario hace clic en "Realizar Pedido"
if (isset($_POST['realizar_pedido'])) {
    if (!empty($carrito)) {
        // Calcular el total del pedido
        foreach ($carrito as $producto) {
            if (is_array($producto) && isset($producto['precio'], $producto['cantidad'])) {
                $total += $producto['precio'] * $producto['cantidad'];
            }
        }

        // Insertar en la tabla Pedidos
        $query_pedido = $conexion->prepare("INSERT INTO Pedidos (id_usuario, total, estado) VALUES (?, ?, 'pendiente')");
        $query_pedido->bind_param("id", $id_usuario, $total);
        $query_pedido->execute();
        $id_pedido = $query_pedido->insert_id;

        // Insertar los detalles del pedido en la tabla Detalles y actualizar el stock
        foreach ($carrito as $producto) {
            if (is_array($producto) && isset($producto['id'], $producto['cantidad'], $producto['precio'])) {
                // Insertar cada producto en la tabla Detalles
                $query_detalle = $conexion->prepare("INSERT INTO Detalles (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                $query_detalle->bind_param("iiid", $id_pedido, $producto['id'], $producto['cantidad'], $producto['precio']);
                $query_detalle->execute();

                // Actualizar el stock en la base de datos
                $query_stock = $conexion->prepare("UPDATE Productos SET stock = stock - ? WHERE id_producto = ?");
                $query_stock->bind_param("ii", $producto['cantidad'], $producto['id']);
                $query_stock->execute();
            }
        }

        // Limpiar el carrito después de procesar el pedido
        unset($_SESSION['carrito']);
        echo "<script>alert('Pedido realizado con éxito'); window.location.href = 'index.php';</script>";
        exit;
    }
}

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: index.php');
    exit;
}

$productos_en_carrito = [];
$ids = implode(',', array_keys($_SESSION['carrito']));
$query = "SELECT * FROM Productos WHERE id_producto IN ($ids)";
$resultado = $conexion->query($query);

while ($producto = $resultado->fetch_assoc()) {
    $cantidad = isset($_SESSION['carrito'][$producto['id_producto']]) ? (int)$_SESSION['carrito'][$producto['id_producto']] : 0;
    $precio = isset($producto['precio']) ? (float)$producto['precio'] : 0;

    if ($cantidad > 0 && $precio > 0) {
        $producto['cantidad'] = $cantidad;
        $productos_en_carrito[] = $producto;
        $total += $precio * $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Confirmar Compra</title>
    <link href="./assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center">Confirmar Compra</h2>

        <!-- Resumen de productos en el carrito -->
        <h3 class="mt-4">Resumen de Productos</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos_en_carrito as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                        <td><?php echo $producto['cantidad']; ?></td>
                        <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                        <td>$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4 class="text-end">Total a Pagar: $<?php echo number_format($total, 2); ?></h4>

        <!-- Formulario de información del cliente -->
        <form method="POST" action="" class="mt-4">
            <h3>Datos del Cliente</h3>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" required>
            </div>
            <div class="mb-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="ciudad" name="ciudad" required>
            </div>
            <div class="mb-3">
                <label for="codigo_postal" class="form-label">Código Postal</label>
                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required>
            </div>

            <!-- Botón para procesar el pago -->
            <button type="submit" class="btn btn-success w-100" name="realizar_pedido">Finalizar Compra</button>
        </form>
    </div>
</body>

</html>