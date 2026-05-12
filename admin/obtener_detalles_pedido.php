<?php
include("./conexion.php");

if (!isset($_GET['id_pedido'])) {
    echo "Error: ID de pedido no proporcionado";
    exit;
}

$id_pedido = intval($_GET['id_pedido']);

// Obtener los detalles del cliente y la dirección asociada al pedido
$pedido_info = $conexion->query("
    SELECT p.id_pedido, p.id_usuario, p.total, p.fecha_pedido, u.nombre, u.email, u.telefono, d.ciudad, d.codigo_postal, d.direccion, p.estado
    FROM Pedidos p
    JOIN Usuarios u ON p.id_usuario = u.id_usuario
    LEFT JOIN Direcciones d ON p.id_pedido = d.id_pedido
    WHERE p.id_pedido = $id_pedido
");

// Obtener los detalles de los productos en el pedido
$detalles = $conexion->query("
    SELECT d.id_producto, d.cantidad, d.precio_unitario, p.nombre_producto 
    FROM Detalles d 
    JOIN Productos p ON d.id_producto = p.id_producto 
    WHERE d.id_pedido = $id_pedido
");

if ($pedido_info && $pedido_info->num_rows > 0) {
    $info = $pedido_info->fetch_assoc();
?>

<!-- Información del Cliente -->
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Información del Cliente</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($info['nombre']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($info['email']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($info['telefono']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Fecha del Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($info['fecha_pedido'])); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($info['total'], 2); ?></p>
                <p><strong>Estado:</strong> 
                    <span class="badge bg-<?php 
                        echo $info['estado'] == 'pendiente' ? 'warning' : 
                            ($info['estado'] == 'pagado' ? 'success' : 'danger'); 
                    ?>">
                        <?php echo ucfirst($info['estado']); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Dirección de Envío -->
<div class="card mb-3">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Dirección de Envío</h5>
    </div>
    <div class="card-body">
        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($info['direccion'] ?? 'No disponible'); ?></p>
        <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($info['ciudad'] ?? 'No disponible'); ?></p>
        <p><strong>Código Postal:</strong> <?php echo htmlspecialchars($info['codigo_postal'] ?? 'No disponible'); ?></p>
    </div>
</div>

<!-- Productos del Pedido -->
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Productos del Pedido</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_pagar = 0;
                    while ($detalle = $detalles->fetch_assoc()):
                        $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
                        $total_pagar += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($total_pagar, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Acciones del Pedido -->
<?php if ($info['estado'] == 'pendiente'): ?>
<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Acciones del Pedido</h5>
    </div>
    <div class="card-body text-center">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Este pedido está pendiente de aprobación. Por favor, revise los detalles antes de tomar una decisión.
        </div>
        <div class="d-flex justify-content-center gap-3">
            <a href="pedidos.php?aprobar=<?php echo $id_pedido; ?>" class="btn btn-success btn-lg">
                <i class="fas fa-check-circle"></i> Aprobar Pedido
            </a>
            <a href="pedidos.php?rechazar=<?php echo $id_pedido; ?>" class="btn btn-danger btn-lg">
                <i class="fas fa-times-circle"></i> Rechazar Pedido
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
} else {
    echo '<div class="alert alert-danger">No se encontró información del pedido.</div>';
}
?> 