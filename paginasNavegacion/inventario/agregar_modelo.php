<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Agregar Modelo</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>
    <?php
    require_once '../../conexion.php';

    // Consulta para traer todas las marcas ordenadas alfabéticamente
    $sql_marcas = "SELECT id_marca, nombre_marca 
                   FROM marcas 
                   ORDER BY nombre_marca ASC";
    $res_marcas = $conn->query($sql_marcas);

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
                <li><a href="agregar_marca.php">Agregar marca</a></li>
                <li><a href="agregar_modelo.php" class="activo">Agregar modelo</a></li>
                <li><a href="agregar_equipo.php">Agregar equipo</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <div class="seccion">
                <h2>Agregar nuevo modelo</h2>

                <?php if (!empty($error)): ?>
                    <p style="color: red; font-weight: bold; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form id="formularioModelo" action="guardar_modelo.php" method="POST">

                    <div class="grupoFormulario">
                        <label for="marcaModelo">Marca *</label>
                        <select id="marcaModelo" name="id_marca" required>
                            <option value="">Seleccione una marca</option>
                            <?php while ($marca = $res_marcas->fetch_assoc()): ?>
                                <option value="<?php echo $marca['id_marca']; ?>">
                                    <?php echo htmlspecialchars($marca['nombre_marca']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="nombreModelo">Nombre del modelo *</label>
                        <input type="text" id="nombreModelo" name="nombre_modelo" placeholder="Ej: ProBook 450 G8" required>
                    </div>

                    <div class="botonesFormulario">
                        <button type="submit">Guardar modelo</button>
                        <a href="inventario.php" class="btn-cancelar">Cancelar</a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>
