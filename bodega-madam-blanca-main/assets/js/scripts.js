// Módulo de Navegación
const Navigation = {
    init() {
        this.initSidebar();
        this.initMobileMenu();
        this.initUserMenu();
    },

    initSidebar() {
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
            });
        }
    },

    initMobileMenu() {
        const menuToggle = document.querySelector('.fa-bars');
        const menu = document.querySelector('.menu');

        if (menuToggle && menu) {
            menuToggle.addEventListener('click', () => {
                menu.classList.toggle('active');
            });
        }
    },

    initUserMenu() {
        const userMenuToggle = document.getElementById('userMenuToggle');
        const userMenu = document.getElementById('userMenu');

        if (userMenuToggle && userMenu) {
            userMenuToggle.addEventListener('click', (event) => {
                event.preventDefault();
                userMenu.classList.toggle('active');
                userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', (event) => {
                if (!userMenuToggle.contains(event.target) && !userMenu.contains(event.target)) {
                    userMenu.style.display = 'none';
                }
            });
        }
    }
};

// Módulo de Formularios
const Forms = {
    init() {
        this.initAddressForm();
        this.initPedidoForm();
        this.initValidation();
    },

    initAddressForm() {
        const submitDireccion = document.getElementById('submitDireccion');
        const direccionForm = document.getElementById('direccionForm');

        if (submitDireccion && direccionForm) {
            submitDireccion.addEventListener('click', () => {
                const formData = new FormData(direccionForm);
                
                fetch('guardar_direccion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido realizado con éxito');
                        window.location.href = '../index.php';
                    } else {
                        alert('Error al realizar el pedido: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    },

    initPedidoForm() {
        const pedidoForm = document.querySelector('form.needs-validation');
        const cantidadInput = document.getElementById('cantidad');
        const productoSelect = document.getElementById('id_producto');

        if (pedidoForm) {
            // Validar cantidad máxima según stock disponible
            if (cantidadInput && productoSelect) {
                productoSelect.addEventListener('change', function() {
                    const stockDisponible = parseInt(this.options[this.selectedIndex].text.match(/Stock disponible: (\d+)/)[1]);
                    cantidadInput.max = stockDisponible;
                    cantidadInput.value = Math.min(parseInt(cantidadInput.value) || 1, stockDisponible);
                });

                cantidadInput.addEventListener('input', function() {
                    const stockDisponible = parseInt(productoSelect.options[productoSelect.selectedIndex].text.match(/Stock disponible: (\d+)/)[1]);
                    if (this.value > stockDisponible) {
                        this.value = stockDisponible;
                    }
                });
            }
        }
    },

    initValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }
};

// Módulo de Pedidos
const Pedidos = {
    init() {
        this.initFiltroProductos();
        this.initPaginacion();
    },

    initFiltroProductos() {
        const buscarInputs = document.querySelectorAll('[id^="buscarProducto"]');
        buscarInputs.forEach(input => {
            input.addEventListener('keyup', () => {
                const idProyecto = input.id.replace('buscarProducto', '');
                this.filtrarProductos(idProyecto);
            });
        });
    },

    initPaginacion() {
        const paginacionButtons = document.querySelectorAll('.page-link');
        paginacionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const pagina = parseInt(button.textContent);
                const idProyecto = button.closest('.modal').id.replace('modalProductos', '');
                this.cambiarPagina(pagina, idProyecto);
            });
        });
    },

    filtrarProductos(idProyecto) {
        const modal = document.getElementById('modalProductos' + idProyecto);
        const busqueda = document.getElementById('buscarProducto' + idProyecto).value.toLowerCase();
        const filas = modal.getElementsByClassName('fila-producto');
        const paginacion = modal.querySelector('.pagination');
        let hayResultados = false;

        // Si el campo de búsqueda está vacío, restaurar la paginación normal
        if (busqueda === '') {
            paginacion.style.display = '';
            this.cambiarPagina(1, idProyecto);
            return;
        }

        // Ocultar la paginación durante la búsqueda
        paginacion.style.display = 'none';

        // Filtrar las filas
        Array.from(filas).forEach(fila => {
            const nombre = fila.getElementsByTagName('td')[0].textContent.toLowerCase();
            const descripcion = fila.getElementsByTagName('td')[1].textContent.toLowerCase();

            if (nombre.includes(busqueda) || descripcion.includes(busqueda)) {
                fila.style.display = '';
                hayResultados = true;
            } else {
                fila.style.display = 'none';
            }
        });

        // Mostrar mensaje si no hay resultados
        this.mostrarMensajeNoResultados(modal, hayResultados);
    },

    cambiarPagina(pagina, idProyecto) {
        const modal = document.getElementById('modalProductos' + idProyecto);
        const filas = modal.getElementsByClassName('fila-producto');
        const productosPorPagina = 6;

        // Ocultar todas las filas
        Array.from(filas).forEach(fila => fila.style.display = 'none');

        // Mostrar solo las filas de la página seleccionada
        const inicio = (pagina - 1) * productosPorPagina;
        const fin = inicio + productosPorPagina;

        for (let i = inicio; i < fin && i < filas.length; i++) {
            filas[i].style.display = '';
        }

        // Actualizar estado de los botones de paginación
        this.actualizarPaginacion(modal, pagina);
    },

    actualizarPaginacion(modal, paginaActual) {
        const botones = modal.getElementsByClassName('page-link');
        Array.from(botones).forEach(boton => {
            boton.parentElement.classList.remove('active');
            if (boton.textContent == paginaActual) {
                boton.parentElement.classList.add('active');
            }
        });
    },

    mostrarMensajeNoResultados(modal, hayResultados) {
        const mensajeNoResultados = modal.querySelector('.no-resultados');
        if (!hayResultados) {
            if (!mensajeNoResultados) {
                const mensaje = document.createElement('div');
                mensaje.className = 'alert alert-info no-resultados mt-3';
                mensaje.innerHTML = '<i class="fas fa-info-circle me-2"></i>No se encontraron productos que coincidan con la búsqueda.';
                modal.querySelector('.table-responsive').after(mensaje);
            }
        } else if (mensajeNoResultados) {
            mensajeNoResultados.remove();
        }
    }
};

// Inicialización de la aplicación
document.addEventListener('DOMContentLoaded', () => {
    Navigation.init();
    Forms.init();
    Pedidos.init();
});


