<?php

include("./conexion.php");

session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol de administrador (id_rol == 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    // Si no tiene el rol correcto, redirigir al usuario a la página principal
    header("Location: ../index.php");
    exit();
}

// Verificar si la columna estado existe
$check_column = $conexion->query("SHOW COLUMNS FROM Categorias LIKE 'estado'");
if ($check_column->num_rows == 0) {
    $conexion->query("ALTER TABLE Categorias ADD COLUMN estado BOOLEAN DEFAULT TRUE");
}

// Obtener el término de búsqueda si existe
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Construir la consulta SQL
$sql = "SELECT * FROM Categorias";
if (!empty($busqueda)) {
    $busqueda_param = '%' . $busqueda . '%';
    $sql .= " WHERE nombre_categoria LIKE ? OR descripcion LIKE ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $busqueda_param, $busqueda_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conexion->query($sql);
}

// Agregar una categoría
if (isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $stmt = $conexion->prepare("INSERT INTO Categorias (nombre_categoria, descripcion, estado) VALUES (?, ?, TRUE)");
    $stmt->bind_param("ss", $nombre, $descripcion);
    $stmt->execute();
    header('Location: categorias.php');
}

// Cambiar estado de una categoría
if (isset($_GET['cambiar_estado'])) {
    $id = $_GET['cambiar_estado'];
    $stmt = $conexion->prepare("UPDATE Categorias SET estado = NOT estado WHERE id_categoria = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: categorias.php');
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
                    <!-- Barra de búsqueda -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre o descripción..." value="<?php echo htmlspecialchars($busqueda); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <?php if (!empty($busqueda)): ?>
                                        <a href="categorias.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div >
                                <i class="fas fa-table me-1"></i>
                                Gestionar Categorías
                            </div>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevaCategoriaModal">
                                <i class="fas fa-plus me-2"></i>Nueva categoria
                            </button>
                        </div>

                        <!-- Modal para Nueva categoria -->
                        <div class="modal fade" id="nuevaCategoriaModal" tabindex="-1" aria-labelledby="nuevaCategoriaModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="nuevaCategoriaModalLabel">Agregar Nueva categoria</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" id="formProyecto">
                                            <div class="mb-3">
                                                <input type="text" class="form-control" name="nombre" placeholder="Nombre de la categoría" required>
                                            </div>
                                            <div class="mb-3">
                                                <textarea placeholder="Descripción" class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" form="formProyecto" name="agregar" class="btn btn-success">
                                            <i class="fas fa-save"></i>Agregar Proyecto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if ($result->num_rows > 0) { ?>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo $row['id_categoria']; ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre_categoria']); ?></td>
                                                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                                <td>
                                                    <?php if ($row['estado'] == 1): ?>
                                                        <span class="badge bg-success">Activa</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactiva</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="categorias.php?cambiar_estado=<?php echo $row['id_categoria']; ?>" 
                                                       class="btn btn-sm <?php echo $row['estado'] == 1 ? 'btn-warning' : 'btn-success'; ?>"
                                                       onclick="return confirm('¿Estás seguro de <?php echo $row['estado'] == 1 ? 'deshabilitar' : 'habilitar'; ?> esta categoría?');">
                                                        <i class="fas <?php echo $row['estado'] == 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else {

                                        echo "<tr><td colspan='5'>No hay categorías disponibles</td></tr>";
                                    } ?>
                                </tbody>

                            </table>
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