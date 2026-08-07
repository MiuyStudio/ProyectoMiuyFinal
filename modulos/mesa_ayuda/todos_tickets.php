<?php
    session_start();
    require_once '../../config/conexion.php';

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../../login/index.php");
        exit();
    }

    $rol = intval($_SESSION['usuario_rol']);

    // Solo técnicos y admins
    if ($rol == 3) {
        header("Location: mesa_ayuda.php");
        exit();
    }

    $mensaje = $_GET['mensaje'] ?? '';

    // Filtros
    $est_filtro  = $_GET['estado'] ?? '';
    $pri_filtro  = $_GET['prioridad'] ?? '';

    $sql = "SELECT t.id_ticket, t.titulo, t.prioridad, t.estado, t.fecha_creacion,
                   CONCAT(u.nombre, ' ', u.apellido) AS nombre_solicitante,
                   CONCAT(tec.nombre, ' ', tec.apellido) AS nombre_tecnico,
                   c.nombre_categoria
            FROM tickets t
            INNER JOIN usuarios u ON t.id_solicitante = u.id_usuario
            LEFT JOIN usuarios tec ON t.id_tecnico = tec.id_usuario
            LEFT JOIN categorias c ON t.id_categoria = c.id_categoria
            WHERE 1=1";

    if (!empty($est_filtro)) {
        $est_clean = $conn->real_escape_string($est_filtro);
        $sql .= " AND t.estado = '$est_clean'";
    }
    if (!empty($pri_filtro)) {
        $pri_clean = $conn->real_escape_string($pri_filtro);
        $sql .= " AND t.prioridad = '$pri_clean'";
    }

    $sql .= " ORDER BY t.fecha_creacion DESC";
    $res = $conn->query($sql);
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Todos los Tickets</title>
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

        <div class="barraLateral">
            <ul>
                <li><a href="mesa_ayuda.php">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php">Nuevo Ticket</a></li>
                <li><a href="todos_tickets.php" class="activo">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php">Equipos con atención</a></li>
            </ul>
        </div>

        <div class="areaContenido">

            <?php if (!empty($mensaje)): ?>
                <p style="color: green; font-weight: bold; padding: 10px; background: #e6f4ea; border: 1px solid #b7e1cd; border-radius: 4px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>

            <div class="seccion">
                <h2>Todos los Tickets</h2>

                <!-- Filtros -->
                <form method="GET" action="todos_tickets.php" class="filtros">
                    <label>Estado:
                        <select name="estado" id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="Pendiente" <?php echo ($est_filtro == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="En Proceso" <?php echo ($est_filtro == 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="Resuelto" <?php echo ($est_filtro == 'Resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                        </select>
                    </label>
                    <label>Prioridad:
                        <select name="prioridad" id="filtroPrioridad">
                            <option value="">Todas</option>
                            <option value="Alta" <?php echo ($pri_filtro == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                            <option value="Media" <?php echo ($pri_filtro == 'Media') ? 'selected' : ''; ?>>Media</option>
                            <option value="Baja" <?php echo ($pri_filtro == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                        </select>
                    </label>
                    <button type="submit">Filtrar</button>
                </form>

                <table class="tablaTickets">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Solicitante</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Técnico</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && $res->num_rows > 0): ?>
                            <?php while ($t = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $t['id_ticket']; ?></td>
                                <td><?php echo htmlspecialchars($t['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($t['nombre_solicitante']); ?></td>
                                <td><span class="badge-prioridad prioridad<?php echo htmlspecialchars($t['prioridad']); ?>"><?php echo htmlspecialchars($t['prioridad']); ?></span></td>
                                <td><span class="badge-estado estado<?php echo str_replace(' ', '', $t['estado']); ?>"><?php echo htmlspecialchars($t['estado']); ?></span></td>
                                <td><?php echo htmlspecialchars($t['nombre_tecnico'] ?? 'Sin asignar'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($t['fecha_creacion'])); ?></td>
                                <td>
                                    <a href="ver_ticket.php?id=<?php echo $t['id_ticket']; ?>"><button type="button">Ver</button></a>
                                    <?php if ($t['nombre_tecnico'] === null): ?>
                                        <form method="POST" action="../../acciones/mesa_ayuda/actualizar_ticket.php" style="display: inline;">
                                            <input type="hidden" name="id_ticket" value="<?php echo $t['id_ticket']; ?>">
                                            <input type="hidden" name="accion" value="asignarme">
                                            <button type="submit">Asignarme</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No hay tickets registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>