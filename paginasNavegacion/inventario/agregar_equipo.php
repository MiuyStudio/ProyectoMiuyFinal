<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Agregar Equipo</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>
    <?php
    require_once '../../conexion.php';

    // 1. Consulta para traer las categorías en el rango (8-12)
    $sql_categorias = "SELECT id_categoria, nombre_categoria 
                       FROM categorias 
                       WHERE id_categoria BETWEEN 8 AND 12 
                       ORDER BY nombre_categoria ASC";
    $res_categorias = $conn->query($sql_categorias);

    // 2. Consulta para traer todas las marcas ordenadas alfabéticamente
    $sql_marcas = "SELECT id_marca, nombre_marca 
                   FROM marcas 
                   ORDER BY nombre_marca ASC";
    $res_marcas = $conn->query($sql_marcas);

    // 3. Consulta para traer todos los modelos ordenados alfabéticamente
    $sql_modelos = "SELECT id_modelo, nombre_modelo, id_marca 
                    FROM modelos 
                    ORDER BY nombre_modelo ASC";
    $res_modelos = $conn->query($sql_modelos);

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
                <li><a href="agregar_modelo.php">Agregar modelo</a></li>
                <li><a href="agregar_equipo.php" class="activo">Agregar equipo</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <div class="seccion">
                <h2>Agregar nuevo equipo</h2>

                <?php if (!empty($error)): ?>
                    <p style="color: red; font-weight: bold; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <!-- Formulario configurado con POST y action -->
                <form id="formularioEquipo" action="guardar_equipo.php" method="POST">

                    <div class="grupoFormulario">
                        <label for="nombreEquipo">Nombre del equipo *</label>
                        <input type="text" id="nombreEquipo" name="nombre"
                            placeholder="Ej: Computadora del laboratorio 5" required>
                    </div>

                    <div class="grupoFormulario">
                        <label for="numeroSerie">Número de serie</label>
                        <input type="text" id="numeroSerie" name="numero_serie" placeholder="Ej: SN-00025">
                    </div>

                    <div class="grupoFormulario">
                        <label for="marcaEquipo">Marca *</label>
                        <select id="marcaEquipo" name="id_marca" required>
                            <option value="">Seleccione una marca</option>
                            <?php while ($marca = $res_marcas->fetch_assoc()): ?>
                                <option value="<?php echo $marca['id_marca']; ?>">
                                    <?php echo htmlspecialchars($marca['nombre_marca']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="modeloEquipo">Modelo *</label>
                        <select id="modeloEquipo" name="id_modelo" required>
                            <option value="">Seleccione un modelo</option>
                            <?php while ($modelo = $res_modelos->fetch_assoc()): ?>
                                <option value="<?php echo $modelo['id_modelo']; ?>" data-marca="<?php echo $modelo['id_marca']; ?>">
                                    <?php echo htmlspecialchars($modelo['nombre_modelo']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="categoriaEquipo">Categoría *</label>
                        <select id="categoriaEquipo" name="id_categoria" required>
                            <option value="">Seleccione una categoría</option>
                            <?php while ($cat = $res_categorias->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="estadoEquipo">Estado *</label>
                        <select id="estadoEquipo" name="estado" required>
                            <option value="">Seleccione un estado</option>
                            <option value="Disponible">Disponible</option>
                            <option value="En Uso">En Uso</option>
                            <option value="En Mantenimiento">En Mantenimiento</option>
                            <option value="De Baja">De Baja</option>
                        </select>
                    </div>

                    <div class="botonesFormulario">
                        <button type="submit">Guardar equipo</button>
                        <a href="inventario.php" class="btn-cancelar">Cancelar</a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>