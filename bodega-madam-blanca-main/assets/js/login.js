const 
    container = document.querySelector(".container2"),
    signUp = document.querySelector(".registro"),
    login = document.querySelector(".inicio-de-sesión");

    // Código JS para que aparezca el formulario de registro e inicio de sesión
    signUp.addEventListener("click", ( )=>{
        container.classList.add("active2");
    });
    login.addEventListener("click", ( )=>{
        container.classList.remove("active2");
    });

document.addEventListener('DOMContentLoaded', () => {
    const registroForm = document.querySelector('.form.signup form');
    
    registroForm.addEventListener('submit', function(e) {
        // Limpiar errores previos
        const erroresAnteriores = document.querySelectorAll('.error-registro');
        erroresAnteriores.forEach(error => error.remove());

        // Validaciones
        const nombre = registroForm.querySelector('input[name="nombre"]');
        const email = registroForm.querySelector('input[name="email"]');
        const contrasena = registroForm.querySelector('input[name="contrasena"]');
        const telefono = registroForm.querySelector('input[name="telefono"]');
        const rol = registroForm.querySelector('select[name="rol"]');
        const idProyecto = registroForm.querySelector('input[name="id_proyecto"]');

        let hayErrores = false;

        // Validación de nombre
        if (nombre.value.trim() === '') {
            mostrarError(nombre, 'El nombre es obligatorio');
            hayErrores = true;
        }

        // Validación de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            mostrarError(email, 'Ingrese un email válido');
            hayErrores = true;
        }

        // Validación de contraseña
        if (contrasena.value.length < 6) {
            mostrarError(contrasena, 'La contraseña debe tener al menos 6 caracteres');
            hayErrores = true;
        }

        // Validación de teléfono
        const telefonoRegex = /^[0-9]{10}$/;
        if (!telefonoRegex.test(telefono.value)) {
            mostrarError(telefono, 'Ingrese un teléfono válido (10 dígitos)');
            hayErrores = true;
        }

        // Validación de rol
        if (rol.value === '') {
            mostrarError(rol, 'Seleccione un rol');
            hayErrores = true;
        }

        // Validación de ID de proyecto para Jefe de Obra
        if (rol.value === '3' && idProyecto.value.trim() === '') {
            mostrarError(idProyecto, 'El ID de proyecto es obligatorio para Jefe de Obra');
            hayErrores = true;
        }

        if (hayErrores) {
            e.preventDefault(); // Detener el envío del formulario
        }
    });

    function mostrarError(elemento, mensaje) {
        const errorDiv = document.createElement('div');
        errorDiv.classList.add('error-registro');
        errorDiv.textContent = mensaje;
        
        // Insertar el error justo antes del botón de registro
        const botonRegistro = elemento.closest('form').querySelector('input[type="submit"]');
        botonRegistro.parentNode.insertBefore(errorDiv, botonRegistro);
    }

    // Función para mostrar/ocultar campo de proyecto
    function toggleProyectoField() {
        const rol = document.getElementById('rol');
        const proyectoField = document.getElementById('id_proyecto');
        
        rol.addEventListener('change', function() {
            if (this.value === '3') {
                proyectoField.style.display = 'block';
                proyectoField.required = true;
            } else {
                proyectoField.style.display = 'none';
                proyectoField.required = false;
            }
        });
    }

    toggleProyectoField();

    // Función para mostrar modal de error
    function mostrarModalError(mensaje) {
        // Eliminar cualquier modal de error existente
        const modalExistente = document.querySelector('.error-modal');
        if (modalExistente) {
            modalExistente.remove();
        }

        // Crear el modal de error
        const modal = document.createElement('div');
        modal.classList.add('error-modal');
        
        // Contenido del modal
        modal.innerHTML = `
            <span class="error-icon">⚠️</span>
            <span class="error-message">${mensaje}</span>
            <button class="close-btn">&times;</button>
        `;

        // Añadir al cuerpo del documento
        document.body.appendChild(modal);

        // Añadir evento para cerrar el modal
        const closeBtn = modal.querySelector('.close-btn');
        closeBtn.addEventListener('click', () => {
            modal.remove();
        });

        // Eliminar automáticamente después de 5 segundos
        setTimeout(() => {
            if (modal.parentNode) {
                modal.remove();
            }
        }, 5000);
    }

    // Verificar si hay un parámetro de error en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        mostrarModalError('Usuario o contraseña incorrectos');
    }
    
    // Verificar errores de correo ya registrado
    if (urlParams.has('email_error')) {
        mostrarModalError('El correo ya está registrado');
    }
}); 