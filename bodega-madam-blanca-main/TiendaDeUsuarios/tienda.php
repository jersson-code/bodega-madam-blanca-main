<?php
session_start();

include('../admin/conexion.php');

// Verificar la conexión
if ($conexion->connect_error) {
	die("Conexión fallida: " . $conexion->connect_error);
}

// // Verifica si el usuario ha iniciado sesión y tiene el rol de administrador (id_rol == 2)
// if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
//     // Si no tiene el rol correcto, redirigir al usuario a la página principal
//     header("Location: ../index.php");
//     exit();
// }


// Obtener las categorías para el filtro de búsqueda
$categorias = $conexion->query("SELECT * FROM categorias");

// Inicializar variables
$search = '';
$category_filter = '';

// Procesar formulario de búsqueda
if (isset($_GET['buscar'])) {
	$search = trim($_GET['nombre']);
	$category_filter = $_GET['categoria'];
}

// Consulta para obtener productos con stock y categorías activas
$query = "SELECT p.* FROM Productos p 
          INNER JOIN Categorias c ON p.id_categoria = c.id_categoria 
          WHERE p.stock > 0 AND c.estado = 1 AND p.estado = 1";

// Agregar filtros si existen
if (!empty($search)) {
	$search = $conexion->real_escape_string($search);
	$query .= " AND (p.nombre_producto LIKE '%$search%' OR p.descripcion LIKE '%$search%')";
}

if (!empty($category_filter) && $category_filter !== 'todas') {
	$category_filter = $conexion->real_escape_string($category_filter);
	$query .= " AND p.id_categoria = '$category_filter'";
}

$query .= " ORDER BY p.nombre_producto ASC";
$result = $conexion->query($query);


?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1.0" />
	<title>Tienda - Madam Blanca</title>

	<!-- ===== CSS ===== -->
	<link href="../assets/css/style.css" rel="stylesheet">
	<link href="../assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="../assets/css/principal.css" rel="stylesheet">
	<!-- ===== favicon ===== -->
	<link href="../assets/icon/image-removebg-preview.png" rel="stylesheet">
	<!-- ===== Explorador de iconos CSS ===== -->
	<link rel="icon" type="image/png" href="../assets/icon/logo.png">
	
</head>

<body>
<?php include("./header.php"); ?>

	<main>
		<section class="container my-5">
			<h1 class="text-center mb-4">Tienda de Productos</h1>

			

			<!-- Formulario de búsqueda -->
			<form method="GET" class="search-form mb-4">
				<div class="row g-3">
					<div class="col-md-6">
						<input type="text" class="form-control" name="nombre" placeholder="Buscar por nombre o descripción..." value="<?php echo htmlspecialchars($search); ?>">
					</div>
					<div class="col-md-4">
						<select name="categoria" class="form-select">
							<option value="todas">Todas las categorías</option>
							<?php while ($cat = $categorias->fetch_assoc()) { ?>
								<option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($category_filter == $cat['id_categoria']) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($cat['nombre_categoria']); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-2">
						<button type="submit" name="buscar" class="btn btn-primary w-100">Buscar</button>
					</div>
				</div>
			</form>

			<!-- Lista de productos -->
			<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 ">
				<?php if ($result->num_rows > 0): ?>
					<?php while ($producto = $result->fetch_assoc()) { ?>
						<div class="col">
							<div class="card h-100">
								<img src="<?php echo $producto['imagen_url']; ?>" class="card-img-top" alt="Producto">
								<div class="card-body">
									<h5 class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h5>
									<p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
									<p class="text-success fw-bold">Precio: $<?php echo number_format($producto['precio'], 2); ?></p>
									<p class="text-muted">Stock: <?php echo $producto['stock']; ?></p>
								</div>
								<div class="card-footer">
									<form method="POST" action="agregar_carrito.php" class="d-flex justify-content-between align-items-center">
										<input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
										<input type="number" name="cantidad" min="1" max="<?php echo $producto['stock']; ?>" value="1" class="form-control w-25 me-2" <?php echo (!isset($_SESSION['usuario'])) ? 'disabled' : ''; ?> required>
										<?php if (isset($_SESSION['usuario'])): ?>
											<button type="submit" class="btn btn-success">Agregar al carrito</button>
										<?php else: ?>
											<button type="button" class="btn btn-secondary" disabled>Inicia sesión para comprar</button>
										<?php endif; ?>
									</form>

								</div>
							</div>
						</div>
					<?php } ?>
				<?php else: ?>
					<div class="col-12">
						<p class="alert alert-warning text-center">No se encontraron productos que coincidan con tu búsqueda.</p>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</main>



	<?php include '../footer.php'; ?>

	<script src="../assets/js/ico.js" crossorigin="anonymous"></script>
	<script src="../assets/js/bootstrap/bootstrap.bundle.min.js"></script>
	<script type="module" src="../assets/js/scripts.js"></script>

</body>

</html>


