<?php
session_start();

include('./admin/conexion.php');

if (isset($_POST['ingresar'])) {
	// Obtener datos del formulario
	$email = trim($_POST['email']);
	$password = trim($_POST['password']);

	// Consultar usuario en la base de datos
	$query = $conexion->prepare("SELECT id_usuario, nombre, contrasena, id_rol, estado FROM Usuarios WHERE email = ?");
	$query->bind_param("s", $email);
	$query->execute();
	$result = $query->get_result();
	$user = $result->fetch_assoc();

	// Verificar credenciales y estado de la cuenta
	if ($user && password_verify($password, $user['contrasena'])) {
		// Verificar si la cuenta está activa
		if ($user['estado'] == 0) {
			header("Location: login.php?error=2");
			exit;
		}

		// Guardar datos en la sesión
		$_SESSION['usuario'] = $user['nombre'];
		$_SESSION['id_usuario'] = $user['id_usuario'];
		$_SESSION['id_rol'] = $user['id_rol'];

		// Redirigir según el rol
		switch ($user['id_rol']) {
			case 1: // Administrador
				header("Location: ./admin/admin.php");
				break;
			case 2: // Cliente
				header("Location: ./TiendaDeUsuarios/tienda.php");
				break;
			case 3: // Jefe de Obra
				header("Location: ./TiendaDeProyectos/productosProyecto.php");
				break;
			default: // Rol no reconocido
				echo "<script>alert('Rol no válido.'); window.location.href='login.php';</script>";
				break;
		}
		exit;
	} else {
		// Credenciales inválidas
		header("Location: login.php?error=1");
		exit;
	}
}


if (isset($_POST['registrar'])) {
	// Obtener datos del formulario
	$nombre = trim($_POST['nombre']);
	$email = trim($_POST['email']);
	$contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
	$rol = intval($_POST['rol']); // Rol del usuario (2 = cliente, 3 = jefe de obra)
	$id_proyecto = isset($_POST['id_proyecto']) ? intval($_POST['id_proyecto']) : null;
	$telefono = intval($_POST['telefono']);

	// Verificar si el correo ya está registrado
	$checkEmail = $conexion->prepare("SELECT email FROM Usuarios WHERE email = ?");
	$checkEmail->bind_param("s", $email);
	$checkEmail->execute();
	$checkEmail->store_result();

	if ($checkEmail->num_rows > 0) {
		// Redirigir con un parámetro de error específico para correo
		header("Location: login.php?email_error=1");
		exit;
	}

	// Validar código de proyecto si el rol es jefe de obra
	if ($rol === 3) {
		$checkProyecto = $conexion->prepare("SELECT id_proyecto FROM Proyectos WHERE id_proyecto = ?");
		$checkProyecto->bind_param("i", $id_proyecto);
		$checkProyecto->execute();
		$checkProyecto->store_result();

		if ($checkProyecto->num_rows === 0) {
			echo "<script>alert('El código del proyecto es incorrecto.'); window.location.href='login.php';</script>";
			exit;
		}
	}

	// Insertar usuario en la tabla Usuarios
	$insertUser = $conexion->prepare("INSERT INTO Usuarios (nombre, email, contrasena, id_rol, telefono) VALUES (?, ?, ?, ?, ?)");
	$insertUser->bind_param("sssis", $nombre, $email, $contrasena, $rol, $telefono);

	if ($insertUser->execute()) {
		// Si es jefe de obra, vincularlo con el proyecto
		if ($rol === 3) {
			$id_usuario = $conexion->insert_id; // Obtener el ID del usuario recién registrado
			$insertRelacion = $conexion->prepare("INSERT INTO Jefes_Obra_Proyectos (id_usuario, id_proyecto) VALUES (?, ?)");
			$insertRelacion->bind_param("ii", $id_usuario, $id_proyecto);
			$insertRelacion->execute();
		}

		echo "<script>alert('Registro exitoso.'); window.location.href='login.php';</script>";
	} else {
		echo "<script>alert('Error al registrar.');</script>";
	}
}


?>
<!DOCTYPE php>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1.0" />
	<title>Esencia Chocoana</title>
	<!-- ===== CSS ===== -->
	<link rel="stylesheet" href="./assets/css/style.css">
	<link href="./assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">

	<!-- ===== favicon ===== -->
	<link rel="shortcut icon" href="./assets/icon/image-removebg-preview.png" type="image/x-icon">
	<!-- ===== Explorador de iconos CSS ===== -->
	<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

	<link rel="icon" type="image/png" href="./assets/icon/logo.png">

</head>

<body>

	<?php include("./header.php"); ?>


	<section>
		<div class="container-pincipal">
			<div class="container2">
				<div class="forms">
					<div class="form login">
						<span class="title">Iniciar sesion</span>
						<form method="POST">
							<div class="input-field">
								<input type="text" name="email" placeholder="Correo electronico" required>
								<i class="uil uil-envelope icon"></i>
							</div>
							<div class="input-field">
								<input type="password" name="password" class="password" placeholder="Ingresa tu contraseña" required>
								<i class="uil uil-lock icon"></i>
							</div>
							
							<!-- Nuevo div para mostrar mensaje de error -->
							<?php if (isset($_GET['error'])): ?>
								<div class="error-message" style="color: red; text-align: center; margin-top: 10px;">
									<?php 
									if ($_GET['error'] == 1) {
										echo "Usuario o contraseña incorrectos.";
									} elseif ($_GET['error'] == 2) {
										echo "Su cuenta ha sido deshabilitada. Por favor, contacte al administrador.";
									}
									?>
								</div>
							<?php endif; ?>

							<div class="checkbox-text">
								<div class="checkbox-content">
									<input type="checkbox" id="logCheck">
									<label for="logCheck" class="text"></label>
								</div>

								<a href="#" class="text">Olvido su contraseña?</a>
							</div>
							<div class="input-field button">
								<input name="ingresar" type="submit" value="Iniciar sesion">
							</div>
						</form>


						<div class="login-signup">
							<span class="text">¿No es miembro?
								<a href="#" class="text registro">Regístrese ahora</a>
							</span>
						</div>
					</div>
					<!-- Formulario de inscripción -->
					<div class="form signup">
						<span class="title">Registro</span>
						<form method="POST">
							<div class="input-field">
								<input type="text" name="nombre" placeholder="Nombre" required>
								<i class="uil uil-user icon"></i>
							</div>
							<div class="input-field">
								<input type="email" name="email" placeholder="Correo electrónico" required>
								<i class="uil uil-envelope icon"></i>
							</div>
							<div class="input-field">
								<input type="password" name="contrasena" placeholder="Crea una contraseña" required>
								<i class="uil uil-lock icon"></i>
							</div>
							<div class="input-field">
								<input type="tel" name="telefono" placeholder="Teléfono" required>
								<i class="uil uil-phone icon"></i>
							</div>
							<!-- Selección de rol -->
							<div class="input-field select-field">
								<select name="rol" id="rol" required onchange="toggleProyectoField()">
									<option value="">Seleccione su rol</option>
									<option value="2">Cliente</option>
									<option value="3">Jefe de Obra</option>
								</select>
								<i class="uil uil-user-circle icon"></i>
							</div>
							<!-- Campo para ID de proyecto -->
							<div class="input-field" id="proyecto-field" style="display: none;">
								<input type="number" name="id_proyecto" id="id_proyecto" placeholder="ID del Proyecto">
								<i class="uil uil-building icon"></i>
							</div>
							<div class="input-field button">
								<input type="submit" name="registrar" value="Registrarse">
							</div>
						</form>
						<div class="login-signup">
							<span class="text">¿Ya eres miembro?
								<a href="#" class="text inicio-de-sesión">Iniciar sesión ahora</a>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>


	<footer class="footer">
		<div class="container container-footer">
			<div class="menu-footer">
				<div class="contact-info">
					<p class="title-footer">Información de Contacto</p>
					<ul>
						<li>
							Dirección: 71 Pennington Lane Vernon Rockville, CT
							06066
						</li>
						<li>Teléfono: 123-456-7890</li>
						<li>Fax: 55555300</li>
						<li>EmaiL: bodegamadamblanca@support.com</li>
					</ul>
					<div class="social-icons">
						<span class="facebook">
							<i class="fa-brands fa-facebook-f"></i>
						</span>
						<span class="twitter">
							<i class="fa-brands fa-twitter"></i>
						</span>
						<span class="youtube">
							<i class="fa-brands fa-youtube"></i>
						</span>
						<span class="pinterest">
							<i class="fa-brands fa-pinterest-p"></i>
						</span>
						<span class="instagram">
							<i class="fa-brands fa-instagram"></i>
						</span>
					</div>
				</div>

				<div class="information">
					<p class="title-footer">Información</p>
					<ul>
						<li><a href="#">Acerca de Nosotros</a></li>
						<li><a href="#">Información Delivery</a></li>
						<li><a href="#">Politicas de Privacidad</a></li>
						<li><a href="#">Términos y condiciones</a></li>
						<li><a href="#">Contactános</a></li>
					</ul>
				</div>

				<div class="my-account">
					<p class="title-footer">Mi cuenta</p>

					<ul>
						<li><a href="#">Mi cuenta</a></li>
						<li><a href="#">Historial de ordenes</a></li>
						<li><a href="#">Lista de deseos</a></li>
						<li><a href="#">Boletín</a></li>
						<li><a href="#">Reembolsos</a></li>
					</ul>
				</div>

				<div class="newsletter">
					<p class="title-footer">Boletín informativo</p>

					<div class="content">
						<p>
							Suscríbete a nuestros boletines ahora y mantente al
							día con nuevas colecciones y ofertas exclusivas.
						</p>
						<input type="email" placeholder="Ingresa el correo aquí...">
						<button>Suscríbete</button>
					</div>
				</div>
			</div>

			<div class="copyright">
				<p>
					Desarrollado por Programación para el mundo &copy; 2022
				</p>

				<img src="img/payment.png" alt="Pagos">
			</div>
		</div>
	</footer>

	<script type="module" src="./assets/js/ico.js"></script>
	<script type="module" src="./assets/js/login.js"></script>
	<script type="module" src="./assets/js/scripts.js"></script>
	<script>
		function toggleProyectoField() {
			const rol = document.getElementById('rol').value;
			const proyectoField = document.getElementById('proyecto-field');
			if (rol === '3') {
				proyectoField.style.display = 'block';
				proyectoField.required = true;
			} else {
				proyectoField.style.display = 'none';
				proyectoField.required = false;
			}
		}
	</script>
</body>

</html>