<?php
session_start();
include 'db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener el rol y el ID del usuario desde la sesión
$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];

// Verificar que sea un Jefe de Obra (id_rol == 3)
if ($id_rol != 3) {
    echo "<script>alert('Acceso denegado.'); window.location.href='../index.php';</script>";
    exit();
}

// Consultar el proyecto asignado al Jefe de Obra
$stmt_proyecto = $conexion->prepare("
    SELECT P.id_proyecto, P.nombre 
    FROM proyectos P
    INNER JOIN jefes_obra_proyectos JP ON P.id_proyecto = JP.id_proyecto
    WHERE JP.id_usuario = ?
");
$stmt_proyecto->bind_param("i", $id_usuario);
$stmt_proyecto->execute();
$proyecto = $stmt_proyecto->get_result()->fetch_assoc();

// Si no tiene proyectos asignados, mostrar mensaje
if (!$proyecto) {
    echo "<h2>No tienes ningún proyecto asignado.</h2>";
    exit();
}

// Obtener el estado del filtro
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta para obtener los pedidos del jefe de obra
$stmt_pedidos = $conexion->prepare("
    SELECT p.id_pedido, p.fecha_pedido, p.estado,
           GROUP_CONCAT(CONCAT(pr.nombre_producto, ' (', d.cantidad, ')') SEPARATOR ', ') as detalles_productos,
           GROUP_CONCAT(CONCAT(pr.nombre_producto, '|', d.cantidad, '|', pr.precio) SEPARATOR '||') as detalles_completos
    FROM Pedidos p
    LEFT JOIN Detalles d ON p.id_pedido = d.id_pedido
    LEFT JOIN Productos pr ON d.id_producto = pr.id_producto
    WHERE p.id_usuario = ? " . 
    ($estado_filtro ? "AND p.estado = ?" : "") . "
    GROUP BY p.id_pedido
    ORDER BY p.fecha_pedido DESC
");

if ($estado_filtro) {
    $stmt_pedidos->bind_param("is", $id_usuario, $estado_filtro);
} else {
    $stmt_pedidos->bind_param("i", $id_usuario);
}
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Pedidos - Jefe de Obra</title>
    <link rel="stylesheet" href="../assets/css/styles2.css">
    <link rel="stylesheet" href="../assets/css/productosProyecto.css">
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-4">
        <div class="container">
            <h1 class="display-4">Estado de Pedidos</h1>
            <h2 class="h4 mb-3">Proyecto: <?php echo htmlspecialchars($proyecto['nombre']); ?></h2>
            <p class="mb-3">Bienvenido, <?php echo $_SESSION['usuario'] ?? 'Usuario'; ?> | 
                <a href="../logout.php" class="text-warning">Cerrar sesión</a>
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="productosProyecto.php" class="btn btn-warning btn-lg px-4 py-2">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Productos
                </a>
            </div>
        </div>
    </header>

    <main class="container py-5">
        <!-- Filtro de Estado -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Filtrar Pedidos
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="estado" class="form-label fw-bold">
                            <i class="fas fa-tag me-2"></i>Estado del Pedido
                        </label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?php echo $estado_filtro === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="pagado" <?php echo $estado_filtro === 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                            <option value="cancelado" <?php echo $estado_filtro === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filtrar
                            </button>
                            <?php if ($estado_filtro): ?>
                                <a href="estado_proyecto.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Limpiar Filtro
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Historial de Pedidos
                </h3>
            </div>
            <div class="card-body">
                <?php if ($pedidos->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Pedido</th>
                                    <th>Fecha</th>
                                    <th>Detalles del Pedido</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                                    <tr style="cursor: pointer;" onclick="mostrarDetalles(<?php echo htmlspecialchars(json_encode($pedido)); ?>)">
                                        <td>#<?php echo $pedido['id_pedido']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                        <td>
                                            <div class="detalles-pedido">
                                                <?php 
                                                if (!empty($pedido['detalles_productos'])) {
                                                    $productos = explode(', ', $pedido['detalles_productos']);
                                                    // Mostrar solo los dos primeros productos
                                                    $productos_mostrados = array_slice($productos, 0, 2);
                                                    foreach ($productos_mostrados as $producto) {
                                                        echo '<span class="badge bg-info me-2 mb-1">' . htmlspecialchars($producto) . '</span>';
                                                    }
                                                    // Si hay más de 2 productos, mostrar indicador
                                                    if (count($productos) > 2) {
                                                        echo '<span class="badge bg-secondary me-2 mb-1">+' . (count($productos) - 2) . ' más</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">Sin detalles</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $pedido['estado'] == 'pendiente' ? 'warning' : 
                                                    ($pedido['estado'] == 'pagado' ? 'success' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>No tienes pedidos registrados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetallesLabel">
                        <i class="fas fa-clipboard-list me-2"></i>Detalles del Pedido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Información del Pedido</h6>
                            <p class="mb-1"><strong>ID Pedido:</strong> <span id="modalIdPedido"></span></p>
                            <p class="mb-1"><strong>Fecha:</strong> <span id="modalFecha"></span></p>
                            <p class="mb-0"><strong>Estado:</strong> <span id="modalEstado"></span></p>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-3">Productos</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modalProductos">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong id="modalTotal"></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarDetalles(pedido) {
            // Llenar información básica
            document.getElementById('modalIdPedido').textContent = '#' + pedido.id_pedido;
            document.getElementById('modalFecha').textContent = new Date(pedido.fecha_pedido).toLocaleString();
            
            // Crear badge de estado
            const estadoClass = pedido.estado === 'pendiente' ? 'warning' : 
                              (pedido.estado === 'pagado' ? 'success' : 'danger');
            document.getElementById('modalEstado').innerHTML = 
                `<span class="badge bg-${estadoClass}">${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1)}</span>`;

            // Procesar detalles de productos
            const tbody = document.getElementById('modalProductos');
            tbody.innerHTML = '';
            let total = 0;

            if (pedido.detalles_completos) {
                const productos = pedido.detalles_completos.split('||');
                productos.forEach(producto => {
                    const [nombre, cantidad, precio] = producto.split('|');
                    const subtotal = cantidad * precio;
                    total += subtotal;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${nombre}</td>
                        <td>${cantidad}</td>
                        <td>$${parseFloat(precio).toFixed(2)}</td>
                        <td>$${subtotal.toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('modalTotal').textContent = '$' + total.toFixed(2);

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
            modal.show();
        }
    </script>
</body>
</html>
