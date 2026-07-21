<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTU - Portal de Gestión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="contenedor">
        <!-- Barra Lateral de Navegación -->
        <nav class="barra-lateral">
            
            <!-- Logo y nombre de la institución -->
            <div class="cabecera">
                <img src="images/utu.png" alt="Logo UTU">
                <p>UTU</p>
            </div>
            
            <!-- Lista de botones de navegación -->
            <ul class="botones">
                <li><a href="paginasNavegacion/inventario/inventario.html" target="visor-paginas" class="boton-nav">Inventario</a></li>
                <li><a href="paginasNavegacion/mesa_ayuda/mesa_ayuda.php" target="visor-paginas" class="boton-nav">Mesa de Ayuda</a></li>
                <li><a href="paginasNavegacion/dashboard/dashboard.html" target="visor-paginas" class="boton-nav">Dashboard</a></li>
            </ul>
            
            <!-- Botones de idioma -->
            <div class="idiomas">
                <button class="es">EN</button>
                <button class="en">ES</button>
            </div>
            
        </nav>
        
        <!-- Visor donde se cargan las páginas del menú -->
        <iframe src="paginasNavegacion/inventario/inventario.html" name="visor-paginas" id="visor"></iframe>
    </div>
</body>
</html>