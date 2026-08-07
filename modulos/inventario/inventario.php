<?php
    session_start();
    require_once '../../config/conexion.php';

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../../login/index.php");
        exit();
    }

    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] == 3) {
        header("Location: ../../index.php");
        exit();
    }


    // 1. Obtener categorías para el desplegable de filtros y modal
    $sql_categorias = "SELECT id_categoria, nombre_categoria 
                       FROM categorias 
                       WHERE id_categoria BETWEEN 8 AND 12 
                       ORDER BY nombre_categoria ASC";
    $res_categorias = $conn->query($sql_categorias);
    $categorias = [];
    if ($res_categorias) {
        while ($c = $res_categorias->fetch_assoc()) {
            $categorias[] = $c;
        }
    }

    // 2. Obtener marcas para el modal
    $sql_marcas = "SELECT id_marca, nombre_marca 
                   FROM marcas 
                   ORDER BY nombre_marca ASC";
    $res_marcas = $conn->query($sql_marcas);
    $marcas = [];
    if ($res_marcas) {
        while ($m = $res_marcas->fetch_assoc()) {
            $marcas[] = $m;
        }
    }

    // 3. Obtener modelos para el modal
    $sql_modelos = "SELECT id_modelo, nombre_modelo, id_marca 
                    FROM modelos 
                    ORDER BY nombre_modelo ASC";
    $res_modelos = $conn->query($sql_modelos);
    $modelos = [];
    if ($res_modelos) {
        while ($mo = $res_modelos->fetch_assoc()) {
            $modelos[] = $mo;
        }
    }

    // Capturar filtros y mensajes
    $cat_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $est_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
    $mensaje    = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';

    // 4. Construir la consulta de equipos con filtros dinámicos
    $sql_equipos = "SELECT e.id_equipo, e.numero_serie, e.nombre, e.id_marca, e.id_modelo, e.id_categoria, e.estado,
                           m.nombre_marca, mo.nombre_modelo, c.nombre_categoria
                    FROM equipos e
                    INNER JOIN categorias c ON e.id_categoria = c.id_categoria
                    LEFT JOIN marcas m ON e.id_marca = m.id_marca
                    LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
                    WHERE 1=1";
    
    if (!empty($cat_filtro)) {
        $sql_equipos .= " AND e.id_categoria = " . intval($cat_filtro);
    }

    if (!empty($est_filtro)) {
        $est_clean = $conn->real_escape_string($est_filtro);
        $sql_equipos .= " AND e.estado = '$est_clean'";
    }

    $sql_equipos .= " ORDER BY e.id_equipo ASC";
    $res_equipos = $conn->query($sql_equipos);
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Lista de Equipos</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>
    

    <!-- cabecera de la página -->
    <div class="encabezado">
        <h1>Inventario</h1>
        <span>
            Usuario: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> 
            (<?php echo htmlspecialchars($_SESSION['nombre_rol']); ?>) | 
            <a href="../../logout.php" target="_top">Cerrar sesión</a>
        </span>
    </div>

    <!-- layout principal -->
    <div class="contenedorPrincipal">

        <!-- menú de la izquierda -->
        <div class="barraLateral">
            <ul>
                <li><a href="inventario.php" class="activo">Equipos</a></li>
                <li><a href="categorias.php">Categorías</a></li>
                <li><a href="agregar_marca.php">Agregar marca</a></li>
                <li><a href="agregar_modelo.php">Agregar modelo</a></li>
                <li><a href="agregar_equipo.php">Agregar equipo</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <!-- filtros -->
            <div class="seccion">
                <h2>Lista de equipos</h2>

                <?php if (!empty($mensaje)): ?>
                    <p style="color: green; font-weight: bold; margin-bottom: 15px; text-align: center;"><?php echo htmlspecialchars($mensaje); ?></p>
                <?php endif; ?>

                <form method="GET" action="inventario.php" class="filtros">
                    <label>Categoría:
                        <select name="categoria" id="filtroCategoria">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat_filtro == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>Estado:
                        <select name="estado" id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="Disponible" <?php echo ($est_filtro == 'Disponible') ? 'selected' : ''; ?>>
                                Disponible
                            </option>
                            <option value="En Uso" <?php echo ($est_filtro == 'En Uso') ? 'selected' : ''; ?>>
                                En Uso
                            </option>
                            <option value="En Mantenimiento" <?php echo ($est_filtro == 'En Mantenimiento') ? 'selected' : ''; ?>>
                                En Mantenimiento
                            </option>
                            <option value="De Baja" <?php echo ($est_filtro == 'De Baja') ? 'selected' : ''; ?>>
                                De Baja
                            </option>
                        </select>
                    </label>

                    <button type="submit">Filtrar</button>
                </form>

                <table class="tablaInventario">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>N° Serie</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_equipos && $res_equipos->num_rows > 0): ?>
                            <?php while ($equipo = $res_equipos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $equipo['id_equipo']; ?></td>
                                    <td><?php echo htmlspecialchars($equipo['numero_serie'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_marca'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_modelo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_categoria'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge-equipo estado<?php echo str_replace(' ', '', $equipo['estado'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($equipo['estado'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-ver"
                                            data-id="<?php echo $equipo['id_equipo']; ?>"
                                            data-serie="<?php echo htmlspecialchars($equipo['numero_serie'] ?? '', ENT_QUOTES); ?>"
                                            data-nombre="<?php echo htmlspecialchars($equipo['nombre'] ?? '', ENT_QUOTES); ?>"
                                            data-categoria="<?php echo $equipo['id_categoria']; ?>"
                                            data-marca="<?php echo $equipo['id_marca']; ?>"
                                            data-modelo="<?php echo $equipo['id_modelo']; ?>"
                                            data-estado="<?php echo htmlspecialchars($equipo['estado'] ?? '', ENT_QUOTES); ?>"
                                            onclick="abrirModalDesdeBoton(this)">
                                            Editar
                                        </button>
                                        <a href="ver_equipo.php?id=<?php echo $equipo['id_equipo']; ?>" class="btn-ver" style="text-decoration:none; display:inline-block; margin-left:4px;">Detalle</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No hay equipos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- MODAL MODIFICAR / DAR DE BAJA EQUIPO -->
    <div id="modalEquipo" class="modal-overlay">
        <div class="modal-contenido panel">
            <div class="modal-header">
                <h2>Modificar / Dar de baja equipo</h2>
                <span class="modal-cerrar" onclick="cerrarModalEquipo()">&times;</span>
            </div>

            <form method="POST" action="../../acciones/inventario/actualizar_equipo.php" id="formModalEquipo">
                <input type="hidden" name="id_equipo" id="modal_id_equipo" value="">

                <div class="fila-formulario">
                    <div class="grupo-campo">
                        <label for="modal_serie">N° Serie</label>
                        <input type="text" id="modal_serie" name="numero_serie_mostrar" disabled>
                    </div>
                    <div class="grupo-campo">
                        <label for="modal_categoria">Categoría</label>
                        <select id="modal_categoria" name="id_categoria" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="fila-formulario">
                    <div class="grupo-campo completo">
                        <label for="modal_nombre">Nombre</label>
                        <input type="text" id="modal_nombre" name="nombre" required>
                    </div>
                </div>

                <div class="fila-formulario">
                    <div class="grupo-campo">
                        <label for="modal_marca">Marca</label>
                        <select id="modal_marca" name="id_marca" required>
                            <option value="">Seleccione una marca</option>
                            <?php foreach ($marcas as $mar): ?>
                                <option value="<?php echo $mar['id_marca']; ?>">
                                    <?php echo htmlspecialchars($mar['nombre_marca']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grupo-campo">
                        <label for="modal_modelo">Modelo</label>
                        <select id="modal_modelo" name="id_modelo" required>
                            <option value="">Seleccione un modelo</option>
                            <?php foreach ($modelos as $mod): ?>
                                <option value="<?php echo $mod['id_modelo']; ?>" data-marca="<?php echo $mod['id_marca']; ?>">
                                    <?php echo htmlspecialchars($mod['nombre_modelo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="fila-formulario">
                    <div class="grupo-campo">
                        <label for="modal_estado">Estado</label>
                        <select id="modal_estado" name="estado" required>
                            <option value="Disponible">Disponible</option>
                            <option value="En Uso">En Uso</option>
                            <option value="En Mantenimiento">En Mantenimiento</option>
                            <option value="De Baja">De Baja</option>
                        </select>
                    </div>
                </div>

                <div class="acciones">
                    <button type="button" class="boton-secundario" onclick="cerrarModalEquipo()">Cancelar</button>
                    <button type="submit" name="accion" value="dar_baja" class="boton-peligro" onclick="return confirm('¿Está seguro de dar de baja este equipo?');">Dar de baja</button>
                    <button type="submit" name="accion" value="guardar" class="boton-primario">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/modelo_marca.js"></script>
    <?php $conn->close(); ?>
</body>

</html>