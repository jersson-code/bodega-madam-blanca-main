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

// Consulta para obtener los productos asignados al proyecto del Jefe de Obra
$stmt_productos = $conexion->prepare("
    SELECT p.id_producto, p.nombre_producto, p.descripcion, pp.cantidad, p.imagen_url
    FROM stock_proyectos pp
    JOIN productos p ON pp.id_producto = p.id_producto
    WHERE pp.id_proyecto = ? AND pp.cantidad > 0
");
$stmt_productos->bind_param("i", $proyecto['id_proyecto']);
$stmt_productos->execute();
$productos = $stmt_productos->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Productos del Proyecto</title>
    <link rel="stylesheet" href="../assets/css/styles2.css">
    <link rel="stylesheet" href="../assets/css/productosProyecto.css">
    <link rel="icon" type="image/png" href="../assets/icon/logo.png">
    <link href="./assets/css/contacto.css" rel="stylesheet">

</head>

<body>
    <header class="bg-primary text-white text-center py-4">
        <div class="container">
            <h1 class="display-4">Productos Disponibles para el Proyecto</h1>
            <h2 class="h4 mb-3">Proyecto: <?php echo htmlspecialchars($proyecto['nombre']); ?></h2>
            <p class="mb-3">Bienvenido, <?php echo $_SESSION['usuario'] ?? 'Usuario'; ?> | 
                <a href="../logout.php" class="text-warning">Cerrar sesión</a>
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="carrito.php" class="btn btn-warning btn-lg px-4 py-2">
                    <i class="fas fa-shopping-cart me-2"></i>Ver Carrito
                </a>
                <a href="estado_proyecto.php" class="btn btn-info btn-lg px-4 py-2 text-white">
                    <i class="fas fa-clipboard-list me-2"></i>Estado de Pedidos
                </a>
            </div>
        </div>
    </header>

    <main class="container py-5">
        <div class="row g-4">
            <?php if ($productos->num_rows > 0): ?>
                <?php while ($producto = $productos->fetch_assoc()): ?>
                    <?php if ($producto['cantidad'] > 0): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-img-top-container">
                                <img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                <p class="text-muted mb-3">Cantidad disponible: <?php echo $producto['cantidad']; ?></p>

                                <form method="POST" action="agregar_carrito.php" class="mt-auto">
                                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                    <input type="hidden" name="id_proyecto" value="<?php echo $proyecto['id_proyecto']; ?>">
                                    <div class="mb-3">
                                        <label for="cantidad" class="form-label">Cantidad:</label>
                                        <input type="number" 
                                               name="cantidad" 
                                               min="1" 
                                               max="<?php echo $producto['cantidad']; ?>" 
                                               value="1" 
                                               class="form-control" 
                                               required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Agregar al carrito</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <p class="mb-0">No hay productos asignados a este proyecto.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>