<?php
session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol de administrador (id_rol == 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    // Si no tiene el rol correcto, redirigir al usuario a la página principal
    header("Location: ../index.php");
    exit();
}

// Incluir conexión a la base de datos
include("./conexion.php");

// Consulta para obtener los productos más vendidos
$query_productos_vendidos = "SELECT p.nombre_producto, SUM(d.cantidad) as total_vendido 
                           FROM detalles d 
                           JOIN productos p ON d.id_producto = p.id_producto 
                           JOIN pedidos ped ON d.id_pedido = ped.id_pedido
                           WHERE ped.estado = 'pagado'
                           GROUP BY p.id_producto 
                           ORDER BY total_vendido DESC 
                           LIMIT 5";
$result_productos_vendidos = $conexion->query($query_productos_vendidos);

// Consulta para contar usuarios totales
$query_total_usuarios = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 2";
$result_total_usuarios = $conexion->query($query_total_usuarios);
$total_usuarios = $result_total_usuarios->fetch_assoc()['total'];

// Consulta para contar jefes de proyecto
$query_total_jefes = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 3";
$result_total_jefes = $conexion->query($query_total_jefes);
$total_jefes = $result_total_jefes->fetch_assoc()['total'];

// Consulta para contar total de productos
$query_total_productos = "SELECT COUNT(*) as total FROM productos WHERE estado = 1";
$result_total_productos = $conexion->query($query_total_productos);
$total_productos = $result_total_productos->fetch_assoc()['total'];

// Consulta para obtener el total de ventas del día actual
$query_ventas_dia = "SELECT COALESCE(SUM(total), 0) as total_ventas 
                     FROM pedidos 
                     WHERE DATE(fecha_pedido) = CURDATE() 
                    AND estado = 'pagado'"; 
$result_ventas_dia = $conexion->query($query_ventas_dia);
$total_ventas_dia = $result_ventas_dia->fetch_assoc()['total_ventas'];

// Consulta para productos con bajo stock
$query_bajo_stock = "SELECT id_producto, nombre_producto, stock, precio 
                     FROM productos 
                     WHERE stock < 20 AND estado = 1 
                     ORDER BY stock ASC";
$result_bajo_stock = $conexion->query($query_bajo_stock);
$total_bajo_stock = $result_bajo_stock->num_rows;

// Consulta para pedidos pendientes
$query_pedidos_pendientes = "SELECT p.id_pedido, p.fecha_pedido, p.total, u.nombre as nombre_usuario, 
                            COUNT(d.id_detalle) as total_productos
                            FROM pedidos p 
                            JOIN usuarios u ON p.id_usuario = u.id_usuario
                            JOIN detalles d ON p.id_pedido = d.id_pedido
                            WHERE p.estado = 'pendiente'
                            GROUP BY p.id_pedido
                            ORDER BY p.fecha_pedido ASC";
$result_pedidos_pendientes = $conexion->query($query_pedidos_pendientes);
$total_pedidos_pendientes = $result_pedidos_pendientes->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Dashboard - Madam Blanca</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../assets/css/styles2.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h1 class="mt-4">Dashboard</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>

                    <?php if ($total_bajo_stock > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>¡Atención!</strong> Hay <?php echo $total_bajo_stock; ?> productos con stock bajo.
                        <button type="button" class="btn btn-warning ms-3" data-bs-toggle="modal" data-bs-target="#stockModal">
                            Ver Reporte de Productos
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <?php if ($total_pedidos_pendientes > 0): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>¡Nuevos Pedidos!</strong> Hay <?php echo $total_pedidos_pendientes; ?> pedidos pendientes de procesar.
                        <button type="button" class="btn btn-info ms-3" data-bs-toggle="modal" data-bs-target="#pedidosModal">
                            Ver Pedidos Pendientes
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">Total Usuarios</div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <div class="small text-white"><?php echo $total_usuarios; ?> usuarios</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-warning text-white mb-4">
                                <div class="card-body">Jefes de Proyecto</div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <div class="small text-white"><?php echo $total_jefes; ?> jefes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">Total Productos</div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <div class="small text-white"><?php echo $total_productos; ?> productos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-info text-white mb-4">
                                <div class="card-body">Ventas del Día</div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <div class="small text-white">$<?php echo number_format($total_ventas_dia, 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    Productos Más Vendidos
                                </div>
                                <div class="card-body">
                                    <canvas id="productosVendidosChart" width="100%" height="40"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Madam Blanca 2024</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modal para productos con bajo stock -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockModalLabel">Productos con Bajo Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Stock Actual</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $result_bajo_stock->data_seek(0);
                                while($producto = $result_bajo_stock->fetch_assoc()): 
                                    $estado_class = $producto['stock'] < 10 ? 'danger' : 'warning';
                                ?>
                                <tr>
                                    <td><?php echo $producto['id_producto']; ?></td>
                                    <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                    <td><span class="badge bg-<?php echo $estado_class; ?>"><?php echo $producto['stock']; ?></span></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td>
                                        <?php if($producto['stock'] < 10): ?>
                                            <span class="text-danger">Crítico</span>
                                        <?php else: ?>
                                            <span class="text-warning">Bajo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="productos.php" class="btn btn-primary">Ir a Gestión de Productos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para pedidos pendientes -->
    <div class="modal fade" id="pedidosModal" tabindex="-1" aria-labelledby="pedidosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pedidosModalLabel">Pedidos Pendientes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Pedido</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total Productos</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $result_pedidos_pendientes->data_seek(0);
                                while($pedido = $result_pedidos_pendientes->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $pedido['id_pedido']; ?></td>
                                    <td><?php echo htmlspecialchars($pedido['nombre_usuario']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                    <td><?php echo $pedido['total_productos']; ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <a class="btn btn-primary btn-sm" href="pedidos.php?detalles=<?php echo $pedido['id_pedido']; ?>">Ver detalles</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/scripts.js"></script>
    <script src="../assets/js/graficos.js"></script>
    <!-- Datos para el gráfico -->
    <div id="productosVendidosData" style="display: none;">
        <?php 
        $result_productos_vendidos->data_seek(0);
        while($row = $result_productos_vendidos->fetch_assoc()): 
        ?>
            <span data-producto-nombre="<?php echo htmlspecialchars($row['nombre_producto']); ?>"></span>
            <span data-producto-cantidad="<?php echo $row['total_vendido']; ?>"></span>
        <?php endwhile; ?>
    </div>
</body>

</html>