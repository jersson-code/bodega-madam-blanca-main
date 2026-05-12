<?php
// Inclusión de archivos necesarios
include("./conexion.php");
session_start();

// Verificación de autenticación y permisos
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    // Si no tiene el rol correcto, redirigir al usuario a la página principal
    header("Location: ../index.php");
    exit();
}

// Verificación y creación de columna estado si no existe
$check_column = $conexion->query("SHOW COLUMNS FROM Usuarios LIKE 'estado'");
if ($check_column->num_rows == 0) {
    $conexion->query("ALTER TABLE Usuarios ADD COLUMN estado BOOLEAN DEFAULT TRUE");
}

// Obtener el término de búsqueda si existe
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Construir la consulta SQL
$sql = "SELECT u.*, r.descripcion as rol_nombre FROM Usuarios u LEFT JOIN Roles r ON u.id_rol = r.id_rol";
if (!empty($busqueda)) {
    $busqueda_param = '%' . $busqueda . '%';
    $sql .= " WHERE u.nombre LIKE ? OR u.email LIKE ? OR r.descripcion LIKE ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $busqueda_param, $busqueda_param, $busqueda_param);
    $stmt->execute();
    $usuarios = $stmt->get_result();
} else {
    $usuarios = $conexion->query($sql);
}

// Obtener todos los roles
$roles = $conexion->query("SELECT * FROM roles");


// Eliminar un usuario
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']); // Convertir a entero para mayor seguridad
    $stmt = $conexion->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: usuarios.php');
    exit();
}

// Obtener el usuario a editar
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']); // Convertir a entero para mayor seguridad
    $stmt = $conexion->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $usuario_editar = $stmt->get_result()->fetch_assoc();
}
// Actualizar un usuario
if (isset($_POST['actualizar'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $rol = intval($_POST['rol']);
    $telefono = intval($_POST['telefono']);

    // Consulta SQL corregida
    $stmt = $conexion->prepare("UPDATE Usuarios SET nombre = ?, email = ?, id_rol = ?, telefono = ? WHERE id_usuario = ?");

    // Ajustar los tipos y parámetros
    $stmt->bind_param("ssiii", $nombre, $email, $rol, $telefono, $id_usuario);
    $stmt->execute();

    header('Location: usuarios.php');
    exit();
}

if (isset($_POST['actualizar'])) {
    if (actualizarUsuario(
        $conexion,
        intval($_POST['id_usuario']),
        trim($_POST['nombre']),
        trim($_POST['email']),
        intval($_POST['rol']),
        intval($_POST['telefono'])
    )) {
        $mensaje = 'Usuario actualizado correctamente';
        $tipo_mensaje = 'success';
    }
}

// Cambiar estado de un usuario
if (isset($_GET['cambiar_estado'])) {
    $id = intval($_GET['cambiar_estado']);
    $stmt = $conexion->prepare("UPDATE Usuarios SET estado = NOT estado WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: usuarios.php');
    exit();
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
    <title>Admin-Esencias-Chocoanas</title>
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

                    <!-- Modal de Roles -->
                    <div class="modal fade" id="rolesModal" tabindex="-1" aria-labelledby="rolesModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rolesModalLabel">Roles del Sistema</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Rol</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $roles_query = $conexion->query("SELECT * FROM Roles");
                                                while ($rol = $roles_query->fetch_assoc()) { 
                                                ?>
                                                    <tr>
                                                        <td><?php echo $rol['id_rol']; ?></td>
                                                        <td><?php echo $rol['descripcion']; ?></td>
                                                    </tr>
                                                <?php } ?>
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

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-table me-1"></i>
                                Gestionar Usuarios
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rolesModal">
                                <i class="fas fa-user-tag me-2"></i>Ver Roles
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Barra de búsqueda -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <form method="GET" class="d-flex gap-2">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" 
                                                   name="busqueda" 
                                                   class="form-control" 
                                                   placeholder="Buscar por nombre, email o rol..." 
                                                   value="<?php echo htmlspecialchars($busqueda); ?>">
                                        </div>
                                        <button class="btn btn-primary" type="submit">
                                            Buscar
                                        </button>
                                        <?php if (!empty($busqueda)): ?>
                                            <a href="usuarios.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Limpiar
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                            <?php if (isset($usuario_editar)): ?>
                                <!-- Formulario mejorado -->
                                <form method="POST" class="container p-4 bg-light rounded shadow">
                                    <input type="hidden" name="id_usuario" value="<?php echo $usuario_editar['id_usuario']; ?>">

                                    <!-- Nombre -->
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $usuario_editar['nombre']; ?>" required>
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $usuario_editar['email']; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="number" class="form-label">numero telefonico</label>
                                        <input type="emnumberail" class="form-control" id="telefono" name="telefono" value="<?php echo $usuario_editar['telefono']; ?>" required>
                                    </div>

                                    <!-- Rol -->
                                    <div class="mb-3">
                                        <label for="rol" class="form-label">Rol</label>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <?php while ($rol = $roles->fetch_assoc()): ?>
                                                <option value="<?php echo $rol['id_rol']; ?>" <?php echo ($usuario_editar['id_rol'] == $rol['id_rol']) ? 'selected' : ''; ?>>
                                                    <?php echo $rol['descripcion']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" name="actualizar" class="btn btn-success">
                                            <i class="fas fa-save"></i> Actualizar Usuario
                                        </button>
                                        <a href="usuarios.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            <?php endif; ?>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Rol</th>
                                            <th scope="col">Teléfono</th>
                                            <th scope="col">Fecha de registro</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $usuario['id_usuario']; ?></td>
                                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                                <td><?php echo $usuario['fecha_registro']; ?></td>
                                                <td>
                                                    <?php 
                                                    // Verificar si existe la columna estado y tiene un valor
                                                    $estado = isset($usuario['estado']) ? $usuario['estado'] : 1;
                                                    if ($estado == 1): 
                                                    ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="usuarios.php?editar=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="usuarios.php?cambiar_estado=<?php echo $usuario['id_usuario']; ?>" 
                                                       class="btn btn-sm <?php echo $estado == 1 ? 'btn-warning' : 'btn-success'; ?>"
                                                       onclick="return confirm('¿Estás seguro de <?php echo $estado == 1 ? 'deshabilitar' : 'habilitar'; ?> esta cuenta?');">
                                                        <i class="fas <?php echo $estado == 1 ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
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