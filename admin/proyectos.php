<?php

include("./conexion.php");


session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol de administrador (id_rol == 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    // Si no tiene el rol correcto, redirigir al usuario a la página principal
    header("Location: ../index.php");
    exit();
}


// Agregar un nuevo rol
if (isset($_POST['agregar'])) {
    // Obtener datos del formulario
    $id_proyecto = intval($_POST['id_proyecto']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    // Verificar si el correo ya está registrado
    $checkProyecto = $conexion->prepare("SELECT id_proyecto FROM proyectos WHERE id_proyecto = ?");
    $checkProyecto->bind_param("i", $id_proyecto);
    $checkProyecto->execute();
    $checkProyecto->store_result();

    if ($checkProyecto->num_rows > 0) {
        echo "<script>alert('El codigo ya está registrado.'); window.location.href='proyectos.php';</script>";
    } else {
        // Insertar proyecto en la tabla proyectos
        $insertProyecto = $conexion->prepare("INSERT INTO proyectos (id_proyecto, nombre, descripcion) VALUES (?, ?, ?)");
        $insertProyecto->bind_param("iss", $id_proyecto, $nombre, $descripcion);

        if ($insertProyecto->execute()) {
            echo "<script>alert('Registro exitoso.'); window.location.href='proyectos.php';</script>";
        } else {
            echo "<script>alert('Error al registrar.');</script>";
        }
    }
}

// Eliminar un rol
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $deleteProyecto = $conexion->prepare("DELETE FROM proyectos WHERE id_proyecto = ?");
    $deleteProyecto->bind_param("i", $id);

    if ($deleteProyecto->execute()) {
        echo "<script>alert('Proyecto eliminado correctamente.'); window.location.href='proyectos.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el proyecto.');</script>";
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Dashboard - SB Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../assets/css/styles2.css" rel="stylesheet" />
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
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-table me-1"></i>
                                Lista de Proyectos
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProyectoModal">
                                <i class="fas fa-plus me-2"></i>Agregar Proyecto
                            </button>
                        </div>
                        <div class="card-body"> 
                            <!-- Formulario de búsqueda -->
                            <div class="mb-3">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="busqueda" placeholder="Buscar por nombre, descripción o jefe de obra..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Modal para agregar proyecto -->
                            <div class="modal fade" id="agregarProyectoModal" tabindex="-1" aria-labelledby="agregarProyectoModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="agregarProyectoModalLabel">Agregar Nuevo Proyecto</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" id="formProyecto">
                                                <div class="mb-3">
                                                    <label for="id_proyecto" class="form-label">ID del Proyecto</label>
                                                    <input type="number" class="form-control" id="id_proyecto" name="id_proyecto" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="nombre" class="form-label">Nombre del Proyecto</label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción</label>
                                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" form="formProyecto" name="agregar" class="btn btn-primary">Agregar Proyecto</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">ID Proyecto</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Descripción</th>
                                        <th scope="col">Jefe de Obra</th>
                                        <th socope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
                                    $query = "SELECT p.*, u.nombre as nombre_jefe 
                                             FROM proyectos p 
                                             LEFT JOIN jefes_obra_proyectos jp ON p.id_proyecto = jp.id_proyecto 
                                             LEFT JOIN usuarios u ON jp.id_usuario = u.id_usuario";
                                    
                                    if (!empty($busqueda)) {
                                        $busqueda_param = "%$busqueda%";
                                        $query .= " WHERE p.nombre LIKE ? OR p.descripcion LIKE ? OR u.nombre LIKE ?";
                                    }
                                    
                                    $stmt = $conexion->prepare($query);
                                    
                                    if (!empty($busqueda)) {
                                        $stmt->bind_param("sss", $busqueda_param, $busqueda_param, $busqueda_param);
                                    }
                                    
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                    ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id_proyecto']) ?></td>
                                                <td><?= htmlspecialchars($row['nombre']) ?></td>
                                                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                                <td><?= htmlspecialchars($row['nombre_jefe'] ?? 'Sin asignar') ?></td>

                                                <td>
                                                     <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalProductos<?= $row['id_proyecto'] ?>">
                                                        <i class="fas fa-box"></i> Ver Productos
                                                    </button>
                                                    <a href="hacer_pedido.php?id_proyecto=<?= $row['id_proyecto'] ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-shopping-cart"></i> Hacer Pedido
                                                    </a>
                                                </td>

                                            </tr>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay proyectos registrados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Modal para mostrar productos del proyecto -->
                            <?php
                            // Obtener los productos para cada proyecto
                            $stmt_productos = $conexion->prepare("
                                SELECT p.id_proyecto, pr.nombre_producto, pr.descripcion, sp.cantidad, pr.imagen_url
                                FROM proyectos p
                                LEFT JOIN stock_proyectos sp ON p.id_proyecto = sp.id_proyecto
                                LEFT JOIN productos pr ON sp.id_producto = pr.id_producto
                                WHERE p.id_proyecto = ?
                            ");

                            if ($result->num_rows > 0):
                                $result->data_seek(0);
                                while ($row = $result->fetch_assoc()):
                                    $stmt_productos->bind_param("i", $row['id_proyecto']);
                                    $stmt_productos->execute();
                                    $productos = $stmt_productos->get_result();
                                    $total_productos = $productos->num_rows;
                                    $productos_por_pagina = 6;
                                    $total_paginas = ceil($total_productos / $productos_por_pagina);
                            ?>
                                <div class="modal fade" id="modalProductos<?= $row['id_proyecto'] ?>" tabindex="-1" aria-labelledby="modalProductosLabel<?= $row['id_proyecto'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalProductosLabel<?= $row['id_proyecto'] ?>">
                                                    Productos del Proyecto: <?= htmlspecialchars($row['nombre']) ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if ($total_productos > 0): ?>
                                                    <!-- Buscador -->
                                                    <div class="mb-3">
                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="fas fa-search"></i>
                                                            </span>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   id="buscarProducto<?= $row['id_proyecto'] ?>" 
                                                                   placeholder="Buscar producto por nombre o descripción..."
                                                                   onkeyup="filtrarProductos(<?= $row['id_proyecto'] ?>)">
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Producto</th>
                                                                    <th>Descripción</th>
                                                                    <th>Cantidad</th>
                                                                    <th>Imagen</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
                                                                $contador = 0;
                                                                $productos->data_seek(0);
                                                                while ($producto = $productos->fetch_assoc()): 
                                                                ?>
                                                                    <tr class="fila-producto" style="display: <?= $contador < $productos_por_pagina ? '' : 'none' ?>">
                                                                        <td><?= htmlspecialchars($producto['nombre_producto']) ?></td>
                                                                        <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                                                                        <td><?= htmlspecialchars($producto['cantidad']) ?></td>
                                                                        <td>
                                                                            <?php if ($producto['imagen_url']): ?>
                                                                                <img src="<?= htmlspecialchars($producto['imagen_url']) ?>" 
                                                                                     alt="<?= htmlspecialchars($producto['nombre_producto']) ?>" 
                                                                                     style="max-width: 50px; max-height: 50px;">
                                                                            <?php else: ?>
                                                                                Sin imagen
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php 
                                                                $contador++;
                                                                endwhile; 
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <?php if ($total_paginas > 1): ?>
                                                        <div class="d-flex justify-content-center mt-3">
                                                            <nav aria-label="Navegación de productos">
                                                                <ul class="pagination">
                                                                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                                                                        <li class="page-item <?= $i === 1 ? 'active' : '' ?>">
                                                                            <button class="page-link" onclick="cambiarPagina(<?= $i ?>, <?= $row['id_proyecto'] ?>)"><?= $i ?></button>
                                                                        </li>
                                                                    <?php endfor; ?>
                                                                </ul>
                                                            </nav>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        No hay productos asignados a este proyecto.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                endwhile;
                            endif;
                            ?>
                        </div>
                    </div>
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
</body>

</html>