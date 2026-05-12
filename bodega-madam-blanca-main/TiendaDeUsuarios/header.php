<header>
		<div class="container-hero">
			<div class="container hero">
				<div class="customer-support">
					<i class="fa-solid fa-headset"></i>
					<div class="content-customer-support">
						<span class="text">Soporte al cliente</span>
						<span class="number">123-456-7890</span>
					</div>
				</div>

				<div class="container-logo">
					<h1 class="logo">
						<a href="../index.php">
							<img src="../assets/icon/logo.png" alt="logo principal">
						</a>
					</h1>
				</div>

				<div class="container-user">
					<?php if (!isset($_SESSION['usuario'])): ?>
						<!-- Mostrar ícono de usuario si no está en sesión -->
						<a href="../login.php"><i class="fa-solid fa-user"></i></a>
					<?php else: ?>
						<!-- Mostrar ícono de la web si está en sesión -->
						<div class="dropdown">
							<a href="#" class="fa-solid fa-user sesion " id="userMenuToggle"></a>
							<div class="dropdown-menu" id="userMenu">
								<p class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
								<a href="../logout.php" class="logout">Cerrar sesión</a>
							</div>
						</div>
					<?php endif; ?>

					<!-- Ícono de carrito siempre visible -->
					<a href="./carrito.php"><i class="fa-solid fa-basket-shopping"></i></a>
				</div>

			</div>
		</div>

		<div class="container-navbar">
			<nav class="navbar container">
				<i class="fa-solid fa-bars"></i>
				<ul class="menu">
					<li><a href="../index.php">Inicio</a></li>
					<li><a href="../index.php#nosotros">Nosotros</a></li>
					<li><a href="../index.php#blogs">Blogs</a></li>
					<li><a href="../index.php#contactos">Contacto</a></li>
					<li><a href="./tienda.php">Productos</a></li>
				</ul>
			</nav>
		</div>
	</header>