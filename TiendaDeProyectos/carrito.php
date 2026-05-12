<?php
session_start();
include 'db.php';

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

// Obtener productos del carrito
$carrito = $_SESSION['carrito'] ?? [];

// Procesar el pedido cuando el usuario hace clic en "Realizar Pedido"
if (isset($_POST['realizar_pedido'])) {
    if (!empty($carrito)) {
        // Insertar en la tabla Pedidos
        $query_pedido = $conexion->prepare("INSERT INTO Pedidos (id_usuario, estado) VALUES (?, 'pendiente')");
        $query_pedido->bind_param("i", $id_usuario);
        $query_pedido->execute();
        $id_pedido = $query_pedido->insert_id;

        // Insertar los detalles del pedido y actualizar el stock
        foreach ($carrito as $key => $producto) {
            // Insertar cada producto en la tabla Detalles
            $query_detalle = $conexion->prepare("INSERT INTO Detalles (id_pedido, id_producto, cantidad) VALUES (?, ?, ?)");
            $query_detalle->bind_param("iii", $id_pedido, $producto['id_producto'], $producto['cantidad']);
            $query_detalle->execute();

            // Actualizar el stock en la tabla stock_Proyectos
            $query_stock_proyecto = $conexion->prepare("
                UPDATE stock_Proyectos 
                SET cantidad = cantidad - ? 
                WHERE id_proyecto = ? AND id_producto = ?
            ");
            $query_stock_proyecto->bind_param("iii", $producto['cantidad'], $producto['id_proyecto'], $producto['id_producto']);
            $query_stock_proyecto->execute();

            if ($query_stock_proyecto->affected_rows <= 0) {
                echo "<script>alert('No se pudo actualizar el stock del producto: {$producto['nombre']}. Verifique la cantidad disponible.'); window.location.href = 'carrito.php';</script>";
                exit;
            }
        }

        // Limpiar el carrito después de procesar el pedido
        unset($_SESSION['carrito']);
        echo "<script>alert('Pedido realizado con éxito'); window.location.href = 'index.php';</script>";
        exit;
    } else {
        echo "<script>alert('El carrito está vacío');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="../assets/css/styles2.css">
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
</head>

<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Carrito de Compras</h1>

        <div class="mb-3 text-end">
            <a href="./productosProyecto.php" class="btn btn-primary">Continuar Comprando</a>
        </div>

        <form method="POST">
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($carrito)): ?>
                            <?php foreach ($carrito as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo $producto['cantidad']; ?></td>
                                    <td>
                                        <a href="eliminar_producto.php?id=<?php echo $producto['id_producto']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay productos en el carrito</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button type="submit" name="realizar_pedido" class="btn btn-success">Realizar Pedido</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>