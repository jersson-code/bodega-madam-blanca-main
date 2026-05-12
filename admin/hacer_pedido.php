<?php
include("./conexion.php");
session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol de administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Verificar si se proporcionó un ID de proyecto
if (!isset($_GET['id_proyecto'])) {
    header("Location: proyectos.php");
    exit();
}

$id_proyecto = intval($_GET['id_proyecto']);

// Obtener información del proyecto
$stmt_proyecto = $conexion->prepare("SELECT * FROM proyectos WHERE id_proyecto = ?");
$stmt_proyecto->bind_param("i", $id_proyecto);
$stmt_proyecto->execute();
$proyecto = $stmt_proyecto->get_result()->fetch_assoc();

if (!$proyecto) {
    header("Location: proyectos.php");
    exit();
}

// Procesar el formulario de pedido
if (isset($_POST['realizar_pedido'])) {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);
    
    // Verificar stock disponible
    $stmt_stock = $conexion->prepare("SELECT stock FROM productos WHERE id_producto = ?");
    $stmt_stock->bind_param("i", $id_producto);
    $stmt_stock->execute();
    $stock_disponible = $stmt_stock->get_result()->fetch_assoc()['stock'];
    
    if ($stock_disponible >= $cantidad) {
        // Actualizar stock en productos
        $conexion->query("UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");
        
        // Verificar si ya existe el producto en el proyecto
        $stmt_check = $conexion->prepare("SELECT cantidad FROM stock_proyectos WHERE id_proyecto = ? AND id_producto = ?");
        $stmt_check->bind_param("ii", $id_proyecto, $id_producto);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar cantidad existente
            $conexion->query("UPDATE stock_proyectos SET cantidad = cantidad + $cantidad WHERE id_proyecto = $id_proyecto AND id_producto = $id_producto");
        } else {
            // Insertar nuevo registro
            $stmt_insert = $conexion->prepare("INSERT INTO stock_proyectos (id_proyecto, id_producto, cantidad) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iii", $id_proyecto, $id_producto, $cantidad);
            $stmt_insert->execute();
        }
        
        echo "<script>alert('Pedido realizado con éxito.'); window.location.href='proyectos.php';</script>";
    } else {
        echo "<script>alert('Stock insuficiente para realizar el pedido.');</script>";
    }
}

// Obtener productos disponibles
$productos = $conexion->query("SELECT * FROM productos WHERE stock > 0 ORDER BY nombre_producto");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Hacer Pedido - <?= htmlspecialchars($proyecto['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../assets/css/styles2.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="./admin.php">Administrator</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    </nav>
    
    <div id="layoutSidenav">
        <?php include("./nav.php"); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Hacer Pedido</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Pedido para Proyecto: <?= htmlspecialchars($proyecto['nombre']) ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="id_producto" class="form-label">Producto</label>
                                        <select class="form-select" id="id_producto" name="id_producto" required>
                                            <option value="">Seleccione un producto</option>
                                            <?php while ($producto = $productos->fetch_assoc()): ?>
                                                <option value="<?= $producto['id_producto'] ?>">
                                                    <?= htmlspecialchars($producto['nombre_producto']) ?> 
                                                    (Stock disponible: <?= $producto['stock'] ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cantidad" class="form-label">Cantidad</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" name="realizar_pedido" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Realizar Pedido
                                    </button>
                                    <a href="proyectos.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Volver
                                    </a>
                                </div>
                            </form>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <script>
    // Validación del formulario
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html> 