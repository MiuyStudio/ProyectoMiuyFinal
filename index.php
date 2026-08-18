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
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="contenedor">
        <!-- Barra Lateral de Navegación -->
        <nav class="barra-lateral">

            <!-- Logo y nombre de la institución -->
            <div class="cabecera">
                <img src="assets/utu.png" alt="Logo UTU">
                <p>UTU</p>
            </div>

            <!-- Lista de botones de navegación -->
            <ul class="botones">
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] != 3): ?>
                    <li><a href="modulos/inventario/inventario.php" target="visor-paginas" class="boton-nav">Inventario</a>
                    </li>
                <?php endif; ?>
                <li><a href="modulos/mesa_ayuda/mesa_ayuda.php" target="visor-paginas" class="boton-nav">Mesa de
                        Ayuda</a></li>
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] != 3): ?>
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

        // Al hacer clic en un botón del menú, desvanecer suavemente el visor
        botones.forEach(boton => {
            boton.addEventListener('click', () => {
                visor.classList.add('cargando');
            });
        });

        // Cuando la nueva sección termine de cargar, volver a mostrarla suavemente
        visor.addEventListener('load', () => {
            visor.classList.remove('cargando');
        });
    </script>
</body>

</html>