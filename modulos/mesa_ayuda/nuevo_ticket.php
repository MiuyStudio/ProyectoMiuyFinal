<?php
    session_start();
    require_once '../../config/conexion.php';

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../../login/index.php");
        exit();
    }

    $rol    = intval($_SESSION['usuario_rol']);
    $error  = $_GET['error'] ?? '';

    // Categorías para tickets (ids 1-6)
    $sql_cat = "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_categoria BETWEEN 1 AND 6";
    $res_cat = $conn->query($sql_cat);

    // Equipos para el campo opcional
    $res_eq = $conn->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre ASC");
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Nuevo Ticket</title>
    <link rel="stylesheet" href="../css/mesa_ayuda.css">
</head>

<body>
    

    <!-- cabecera -->
    <div class="encabezado">
        <h1>Mesa de Ayuda</h1>
        <span>
            Usuario: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
            (<?php echo htmlspecialchars($_SESSION['nombre_rol']); ?>) |
            <a href="../../logout.php" target="_top">Cerrar sesión</a>
        </span>
    </div>

    <div class="contenedorPrincipal">

        <!-- menú lateral -->
        <div class="barraLateral">
            <ul>
                <li><a href="mesa_ayuda.php">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php" class="activo">Nuevo Ticket</a></li>
                <?php if ($rol == 1 || $rol == 2): ?>
                <li><a href="todos_tickets.php">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php">Equipos con atención</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="areaContenido">

            <div class="seccion">
                <h2>Crear Nuevo Ticket</h2>

                <?php if (!empty($error)): ?>
                    <p style="color: red; font-weight: bold; padding: 10px; background: #fce8e6; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>

                <form id="formularioNuevoTicket" action="../../acciones/mesa_ayuda/guardar_ticket.php" method="POST">

                    <div class="grupoFormulario">
                        <label for="titulo">Título del problema *</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Ej: No puedo abrir el programa" required>
                    </div>

                    <div class="grupoFormulario">
                        <label for="tipo_ticket">Tipo de ticket *</label>
                        <select id="tipo_ticket" name="tipo_ticket">
                            <option value="Incidencia">Incidencia</option>
                            <option value="Solicitud de Servicio">Solicitud de Servicio</option>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="id_categoria">Categoría *</label>
                        <select id="id_categoria" name="id_categoria" required>
                            <option value="">Seleccione una categoría</option>
                            <?php if ($res_cat): while ($cat = $res_cat->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="prioridad">Prioridad *</label>
                        <select id="prioridad" name="prioridad">
                            <option value="Alta">Alta</option>
                            <option value="Media" selected>Media</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="descripcion">Descripción detallada *</label>
                        <textarea id="descripcion" name="descripcion" rows="5"
                            placeholder="Describí el problema con el mayor detalle posible..." required></textarea>
                    </div>

                    <div class="grupoFormulario">
                        <label for="id_equipo">Equipo afectado (opcional)</label>
                        <select id="id_equipo" name="id_equipo">
                            <option value="">— Ninguno —</option>
                            <?php if ($res_eq): while ($eq = $res_eq->fetch_assoc()): ?>
                                <option value="<?php echo $eq['id_equipo']; ?>">
                                    <?php echo htmlspecialchars($eq['nombre']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <div class="botonesFormulario">
                        <button type="submit">Enviar Ticket</button>
                        <a href="mesa_ayuda.php" class="btn-cancelar">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>