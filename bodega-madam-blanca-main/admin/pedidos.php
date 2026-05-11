<?php

include("./conexion.php");

session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol de administrador (id_rol == 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    // Si no tiene el rol correcto, redirigir al usuario a la página principal
    header("Location: ../index.php");
    exit();
}

// Obtener fechas del filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

// Verificar si se está solicitando ver detalles de un pedido
$mostrar_detalles = false;
$detalles_pedido = null;
if (isset($_GET['detalles'])) {
    $id_pedido = intval($_GET['detalles']);
    
    // Obtener información del pedido
    $query_pedido = $conexion->prepare("
        SELECT p.*, u.nombre as nombre_usuario, u.email as email_usuario
        FROM Pedidos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE p.id_pedido = ?
    ");
    $query_pedido->bind_param("i", $id_pedido);
    $query_pedido->execute();
    $pedido_info = $query_pedido->get_result()->fetch_assoc();
    
    if ($pedido_info) {
        // Obtener detalles del pedido
        $query_detalles = $conexion->prepare("
            SELECT d.*, p.nombre_producto, p.precio
            FROM Detalles d
            JOIN Productos p ON d.id_producto = p.id_producto
            WHERE d.id_pedido = ?
        ");
        $query_detalles->bind_param("i", $id_pedido);
        $query_detalles->execute();
        $detalles_pedido = $query_detalles->get_result();
        $mostrar_detalles = true;
    }
}

// Obtener resumen de ventas
$resumen_query = $conexion->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as monto_total,
        COALESCE(AVG(total), 0) as promedio_venta,
        COALESCE(MAX(total), 0) as venta_maxima
    FROM Pedidos 
    WHERE fecha_pedido BETWEEN ? AND ?
    AND estado = 'pagado'
");
$resumen_query->bind_param("ss", $fecha_inicio, $fecha_fin);
$resumen_query->execute();
$resumen = $resumen_query->get_result()->fetch_assoc();

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$where_busqueda = '';
if (!empty($busqueda)) {
    $where_busqueda = " AND (id_pedido LIKE '%$busqueda%' OR id_usuario LIKE '%$busqueda%')";
}

// Agregar filtro de estado
$where_estado = '';
if (!empty($estado_filtro)) {
    $where_estado = " AND estado = '$estado_filtro'";
}

// Obtener total de registros para paginación
$total_query = $conexion->query("
    SELECT COUNT(*) as total 
    FROM Pedidos 
    WHERE fecha_pedido BETWEEN '$fecha_inicio' AND '$fecha_fin' $where_busqueda $where_estado
");
$total_registros = $total_query->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener pedidos con paginación
$pedidos_query = $conexion->prepare("
    SELECT * FROM Pedidos 
    WHERE fecha_pedido BETWEEN ? AND ? $where_busqueda $where_estado
    ORDER BY fecha_pedido DESC 
    LIMIT ? OFFSET ?
");
$pedidos_query->bind_param("ssii", $fecha_inicio, $fecha_fin, $registros_por_pagina, $offset);
$pedidos_query->execute();
$pedidos = $pedidos_query->get_result();

// Actualizar el estado del pedido
if (isset($_GET['aprobar'])) {
    $id_pedido = intval($_GET['aprobar']);

    // Verificar que el pedido esté en estado 'pendiente'
    $verificar = $conexion->query("SELECT estado FROM Pedidos WHERE id_pedido = $id_pedido");
    $pedido = $verificar->fetch_assoc();

    if ($pedido && $pedido['estado'] == 'pendiente') {
        $conexion->query("UPDATE Pedidos SET estado = 'pagado' WHERE id_pedido = $id_pedido");
        echo "<script>alert('Pedido aprobado'); window.location.href='pedidos.php';</script>";
    } else {
        echo "<script>alert('El pedido ya fue procesado.'); window.location.href='pedidos.php';</script>";
    }
}

if (isset($_GET['rechazar'])) {
    $id_pedido = intval($_GET['rechazar']);

    // Verificar que el pedido esté en estado 'pendiente'
    $verificar = $conexion->query("SELECT estado FROM Pedidos WHERE id_pedido = $id_pedido");
    $pedido = $verificar->fetch_assoc();

    if ($pedido && $pedido['estado'] == 'pendiente') {
        // Restaurar el stock de los productos
        $detalles = $conexion->query("SELECT id_producto, cantidad FROM Detalles WHERE id_pedido = $id_pedido");
        while ($detalle = $detalles->fetch_assoc()) {
            $id_producto = $detalle['id_producto'];
            $cantidad = $detalle['cantidad'];
            $conexion->query("UPDATE Productos SET stock = stock + $cantidad WHERE id_producto = $id_producto");
        }

        // Cambiar el estado del pedido a 'cancelado'
        $conexion->query("UPDATE Pedidos SET estado = 'cancelado' WHERE id_pedido = $id_pedido");
        echo "<script>alert('Pedido rechazado y stock restaurado'); window.location.href='pedidos.php';</script>";
    } else {
        echo "<script>alert('El pedido ya fue procesado.'); window.location.href='pedidos.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Panel de Ventas - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../assets/css/styles2.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">

</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="./admin.php">Administrator</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>

    </nav>
    <div id="layoutSidenav">
        <?php include("./nav.php"); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Panel de Ventas</h1>
                    
                    <?php if ($mostrar_detalles && $pedido_info): ?>
                    <!-- Vista de detalles del pedido -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shopping-cart me-1"></i>
                                Detalles del Pedido #<?php echo $pedido_info['id_pedido']; ?>
                            </div>
                            <a href="admin.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Información del Cliente</h5>
                                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido_info['nombre_usuario']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido_info['email_usuario']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Información del Pedido</h5>
                                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido_info['fecha_pedido'])); ?></p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $pedido_info['estado'] == 'pendiente' ? 'warning' : 
                                                ($pedido_info['estado'] == 'pagado' ? 'success' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($pedido_info['estado']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <h5>Productos</h5>
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
                                        $total = 0;
                                        while ($detalle = $detalles_pedido->fetch_assoc()): 
                                            $subtotal = $detalle['cantidad'] * $detalle['precio'];
                                            $total += $subtotal;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                                            <td><?php echo $detalle['cantidad']; ?></td>
                                            <td>$<?php echo number_format($detalle['precio'], 2); ?></td>
                                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <?php if ($pedido_info['estado'] == 'pendiente'): ?>
                            <div class="mt-3">
                                <button class="btn btn-success" onclick="confirmarAccion('aprobar', <?php echo $pedido_info['id_pedido']; ?>)">
                                    <i class="fas fa-check"></i> Aprobar Pedido
                                </button>
                                <button class="btn btn-danger" onclick="confirmarAccion('rechazar', <?php echo $pedido_info['id_pedido']; ?>)">
                                    <i class="fas fa-times"></i> Rechazar Pedido
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Filtro de fechas -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-filter me-1"></i>
                            Filtros de Búsqueda
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_inicio" class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt me-1"></i>Fecha Inicio
                                        </label>
                                        <input type="date" class="form-control shadow-sm" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_fin" class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt me-1"></i>Fecha Fin
                                        </label>
                                        <input type="date" class="form-control shadow-sm" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estado" class="form-label fw-bold">
                                            <i class="fas fa-tag me-1"></i>Estado
                                        </label>
                                        <select class="form-select shadow-sm" id="estado" name="estado">
                                            <option value="">Todos los estados</option>
                                            <option value="pendiente" <?php echo $estado_filtro === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="pagado" <?php echo $estado_filtro === 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                                            <option value="cancelado" <?php echo $estado_filtro === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="busqueda" class="form-label fw-bold">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </label>
                                        <input type="text" class="form-control shadow-sm" id="busqueda" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="ID Pedido o Usuario">
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo me-1"></i>Limpiar Filtros
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter me-1"></i>Aplicar Filtros
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <h4>Total Ventas</h4>
                                    <h2><?php echo number_format($resumen['total_ventas']); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">
                                    <h4>Monto Total</h4>
                                    <h2>$<?php echo number_format($resumen['monto_total'], 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-info text-white mb-4">
                                <div class="card-body">
                                    <h4>Promedio</h4>
                                    <h2>$<?php echo number_format($resumen['promedio_venta'], 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-warning text-white mb-4">
                                <div class="card-body">
                                    <h4>Venta Máxima</h4>
                                    <h2>$<?php echo number_format($resumen['venta_maxima'], 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de resultados -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            Lista de Pedidos
                        </div>
                        <div class="card-body">
                            <?php if ($pedidos->num_rows > 0): ?>
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Pedido</th>
                                            <th>ID Usuario</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $pedido['id_pedido']; ?></td>
                                                <td><?php echo $pedido['id_usuario']; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                                <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $pedido['estado'] == 'pendiente' ? 'warning' : 
                                                            ($pedido['estado'] == 'pagado' ? 'success' : 'danger'); 
                                                    ?>">
                                                        <?php echo ucfirst($pedido['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    
                                                    <button class="btn btn-primary btn-sm" onclick="cargarDetallesPedido(<?php echo $pedido['id_pedido']; ?>)">Ver detalles</button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>

                                <!-- Paginación -->
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                            <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $i; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&busqueda=<?php echo urlencode($busqueda); ?>&estado=<?php echo urlencode($estado_filtro); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php else: ?>
                                <div class="alert alert-info text-center">
                                    No hay datos disponibles para el período seleccionado.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Inicializar datepicker
        flatpickr("input[type=date]", {
            dateFormat: "Y-m-d",
            locale: "es"
        });

        // Función para cargar los detalles del pedido
        function cargarDetallesPedido(idPedido) {
            $.ajax({
                url: 'obtener_detalles_pedido.php',
                type: 'GET',
                data: { id_pedido: idPedido },
                success: function(response) {
                    $('#modalDetalles .modal-body').html(response);
                    $('#modalDetalles').modal('show');
                },
                error: function() {
                    alert('Error al cargar los detalles del pedido');
                }
            });
        }

        // Función para confirmar acción
        function confirmarAccion(accion, idPedido) {
            if (confirm('¿Está seguro que desea ' + (accion === 'aprobar' ? 'aprobar' : 'rechazar') + ' este pedido?')) {
                window.location.href = 'pedidos.php?' + accion + '=' + idPedido;
            }
        }
    </script>

    <!-- Modal de Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles del Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>