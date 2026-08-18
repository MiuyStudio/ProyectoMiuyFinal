<?php
    session_start();
    require_once '../../config/conexion.php';

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../../login/index.php");
        exit();
    }

    $id_ticket  = intval($_GET['id'] ?? 0);
    $rol        = intval($_SESSION['usuario_rol']);
    $id_usuario = intval($_SESSION['usuario_id']);
    $mensaje    = $_GET['mensaje'] ?? '';
    $error      = $_GET['error'] ?? '';

    if ($id_ticket <= 0) {
        header("Location: mesa_ayuda.php");
        exit();
    }

    // Traer el ticket con sus relaciones
    $sql = "SELECT t.*, 
                   c.nombre_categoria,
                   e.nombre AS nombre_equipo, e.numero_serie,
                   CONCAT(u.nombre, ' ', u.apellido) AS nombre_solicitante,
                   CONCAT(tec.nombre, ' ', tec.apellido) AS nombre_tecnico
            FROM tickets t
            LEFT JOIN categorias c ON t.id_categoria = c.id_categoria
            LEFT JOIN equipos e ON t.id_equipo = e.id_equipo
            INNER JOIN usuarios u ON t.id_solicitante = u.id_usuario
            LEFT JOIN usuarios tec ON t.id_tecnico = tec.id_usuario
            WHERE t.id_ticket = $id_ticket";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows === 0) {
        header("Location: mesa_ayuda.php");
        exit();
    }

    $ticket = $res->fetch_assoc();

    // Verificar que el usuario puede ver este ticket
    // Rol 3 solo puede ver sus propios tickets
    if ($rol == 3 && $ticket['id_solicitante'] != $id_usuario) {
        header("Location: mesa_ayuda.php");
        exit();
    }

    // Traer diagnósticos del ticket
    $sql_diag = "SELECT d.*, CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico, e.nombre AS nombre_equipo
                 FROM diagnosticos d
                 INNER JOIN usuarios u ON d.id_tecnico = u.id_usuario
                 LEFT JOIN equipos e ON d.id_equipo = e.id_equipo
                 WHERE d.id_ticket = $id_ticket
                 ORDER BY d.fecha_intervencion ASC";
    $res_diag = $conn->query($sql_diag);

    // Traer equipos para el formulario de diagnóstico
    $res_equipos = $conn->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre ASC");
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Ver Ticket</title>
    <link rel="icon" type="image/png" href="../../assets/utu.png">
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
                <li><a href="nuevo_ticket.php">Nuevo Ticket</a></li>
                <?php if ($rol == 1 || $rol == 2): ?>
                <li><a href="todos_tickets.php">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php">Equipos con atención</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="areaContenido">

            <?php if (!empty($mensaje)): ?>
                <p style="color: green; font-weight: bold; padding: 10px; background: #e6f4ea; border: 1px solid #b7e1cd; border-radius: 4px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p style="color: red; font-weight: bold; padding: 10px; background: #fce8e6; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php endif; ?>

            <!-- Detalle del ticket -->
            <div class="seccion">
                <h2>Ticket #<?php echo $ticket['id_ticket']; ?> — <?php echo htmlspecialchars($ticket['titulo']); ?></h2>

                <table class="tablaTickets" style="margin-bottom: 20px;">
                    <tbody>
                        <tr><th>Estado</th><td><span class="badge-estado estado<?php echo str_replace(' ', '', $ticket['estado']); ?>"><?php echo htmlspecialchars($ticket['estado']); ?></span></td></tr>
                        <tr><th>Tipo</th><td><?php echo htmlspecialchars($ticket['tipo_ticket']); ?></td></tr>
                        <tr><th>Prioridad</th><td><span class="badge-prioridad prioridad<?php echo htmlspecialchars($ticket['prioridad']); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></td></tr>
                        <tr><th>Categoría</th><td><?php echo htmlspecialchars($ticket['nombre_categoria'] ?? '—'); ?></td></tr>
                        <tr><th>Solicitante</th><td><?php echo htmlspecialchars($ticket['nombre_solicitante']); ?></td></tr>
                        <tr><th>Técnico asignado</th><td><?php echo htmlspecialchars($ticket['nombre_tecnico'] ?? 'Sin asignar'); ?></td></tr>
                        <tr><th>Equipo afectado</th><td><?php echo htmlspecialchars($ticket['nombre_equipo'] ?? '—'); ?></td></tr>
                        <tr><th>Fecha creación</th><td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></td></tr>
                        <tr><th>Última actualización</th><td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])); ?></td></tr>
                        <tr><th>Descripción</th><td><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></td></tr>
                    </tbody>
                </table>

                <a href="mesa_ayuda.php" style="display: inline-block; margin-bottom: 20px;">← Volver a mis tickets</a>
            </div>

            <!-- Diagnósticos registrados -->
            <?php if ($res_diag && $res_diag->num_rows > 0): ?>
            <div class="seccion">
                <h2>Diagnósticos registrados</h2>
                <?php while ($diag = $res_diag->fetch_assoc()): ?>
                <div style="border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 12px;">
                    <p><strong>Técnico:</strong> <?php echo htmlspecialchars($diag['nombre_tecnico']); ?></p>
                    <p><strong>Equipo:</strong> <?php echo htmlspecialchars($diag['nombre_equipo'] ?? '—'); ?></p>
                    <p><strong>Problema encontrado:</strong> <?php echo nl2br(htmlspecialchars($diag['diagnostico'])); ?></p>
                    <p><strong>Solución aplicada:</strong> <?php echo nl2br(htmlspecialchars($diag['solucion_aplicada'])); ?></p>
                    <p style="color: #888; font-size: 0.85em;"><?php echo date('d/m/Y H:i', strtotime($diag['fecha_intervencion'])); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

            <!-- Formulario de actualización — solo para técnicos y admins -->
            <?php if ($rol == 1 || $rol == 2): ?>
            <div class="seccion">
                <h2>Actualizar ticket</h2>
                <form method="POST" action="../../acciones/mesa_ayuda/actualizar_ticket.php">
                    <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                    <input type="hidden" name="accion" value="actualizar_estado">

                    <?php if ($ticket['id_tecnico'] === null): ?>
                        <!-- Botón Asignarme -->
                        <form method="POST" action="../../acciones/mesa_ayuda/actualizar_ticket.php" style="margin-bottom: 15px;">
                            <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                            <input type="hidden" name="accion" value="asignarme">
                            <button type="submit" class="boton-primario">Asignarme este ticket</button>
                        </form>
                    <?php endif; ?>

                    <div class="grupoFormulario">
                        <label for="estado">Cambiar estado</label>
                        <select id="estado" name="estado" required>
                            <option value="Pendiente" <?php echo ($ticket['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="En Proceso" <?php echo ($ticket['estado'] == 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="Resuelto" <?php echo ($ticket['estado'] == 'Resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="diagnostico">Diagnóstico (opcional)</label>
                        <textarea id="diagnostico" name="diagnostico" rows="3" placeholder="Describí el problema encontrado..."></textarea>
                    </div>

                    <div class="grupoFormulario">
                        <label for="solucion_aplicada">Solución aplicada (opcional)</label>
                        <textarea id="solucion_aplicada" name="solucion_aplicada" rows="3" placeholder="Describí qué se hizo para resolverlo..."></textarea>
                    </div>

                    <div class="grupoFormulario">
                        <label for="id_equipo_diag">Equipo intervenido (opcional)</label>
                        <select id="id_equipo_diag" name="id_equipo_diag">
                            <option value="">— Ninguno —</option>
                            <?php if ($res_equipos): while ($eq = $res_equipos->fetch_assoc()): ?>
                                <option value="<?php echo $eq['id_equipo']; ?>" <?php echo ($ticket['id_equipo'] == $eq['id_equipo']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($eq['nombre']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <div class="botonesFormulario">
                        <button type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>