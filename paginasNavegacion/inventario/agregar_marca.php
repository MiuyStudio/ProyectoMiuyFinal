<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Agregar Marca</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>
    <?php
    require_once '../../conexion.php';

    $error = isset($_GET['error']) ? $_GET['error'] : '';
    ?>

    <!-- cabecera de la página -->
    <div class="encabezado">
        <h1>Inventario</h1>
        <span>Usuario: Marcel Matiaude | <a href="#">Cerrar sesión</a></span>
    </div>

    <!-- layout principal -->
    <div class="contenedorPrincipal">

        <!-- menú de la izquierda -->
        <div class="barraLateral">
            <ul>
                <li><a href="inventario.php">Equipos</a></li>
                <li><a href="categorias.php">Categorías</a></li>
                <li><a href="agregar_marca.php" class="activo">Agregar marca</a></li>
                <li><a href="agregar_modelo.php">Agregar modelo</a></li>
                <li><a href="agregar_equipo.php">Agregar equipo</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <div class="seccion">
                <h2>Agregar nueva marca</h2>

                <?php if (!empty($error)): ?>
                    <p style="color: red; font-weight: bold; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form id="formularioMarca" action="guardar_marca.php" method="POST">

                    <div class="grupoFormulario">
                        <label for="nombreMarca">Nombre de la marca *</label>
                        <input type="text" id="nombreMarca" name="nombre_marca" placeholder="Ej: HP" required>
                    </div>

                    <div class="botonesFormulario">
                        <button type="submit">Guardar marca</button>
                        <a href="inventario.php" class="btn-cancelar">Cancelar</a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>
