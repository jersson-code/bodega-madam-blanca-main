<?php
session_start();

// Archivo: index.php
?>
<!DOCTYPE php>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Esencia Chocoana</title>
	<!-- ===== CSS ===== -->
	<link href="./assets/css/style.css" rel="stylesheet">
	<link href="./assets/css/principal.css" rel="stylesheet">
	<link href="./assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="./assets/css/contacto.css" rel="stylesheet">
	<!-- ===== favicon ===== -->
	<link href="./assets/icon/image-removebg-preview.png" rel="stylesheet">
	<!-- ===== Explorador de iconos CSS ===== -->
	<link href="css/line.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="./assets/icon/logo.png">
	<!-- ===== Mapbox CSS ===== -->
	<link href="https://api.mapbox.com/mapbox-gl-js/v2.16.0/mapbox-gl.css" rel="stylesheet">
	<link rel="stylesheet" href="./api.php">
</head>

<body>
	<?php include("./header.php"); ?>
	<section class="banner b">
		<div class="content-banner">
			<h2>Optimiza tu inventario <br /> impulsa tu productividad.</h2>
			<a href="#">Comprar ahora</a>
		</div>
	</section>
	<main class="main-content " id="inicion">
		<section class="container container-features cont">
			<div class="card-feature">
				<i class="fa-solid fa-plane-up"></i>
				<div class="feature-content">
					<span>Envío gratuito a nivel mundial</span>
					<p>En pedido superior a $150</p>
				</div>
			</div>
			<div class="card-feature">
				<i class="fa-solid fa-wallet"></i>
				<div class="feature-content">
					<span>Contrareembolso</span>
					<p>100% garantía de devolución de dinero</p>
				</div>
			</div>
			<div class="card-feature">
				<i class="fa-solid fa-gift"></i>
				<div class="feature-content">
					<span>Tarjeta regalo especial</span>
					<p>Ofrece bonos especiales con regalo</p>
				</div>
			</div>
			<div class="card-feature">
				<i class="fa-solid fa-headset"></i>
				<div class="feature-content">
					<span>Servicio al cliente 24/7</span>
					<p>LLámenos 24/7 al 123-456-7890</p>
				</div>
			</div>
		</section>
		<section class="container-xl py-5" id="nosotros">
			<div class="about-section">
				<h2 class="text-center my-5 section-title">Sobre Nosotros</h2>
				<div class="about-content">
					<div class="row g-5 align-items-center">
						<div class="col-md-6">
							<div class="about-image">
								<img src="./assets/img/banner.jpg" alt="imagen sobre nosotros" class="img-fluid rounded shadow">
								<div class="experience-badge">
									<span class="years">5+</span>
									<span class="text">Años de Experiencia</span>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="about-text">
								<h3 class="mb-4">Nuestra Historia</h3>
								<p class="lead mb-4">
									En Esencias Chocoanas, celebramos la riqueza cultural y natural del Chocó a través de artesanías
									únicas y auténticas.
								</p>
								<p class="mb-4">
									Actuamos como el puente entre los talentosos artesanos de la región y
									aquellos que buscan piezas especiales con un profundo valor cultural. Cada producto refleja la
									esencia de su origen, hecho con dedicación y materiales naturales de la selva y los ríos del
									Chocó.
								</p>
								<p class="mb-4">
									Nuestro compromiso es apoyar el talento local y promover la economía regional, ofreciendo
									una experiencia de compra en línea que te conecta directamente con los creadores y sus
									historias.
								</p>
								<div class="about-features mt-4">
									<div class="row">
										<div class="col-6 mb-3">
											<div class="feature-item">
												<i class="fa-solid fa-check-circle text-success"></i>
												<span>Artesanías Auténticas</span>
											</div>
										</div>
										<div class="col-6 mb-3">
											<div class="feature-item">
												<i class="fa-solid fa-check-circle text-success"></i>
												<span>Materiales Naturales</span>
											</div>
										</div>
										<div class="col-6 mb-3">
											<div class="feature-item">
												<i class="fa-solid fa-check-circle text-success"></i>
												<span>Apoyo Local</span>
											</div>
										</div>
										<div class="col-6 mb-3">
											<div class="feature-item">
												<i class="fa-solid fa-check-circle text-success"></i>
												<span>Calidad Garantizada</span>
											</div>
										</div>
									</div>
								</div>
								<a href="#contactos" class="btn btn-primary mt-4">Contáctanos</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section class="container blogs" id="blogs">
			<h1 class="heading-1">Últimos Blogs</h1>

			<div class="container-blogs">
				<div class="card-blog">
					<div class="container-img">
						<img src="./assets/img/blogs.jpg" alt="Imagen Blog 1" />
						<div class="button-group-blog">
							<span>
								<i class="fa-solid fa-magnifying-glass"></i>
							</span>
							<span>
								<i class="fa-solid fa-link"></i>
							</span>
						</div>
					</div>
					<div class="content-blog">
						<h3>Lorem, ipsum dolor sit</h3>
						<span>29 Noviembre 2023</span>
						<p>
							Lorem ipsum dolor sit amet consectetur adipisicing
							elit. Iste, molestiae! Ratione et, dolore ipsum
							quaerat iure illum reprehenderit non maxime amet dolor
							voluptas facilis corporis, consequatur eius est sunt
							suscipit?
						</p>
						<div class="btn-read-more">Leer más</div>
					</div>
				</div>
				<div class="card-blog">
					<div class="container-img">
						<img src="./assets/img/blogs(1).jpg" alt="Imagen Blog 2" />
						<div class="button-group-blog">
							<span>
								<i class="fa-solid fa-magnifying-glass"></i>
							</span>
							<span>
								<i class="fa-solid fa-link"></i>
							</span>
						</div>
					</div>
					<div class="content-blog">
						<h3>Lorem, ipsum dolor sit</h3>
						<span>19 Noviembre 2020</span>
						<p>
							Lorem ipsum dolor sit amet consectetur adipisicing
							elit. Iste, molestiae! Ratione et, dolore ipsum
							quaerat iure illum reprehenderit non maxime amet dolor
							voluptas facilis corporis, consequatur eius est sunt
							suscipit?
						</p>
						<div class="btn-read-more">Leer más</div>
					</div>
				</div>
				<div class="card-blog">
					<div class="container-img">
						<img src="./assets/img/blogs(2).jpg" alt="Imagen Blog 3" />
						<div class="button-group-blog">
							<span>
								<i class="fa-solid fa-magnifying-glass"></i>
							</span>
							<span>
								<i class="fa-solid fa-link"></i>
							</span>
						</div>
					</div>
					<div class="content-blog">
						<h3>Lorem, ipsum dolor sit</h3>
						<span>1 Abril 2024</span>
						<p>
							Lorem ipsum dolor sit amet consectetur adipisicing
							elit. Iste, molestiae! Ratione et, dolore ipsum
							quaerat iure illum reprehenderit non maxime amet dolor
							voluptas facilis corporis, consequatur eius est sunt
							suscipit?
						</p>
						<div class="btn-read-more">Leer más</div>
					</div>
				</div>
			</div>
		</section>

		<section class="container-xl py-5" id="contactos">

			<h2 class="text-center my-5"> Contacto </h2>

			<div class="row justify-content-center">

				<form action="" class="col-md-8">
					<fieldset>
						<legend class="btn-primary text-center text-dark fs-3 ">Tus Datos</legend>

						<div class="mb-3">
							<label for="nombre" class="form-label">Nombre:</label>
							<input type="text" class="form-control" id="nombre" placeholder="Tu Nombre">
						</div>
						<div class="mb-3">
							<label for="asunto" class="form-label">Asunto: </label>
							<input type="text" class="form-control" id="asunto" placeholder="Tu Asunto">
						</div>
						<div class="mb-3">
							<label for="email" class="form-label">Email:</label>
							<input type="email" class="form-control" id="email" placeholder="Tu Email">
						</div>
						<div class="mb-3">
							<label for="tel" class="form-label">Teléfono: </label>
							<input type="tel" class="form-control" id="tel" placeholder="Tu Teléfono">
						</div>
						<div class="mb-3">
							<label for="" class="form-label">Mensaje: </label>
							<textarea class="form-control" rows="10"></textarea>
						</div>
					</fieldset>
					<fieldset>

						<legend class="btn-primary text-center text-dark fs-2">País</legend>
						<div class="mb-3">
							<label for="país" class="form-label"> País:</label>
							<select class="form-control" id="pais">
								<option value="CO">Colombia</option>
								<option value="PR">Perú</option>
								<option value="MX">México</option>
								<option value="AR">Argentina</option>
								<option value="ES">España</option>
							</select>
						</div>

					</fieldset>
					<fieldset>
						<legend class="btn-primary text-center text-dark fs-2">Informacion Extra</legend>
						<div class="mb-3">
							<label for="Cliente" class="form-label">Cliente: </label>
							<input type="radio" class="form-check-input" id="cliente">
						</div>
						<div class="mb-3">
							<label for="Proveedor" class="form-label">Proveedor: </label>
							<input name="tipo" type="radio" class="form-check-input" id="proveedor">
						</div>

					</fieldset>
					<input type="submit" class="btn btn-primary fs-2 px-5" value="Enviar Formulario">
				</form>

			</div>
		</section>

		<!-- Nueva sección para mostrar las mejores constructoras -->
		<section class="container-xl py-5" id="constructoras">
			<h2 class="text-center my-5">Mejores Constructoras de Colombia</h2>
			<div id="constructoras-list" class="row g-4">
				<!-- Aquí se cargarán las constructoras dinámicamente -->
			</div>
		</section>


	</main>



	<?php include './footer.php'; ?>

	

	<script src="./assets/js/ico.js" crossorigin="anonymous"></script>
	<script type="module" src="./assets/js/login.js"></script>
	<script type="module" src="./assets/js/scripts.js"></script>
	
	<script src="./http://localhost/proyectos/bodega_madam_blanca/api_constructoras.php"></script>

	<script>
		// Obtener y mostrar las mejores constructoras
		async function fetchConstructoras() {
			try {
				const response = await fetch('http://localhost/proyectos/bodega_madam_blanca/api.php');
				const data = await response.json();

				if (data.success) {
					const constructorasList = document.getElementById('constructoras-list');
					constructorasList.innerHTML = '';

					data.data.forEach(constructora => {
						const card = `
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">${constructora.nombre}</h5>
                                        <p class="card-text">Ciudad: ${constructora.ciudad}</p>
                                        <p class="card-text">Ranking: ${constructora.ranking}</p>
                                    </div>
                                </div>
                            </div>
                        `;
						constructorasList.innerHTML += card;
					});
				} else {
					console.error('Error al obtener las constructoras:', data.message);
				}
			} catch (error) {
				console.error('Error en la solicitud:', error);
			}
		}

		// Llamar a la función al cargar la página
		document.addEventListener('DOMContentLoaded', fetchConstructoras);

		// Guardar y restaurar la posición de desplazamiento
		window.onbeforeunload = function() {
			localStorage.setItem('scrollPosition', window.scrollY);
		};

		window.onload = function() {
			const scrollPosition = localStorage.getItem('scrollPosition');
			if (scrollPosition) {
				window.scrollTo(0, scrollPosition);
				localStorage.removeItem('scrollPosition');
			}
		};
	</script>
</body>

</html>