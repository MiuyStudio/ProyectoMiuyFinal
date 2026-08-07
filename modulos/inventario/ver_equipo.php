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

    // 1. Obtener ID del equipo desde la URL
    $id_equipo = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id_equipo <= 0) {
        header("Location: inventario.php");
        exit();
    }

    // 2. Consultar datos del equipo específico
    $sql_equipo = "SELECT e.*, c.nombre_categoria, m.nombre_marca, mo.nombre_modelo
                   FROM equipos e
                   LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
                   LEFT JOIN marcas m ON e.id_marca = m.id_marca
                   LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
                   WHERE e.id_equipo = $id_equipo";
    $res_equipo = $conn->query($sql_equipo);

    if (!$res_equipo || $res_equipo->num_rows === 0) {
        header("Location: inventario.php");
        exit();
    }

    $equipo = $res_equipo->fetch_assoc();

    // 3. Consultar categorías disponibles (rango 8-12 como en el resto de la app)
    $sql_categorias = "SELECT id_categoria, nombre_categoria 
                       FROM categorias 
                       WHERE id_categoria BETWEEN 8 AND 12 
                       ORDER BY nombre_categoria ASC";
    $res_categorias = $conn->query($sql_categorias);

    // 4. Consultar marcas disponibles
    $sql_marcas = "SELECT id_marca, nombre_marca 
                   FROM marcas 
                   ORDER BY nombre_marca ASC";
    $res_marcas = $conn->query($sql_marcas);

    // 5. Consultar modelos disponibles
    $sql_modelos = "SELECT id_modelo, nombre_modelo, id_marca 
                    FROM modelos 
                    ORDER BY nombre_modelo ASC";
    $res_modelos = $conn->query($sql_modelos);    // 6. Consultar historial de asignaciones del equipo
    $sql_asig_equipo = "SELECT a.*, 
                               CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario,
                               r.nombre_rol
                        FROM asignaciones a
                        INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                        LEFT JOIN roles r ON u.id_rol = r.id_rol
                        WHERE a.id_equipo = $id_equipo
                        ORDER BY a.fecha_inicio DESC";
    $res_asig_equipo = $conn->query($sql_asig_equipo);

    // 7. Consultar diagnósticos asociados al equipo
    $sql_diag_equipo = "SELECT d.*, 
                               t.titulo AS titulo_ticket,
                               CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico
                        FROM diagnosticos d
                        LEFT JOIN tickets t ON d.id_ticket = t.id_ticket
                        LEFT JOIN usuarios u ON d.id_tecnico = u.id_usuario
                        WHERE d.id_equipo = $id_equipo
                           OR d.id_ticket IN (SELECT id_ticket FROM tickets WHERE id_equipo = $id_equipo)
                        ORDER BY d.fecha_intervencion DESC, d.id_diagnostico DESC";
    $res_diag_equipo = $conn->query($sql_diag_equipo);

    $error = isset($_GET['error']) ? $_GET['error'] : '';
    $mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Modificar / Dar de baja equipo</title>
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

            <?php if (!empty($mensaje)): ?>
                <p style="color: green; font-weight: bold; margin-bottom: 15px; text-align: center;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p style="color: red; font-weight: bold; margin-bottom: 15px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php endif; ?>

            <div class="panel">
                <h2>Modificar / Dar de baja equipo</h2>

                <form method="POST" action="../../acciones/inventario/actualizar_equipo.php">
                    <input type="hidden" name="id_equipo" value="<?php echo $equipo['id_equipo']; ?>">

                    <div class="fila-formulario">
                        <div class="grupo-campo">
                            <label for="serie">N° Serie</label>
                            <input type="text" id="serie" name="numero_serie_mostrar" value="<?php echo htmlspecialchars($equipo['numero_serie'] ?? ''); ?>" disabled>
                        </div>
                        <div class="grupo-campo">
                            <label for="categoria">Categoría</label>
                            <select id="categoria" name="id_categoria" required>
                                <option value="">Seleccione una categoría</option>
                                <?php while ($cat = $res_categorias->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $equipo['id_categoria']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="fila-formulario">
                        <div class="grupo-campo completo">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($equipo['nombre'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="fila-formulario">
                        <div class="grupo-campo">
                            <label for="marca">Marca</label>
                            <select id="marca" name="id_marca" required>
                                <option value="">Seleccione una marca</option>
                                <?php while ($marca = $res_marcas->fetch_assoc()): ?>
                                    <option value="<?php echo $marca['id_marca']; ?>" <?php echo ($marca['id_marca'] == $equipo['id_marca']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($marca['nombre_marca']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="grupo-campo">
                            <label for="modelo">Modelo</label>
                            <select id="modelo" name="id_modelo" required>
                                <option value="">Seleccione un modelo</option>
                                <?php while ($modelo = $res_modelos->fetch_assoc()): ?>
                                    <option value="<?php echo $modelo['id_modelo']; ?>" data-marca="<?php echo $modelo['id_marca']; ?>" <?php echo ($modelo['id_modelo'] == $equipo['id_modelo']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($modelo['nombre_modelo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="fila-formulario">
                        <div class="grupo-campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" required>
                                <option value="Disponible" <?php echo ($equipo['estado'] == 'Disponible') ? 'selected' : ''; ?>>Disponible</option>
                                <option value="En Uso" <?php echo ($equipo['estado'] == 'En Uso') ? 'selected' : ''; ?>>En Uso</option>
                                <option value="En Mantenimiento" <?php echo ($equipo['estado'] == 'En Mantenimiento') ? 'selected' : ''; ?>>En Mantenimiento</option>
                                <option value="De Baja" <?php echo ($equipo['estado'] == 'De Baja') ? 'selected' : ''; ?>>De Baja</option>
                            </select>
                        </div>
                    </div>

                    <div class="acciones">
                        <a href="inventario.php" class="boton-secundario">Volver al Inventario</a>
                        <button type="submit" name="accion" value="dar_baja" class="boton-peligro" onclick="return confirm('¿Está seguro de dar de baja este equipo?');">Dar de baja</button>
                        <button type="submit" name="accion" value="guardar" class="boton-primario">Guardar cambios</button>
                    </div>
                </form>
            </div>

            <!-- HISTORIAL DE ASIGNACIONES DEL EQUIPO -->
            <div class="panel">
                <h2>Historial de Asignaciones del Equipo</h2>
                <table class="tablaInventario">
                    <thead>
                        <tr>
                            <th>Asignado a</th>
                            <th>Rol</th>
                            <th>Desde (Fecha Inicio)</th>
                            <th>Hasta / Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_asig_equipo && $res_asig_equipo->num_rows > 0): ?>
                            <?php while ($asig = $res_asig_equipo->fetch_assoc()): ?>
                                <?php 
                                    $ffin = $asig['fecha_fin'] ?? '';
                                    $es_activa = (empty($ffin) || strpos($ffin, '0000-00-00') !== false);
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($asig['nombre_usuario']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($asig['nombre_rol'] ?? 'Usuario'); ?></td>
                                    <td><?php echo htmlspecialchars($asig['fecha_inicio']); ?></td>
                                    <td>
                                        <?php if ($es_activa): ?>
                                            <span class="estadoDisponible">Activa</span>
                                        <?php else: ?>
                                            <span><?php echo htmlspecialchars($asig['fecha_fin']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #777;">No hay historial de asignaciones registrado para este equipo.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- HISTORIAL DE DIAGNÓSTICOS DEL EQUIPO -->
            <div class="panel">
                <h2>Historial de Diagnósticos y Soluciones</h2>
                <table class="tablaInventario">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th>Ticket</th>
                            <th>Diagnóstico</th>
                            <th>Solución Aplicada</th>
                            <th>Fecha Intervención</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_diag_equipo && $res_diag_equipo->num_rows > 0): ?>
                            <?php while ($diag = $res_diag_equipo->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($diag['nombre_tecnico'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($diag['titulo_ticket'] ?? 'Sin Ticket'); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($diag['diagnostico'])); ?></td>
                                    <td><?php echo !empty($diag['solucion_aplicada']) ? nl2br(htmlspecialchars($diag['solucion_aplicada'])) : '<em style="color:#888;">Sin solución registrada</em>'; ?></td>
                                    <td><?php echo htmlspecialchars($diag['fecha_intervencion'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #777;">No hay diagnósticos registrados para este equipo.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="js/modelo_marca.js"></script>
    <?php $conn->close(); ?>
</body>

</html>