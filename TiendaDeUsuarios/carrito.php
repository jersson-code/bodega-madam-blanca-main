<?php
session_start();
include '../admin/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

// Verificar si el usuario está logueado antes de proceder
if (!$id_usuario) {
    echo "<script> window.location.href = '../login.php';</script>";
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
            $total += $producto['precio'] * $producto['cantidad'];
        }

        // Insertar en la tabla Pedidos
        $query_pedido = $conexion->prepare("INSERT INTO Pedidos (id_usuario, total, estado) VALUES (?, ?, 'pendiente')");
        $query_pedido->bind_param("id", $id_usuario, $total);
        $query_pedido->execute();
        $id_pedido = $query_pedido->insert_id;

        // Insertar los detalles del pedido en la tabla Detalles y actualizar el stock
        foreach ($carrito as $producto) {
            // Insertar cada producto en la tabla Detalles
            $query_detalle = $conexion->prepare("INSERT INTO Detalles (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $query_detalle->bind_param("iiid", $id_pedido, $producto['id'], $producto['cantidad'], $producto['precio']);
            $query_detalle->execute();

            // Actualizar el stock en la base de datos
            $query_stock = $conexion->prepare("UPDATE Productos SET stock = stock - ? WHERE id_producto = ?");
            $query_stock->bind_param("ii", $producto['cantidad'], $producto['id']);
            $query_stock->execute();
        }

        // Limpiar el carrito después de procesar el pedido
        unset($_SESSION['carrito']);
        echo "<script>alert('Pedido realizado con éxito'); window.location.href = '../index.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/styles2.css">
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">

</head>

<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Carrito de Compras</h1>
        <div class="d-flex justify-content-between mb-4">
            <a href="tienda.php" class="btn btn-primary">Continuar Comprando</a>
        </div>

        <form method="POST">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($carrito)): ?>
                            <?php foreach ($carrito as $producto): ?>
                                <?php
                                $subtotal = $producto['precio'] * $producto['cantidad'];
                                $total += $subtotal;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo $producto['cantidad']; ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar este producto?')"
                                           >eliminar
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay productos en el carrito</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-success">Total: $<?php echo number_format($total, 2); ?></h2>
                <button type="submit" name="realizar_pedido" class="btn btn-success btn-lg">
                    Realizar Pedido
                </button>
            </div>
        </form>
    </div>

</body>

</html>
