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
                    <a href="./index.php">
                        <img src="./assets/icon/logo.png" alt="logo principal">
                    </a>
                </h1>
            </div>

            <div class="container-user">
                <?php if (!isset($_SESSION['usuario'])): ?>
                    <!-- Mostrar ícono de usuario si no está en sesión -->
                    <a href="./login.php">Sesión</a>
                <?php else: ?>
                    <!-- Mostrar ícono de la web si está en sesión -->
                    <div class="dropdown">
                        <a href="#" class="fa-solid fa-user sesion" id="userMenuToggle"></a>
                        <div class="dropdown-menu" id="userMenu">
                            <div class="user-info">
                                <i class="fa-solid fa-user-circle"></i>
                                <p class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
                            </div>
                            <div class="menu-items">
                                <a href="./TiendaDeUsuarios/mis-pedidos.php" class="menu-item">
                                    <i class="fa-solid fa-box"></i>
                                    Estado de Pedido
                                </a>
                                <a href="./logout.php" class="menu-item">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Cerrar sesión
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Ícono de carrito siempre visible -->
                <a href="./TiendaDeUsuarios/carrito.php"><i class="fa-solid fa-basket-shopping"></i></a>
            </div>

        </div>
    </div>

    <div class="container-navbar">
        <nav class="navbar container p-3">
            <i class="fa-solid fa-bars"></i>
            <ul class="menu">
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="./index.php#nosotros">Nosotros</a></li>
                <li><a href="./index.php#blogs">Blogs</a></li>
                <li><a href="./index.php#contactos">Contacto</a></li>
                <li><a href="./TiendaDeUsuarios/tienda.php">productos</a></li>
            </ul>
        </nav>
    </div>
</header>

<style>
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        min-width: 200px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 1rem;
        z-index: 1000;
    }

    .dropdown-menu.active {
        display: block;
    }

    .user-info {
        display: flex;
        align-items: center;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
        margin-bottom: 1rem;
    }

    .user-info i {
        font-size: 2rem;
        color: #666;
        margin-right: 0.5rem;
    }

    .user-name {
        margin: 0;
        font-weight: 500;
        color: #333;
    }

    .menu-items {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .menu-item:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }

    .menu-item i {
        margin-right: 0.5rem;
        width: 20px;
        text-align: center;
    }

    
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userMenu = document.getElementById('userMenu');

    userMenuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        userMenu.classList.toggle('active');
    });

    // Cerrar el menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!userMenu.contains(e.target) && !userMenuToggle.contains(e.target)) {
            userMenu.classList.remove('active');
        }
    });
});
</script>