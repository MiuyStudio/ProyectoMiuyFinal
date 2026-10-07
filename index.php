<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTU - Portal de Gestión</title>
    <link rel="icon" type="image/png" href="assets/utu.png">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        /* Pantalla de carga crítica integrada (cubre 100% de la pantalla sin destellos) */
        #pantalla-carga-login {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 2147483647 !important;
            background-color: #0b0d12 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 24px !important;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            user-select: none;
        }

        #pantalla-carga-login.oculto {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Spinner circular minimalista */
        .loader-circle {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0%, rgba(255, 255, 255, 0.05) 20%, rgba(255, 255, 255, 0.95) 100%);
            mask: radial-gradient(farthest-side, transparent calc(100% - 7px), #000 calc(100% - 6px));
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 7px), #000 calc(100% - 6px));
            animation: spinLoader 0.9s linear infinite;
        }

        /* Texto CARGANDO... */
        .loader-text {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 5px;
            color: #94a3b8;
            text-transform: uppercase;
            animation: pulseLoaderText 1.6s ease-in-out infinite;
        }

        /* Barra de progreso inferior */
        .loader-bar-container {
            width: 180px;
            height: 5px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .loader-bar-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 35%;
            background: #ffffff;
            border-radius: 999px;
            animation: slideLoaderBar 1.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes spinLoader {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideLoaderBar {
            0% { left: -35%; width: 30%; }
            50% { left: 35%; width: 50%; }
            100% { left: 100%; width: 30%; }
        }

        @keyframes pulseLoaderText {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 1; }
        }
    </style>
</head>

<body>
    <!-- Pantalla de carga post-login -->
    <div id="pantalla-carga-login">
        <div class="loader-circle"></div>
        <div class="loader-text">Cargando...</div>
        <div class="loader-bar-container">
            <div class="loader-bar-fill"></div>
        </div>
    </div>

    <div class="contenedor">
        <!-- Barra Lateral de Navegación -->
        <nav class="barra-lateral">

            <!-- Logo y nombre de la institución -->
            <div class="cabecera">
                <img src="assets/utu.png" alt="Logo UTU">
                <p>UTU</p>
            </div>

            <!-- Lista de botones de navegación -->
            <?php
            $es_rol_3 = (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 3);
            ?>
            <ul class="botones">
                <?php if (!$es_rol_3): ?>
                    <li><a href="modulos/inventario/inventario.php" target="visor-paginas" class="boton-nav activo">Inventario</a>
                    </li>
                <?php endif; ?>
                <li><a href="modulos/mesa_ayuda/mesa_ayuda.php" target="visor-paginas" class="boton-nav <?php echo $es_rol_3 ? 'activo' : ''; ?>">Mesa de
                        Ayuda</a></li>
                <?php if (!$es_rol_3): ?>
                    <li><a href="modulos/dashboard/dashboard.php" target="visor-paginas" class="boton-nav">Dashboard</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1): ?>
                    <li><a href="modulos/admin/administrar_usuarios.php" target="visor-paginas" class="boton-nav">Panel
                            Admin</a></li>
                <?php endif; ?>
            </ul>

        </nav>

        <!-- Visor donde se cargan las páginas del menú -->
        <iframe
            src="<?php echo (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 3) ? 'modulos/mesa_ayuda/mesa_ayuda.php' : 'modulos/inventario/inventario.php'; ?>"
            name="visor-paginas" id="visor"></iframe>
    </div>

    <script>
        const visor = document.getElementById('visor');
        const botones = document.querySelectorAll('.boton-nav');
        const loaderLogin = document.getElementById('pantalla-carga-login');

        // Al hacer clic en un botón del menú, marcar como activo y desvanecer visor
        botones.forEach(boton => {
            boton.addEventListener('click', () => {
                visor.classList.add('cargando');
                botones.forEach(b => b.classList.remove('activo'));
                boton.classList.add('activo');
            });
        });

        // Cuando la nueva sección termine de cargar, volver a mostrarla suavemente
        visor.addEventListener('load', () => {
            visor.classList.remove('cargando');
        });

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
    </script>
</body>

</html>