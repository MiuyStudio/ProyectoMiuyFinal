/**
 * Control del visor iframe y animación de bienvenida post-login
 */
document.addEventListener('DOMContentLoaded', () => {
    const visor = document.getElementById('visor');
    const botones = document.querySelectorAll('.boton-nav');
    const loaderLogin = document.getElementById('pantalla-carga-login');

    // Al hacer clic en un botón del menú, marcar como activo y animar visor
    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            if (visor) visor.classList.add('cargando');
            botones.forEach(b => b.classList.remove('activo'));
            boton.classList.add('activo');
        });
    });

    // Cuando la nueva sección termine de cargar, volver a mostrarla suavemente
    if (visor) {
        visor.addEventListener('load', () => {
            visor.classList.remove('cargando');
        });
    }

    // Control de animación de carga post-login
    if (loaderLogin) {
        const tiempoInicio = Date.now();
        const TIEMPO_MINIMO_MS = 1400; // Duración para mostrar la animación antes de abrir

        const finalizarCarga = () => {
            const transcurrido = Date.now() - tiempoInicio;
            const restante = Math.max(0, TIEMPO_MINIMO_MS - transcurrido);
            setTimeout(() => {
                loaderLogin.classList.add('oculto');
                setTimeout(() => loaderLogin.remove(), 500);
            }, restante);
        };

        window.addEventListener('load', finalizarCarga);
        setTimeout(finalizarCarga, 2500); // Respaldo máximo
    }
});
