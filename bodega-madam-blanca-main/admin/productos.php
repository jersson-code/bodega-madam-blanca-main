<?php
session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol de administrador (id_rol == 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    // Si no tiene el rol correcto, redirigir al usuario a la página principal
    header("Location: ../index.php");
    exit();
}

include("./conexion.php");


// Obtener categorías para el select
$categorias = $conexion->query("SELECT * FROM Categorias");

// Procesar búsqueda
$busqueda = '';
if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
}

// Construir la consulta SQL
$sql = "SELECT * FROM Productos";
if (!empty($busqueda)) {
    $sql .= " WHERE nombre_producto LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%'";
}
$result_productos = $conexion->query($sql);

// Agregar un producto
if (isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];

    // Subir imagen
    $imagen = $_FILES['imagen']['name'];
    $ruta = '../assets/img/' . basename($imagen);
    move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);

    $conexion->query("INSERT INTO Productos (nombre_producto, descripcion, precio, stock, imagen_url, id_categoria, estado) 
                  VALUES ('$nombre', '$descripcion', '$precio', '$stock', '$ruta', '$id_categoria', 1)");
    echo "<script>alert('Registro exitoso.'); window.location.href='productos.php';</script>";
    exit();
}

// Deshabilitar un producto
if (isset($_GET['deshabilitar'])) {
    $id = $_GET['deshabilitar'];
    $conexion->query("UPDATE Productos SET estado = 0 WHERE id_producto = $id");
    header('Location: productos.php');
}

// Habilitar un producto
if (isset($_GET['habilitar'])) {
    $id = $_GET['habilitar'];
    $conexion->query("UPDATE Productos SET estado = 1 WHERE id_producto = $id");
    header('Location: productos.php');
}

// Obtener el producto a editar
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $producto_editar = $conexion->query("SELECT * FROM Productos WHERE id_producto = $id_editar")->fetch_assoc();
}

// Actualizar un producto
if (isset($_POST['actualizar'])) {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];

    // Subir nueva imagen si se proporciona
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = $_FILES['imagen']['name'];
        $ruta = './assets/img/' . basename($imagen);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);
        $conexion->query("UPDATE Productos SET nombre_producto='$nombre', descripcion='$descripcion', precio='$precio', 
                      stock='$stock', imagen_url='$ruta', id_categoria='$id_categoria' WHERE id_producto='$id_producto'");
    } else {
        $conexion->query("UPDATE Productos SET nombre_producto='$nombre', descripcion='$descripcion', precio='$precio', 
                      stock='$stock', id_categoria='$id_categoria' WHERE id_producto='$id_producto'");
    }
    header('Location: productos.php');
}

// Actualizar stock de un producto
if (isset($_POST['actualizar_stock'])) {
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];
    $conexion->query("UPDATE Productos SET stock = stock + $cantidad WHERE id_producto = $id_producto");
    header('Location: productos.php');
}

$result = $conexion->query("SELECT * FROM Productos");
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
                                Gestionar Productos
                            </div>
                            <div class="d-flex">
                                <form method="GET" class="me-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="buscar" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($busqueda); ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>
                        </div>

                        <!-- Modal para Agregar Producto -->
                        <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Nuevo Producto</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <!-- Nombre del producto -->
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre del Producto</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto" required>
                                            </div>

                                            <!-- Descripción -->
                                            <div class="mb-3">
                                                <label for="descripcion" class="form-label">Descripción</label>
                                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Descripción" required></textarea>
                                            </div>

                                            <!-- Precio -->
                                            <div class="mb-3">
                                                <label for="precio" class="form-label">Precio</label>
                                                <input type="number" class="form-control" id="precio" name="precio" placeholder="Precio" step="0.01" required>
                                            </div>

                                            <!-- Stock -->
                                            <div class="mb-3">
                                                <label for="stock" class="form-label">Stock</label>
                                                <input type="number" class="form-control" id="stock" name="stock" placeholder="Stock" required>
                                            </div>

                                            <!-- Categoría -->
                                            <div class="mb-3">
                                                <label for="id_categoria" class="form-label">Categoría</label>
                                                <select class="form-select" id="id_categoria" name="id_categoria" required>
                                                    <?php while ($cat = $categorias->fetch_assoc()) { ?>
                                                        <option value="<?php echo $cat['id_categoria']; ?>"><?php echo $cat['nombre_categoria']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Imagen -->
                                            <div class="mb-3">
                                                <label for="imagen" class="form-label">Imagen del Producto</label>
                                                <input type="file" class="form-control" id="imagen" name="imagen" required>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" name="agregar" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Agregar Producto
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Imagenes</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Descripción</th>
                                        <th scope="col">Precio.Un</th>
                                        <th scope="col">Stock</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result_productos->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="<?php echo $row['imagen_url']; ?>" width="100"></td>
                                            <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                            <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                            <td>$<?php echo number_format($row['precio'], 2); ?></td>
                                            <td><?php if ($row['stock'] > 0) {
                                                    echo $row['stock'];
                                                } else {
                                                    echo $firowla['stock'] = 0;
                                                }  ?></td>
                                            <td>
                                                <?php if ($row['estado'] == 1): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarProductoModal<?php echo $row['id_producto']; ?>" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#actualizarStockModal<?php echo $row['id_producto']; ?>" title="Actualizar Stock">
                                                        <i class="fas fa-boxes"></i>
                                                    </button>
                                                    <?php if ($row['estado'] == 1): ?>
                                                        <a class="btn btn-danger btn-sm" href="productos.php?deshabilitar=<?php echo $row['id_producto'] ?>" onclick="return confirm('¿Estás seguro de deshabilitar este producto?');" title="Deshabilitar">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a class="btn btn-success btn-sm" href="productos.php?habilitar=<?php echo $row['id_producto'] ?>" onclick="return confirm('¿Estás seguro de habilitar este producto?');" title="Habilitar">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal de Edición para cada producto -->
                                        <div class="modal fade" id="editarProductoModal<?php echo $row['id_producto']; ?>" tabindex="-1" aria-labelledby="editarProductoModalLabel<?php echo $row['id_producto']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editarProductoModalLabel<?php echo $row['id_producto']; ?>">Editar Producto</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                                                            
                                                            <!-- Nombre del producto -->
                                                            <div class="mb-3">
                                                                <label for="nombre" class="form-label">Nombre del Producto</label>
                                                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['nombre_producto']); ?>" required>
                                                            </div>

                                                            <!-- Descripción -->
                                                            <div class="mb-3">
                                                                <label for="descripcion" class="form-label">Descripción</label>
                                                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($row['descripcion']); ?></textarea>
                                                            </div>

                                                            <!-- Precio -->
                                                            <div class="mb-3">
                                                                <label for="precio" class="form-label">Precio</label>
                                                                <input type="number" class="form-control" id="precio" name="precio" value="<?php echo $row['precio']; ?>" step="0.01" required>
                                                            </div>

                                                            <!-- Stock -->
                                                            <div class="mb-3">
                                                                <label for="stock" class="form-label">Stock</label>
                                                                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $row['stock']; ?>" required>
                                                            </div>

                                                            <!-- Categoría -->
                                                            <div class="mb-3">
                                                                <label for="id_categoria" class="form-label">Categoría</label>
                                                                <select class="form-select" id="id_categoria" name="id_categoria" required>
                                                                    <?php 
                                                                    $categorias_edit = $conexion->query("SELECT * FROM Categorias");
                                                                    while ($cat = $categorias_edit->fetch_assoc()) { 
                                                                    ?>
                                                                        <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $row['id_categoria']) ? 'selected' : ''; ?>>
                                                                            <?php echo $cat['nombre_categoria']; ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>

                                                            <!-- Imagen actual -->
                                                            <div class="mb-3">
                                                                <label class="form-label">Imagen Actual</label>
                                                                <img src="<?php echo $row['imagen_url']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                                            </div>

                                                            <!-- Nueva imagen -->
                                                            <div class="mb-3">
                                                                <label for="imagen" class="form-label">Nueva Imagen (opcional)</label>
                                                                <input type="file" class="form-control" id="imagen" name="imagen">
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="actualizar" class="btn btn-primary">
                                                                    <i class="fas fa-save"></i> Guardar Cambios
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal para Actualizar Stock -->
                                        <div class="modal fade" id="actualizarStockModal<?php echo $row['id_producto']; ?>" tabindex="-1" aria-labelledby="actualizarStockModalLabel<?php echo $row['id_producto']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="actualizarStockModalLabel<?php echo $row['id_producto']; ?>">Actualizar Stock</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST">
                                                            <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="producto" class="form-label">Producto</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['nombre_producto']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="stock_actual" class="form-label">Stock Actual</label>
                                                                <input type="number" class="form-control" value="<?php echo $row['stock']; ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="cantidad" class="form-label">Cantidad a Agregar</label>
                                                                <input type="number" class="form-control" name="cantidad" min="1" required>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="actualizar_stock" class="btn btn-primary">
                                                                    <i class="fas fa-save"></i> Actualizar Stock
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <footer class=" py-4 bg-light mt-auto">
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