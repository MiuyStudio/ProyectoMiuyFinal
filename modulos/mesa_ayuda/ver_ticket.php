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
    $origen        = $_GET['origen'] ?? '';
    $id_equipo_ref = intval($_GET['id_equipo'] ?? 0);

    if ($origen === 'todos') {
        $volver_url   = 'todos_tickets.php';
        $volver_texto = '← Volver a todos los tickets';
    } elseif ($origen === 'equipos_atencion') {
        $volver_url   = 'equipos_atencion.php';
        $volver_texto = '← Volver a equipos con atención';
    } elseif ($origen === 'diagnosticos') {
        $volver_url   = 'diagnosticos.php';
        $volver_texto = '← Volver a diagnósticos';
    } elseif ($origen === 'soluciones') {
        $volver_url   = 'soluciones.php';
        $volver_texto = '← Volver a soluciones';
    } elseif ($origen === 'equipo' && $id_equipo_ref > 0) {
        $volver_url   = '../inventario/ver_equipo.php?id=' . $id_equipo_ref . '&origen=ticket&id_ticket=' . $id_ticket;
        $volver_texto = '← Volver a la ficha del equipo';
    } else {
        $volver_url   = 'mesa_ayuda.php';
        $volver_texto = '← Volver a mis tickets';
    }

    if ($id_ticket <= 0) {
        header("Location: " . $volver_url);
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

    // Traer diagnósticos del ticket
    $sql_diag = "SELECT d.*, CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico, e.nombre AS nombre_equipo
                 FROM diagnosticos d
                 INNER JOIN usuarios u ON d.id_tecnico = u.id_usuario
                 LEFT JOIN equipos e ON d.id_equipo = e.id_equipo
                 WHERE d.id_ticket = $id_ticket
                 ORDER BY d.fecha_intervencion ASC";
    $res_diag = $conn->query($sql_diag);

    // Traer comentarios del ticket
    $sql_com = "SELECT c.*, CONCAT(u.nombre, ' ', u.apellido) AS autor, r.nombre_rol
                FROM comentarios c
                INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE c.id_ticket = $id_ticket
                ORDER BY c.fecha_creacion ASC";
    $res_com = $conn->query($sql_com);

    // Traer equipos para el formulario de diagnóstico
    $res_equipos = $conn->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Ver Ticket #<?php echo $id_ticket; ?></title>
    <link rel="icon" type="image/png" href="../../assets/utu.png">
    <link rel="stylesheet" href="../css/mesa_ayuda.css?v=<?php echo time(); ?>">
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
                <li><a href="mesa_ayuda.php" class="<?php echo (empty($origen) || $origen === 'mis') ? 'activo' : ''; ?>">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php">Nuevo Ticket</a></li>
                <?php if ($rol == 1 || $rol == 2): ?>
                <li><a href="todos_tickets.php" class="<?php echo ($origen === 'todos') ? 'activo' : ''; ?>">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php" class="<?php echo ($origen === 'equipos_atencion') ? 'activo' : ''; ?>">Equipos con atención</a></li>
                <li><a href="diagnosticos.php" class="<?php echo ($origen === 'diagnosticos') ? 'activo' : ''; ?>">Diagnósticos</a></li>
                <li><a href="soluciones.php" class="<?php echo ($origen === 'soluciones') ? 'activo' : ''; ?>">Soluciones Aplicadas</a></li>
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

            <div style="margin-bottom: 12px;">
                <a href="<?php echo htmlspecialchars($volver_url); ?>" style="color: #0066cc; text-decoration: none; font-weight: 500; font-size: 14px;"><?php echo htmlspecialchars($volver_texto); ?></a>
            </div>

            <!-- CONTENEDOR EN 2 COLUMNAS (TICKET A LA IZQUIERDA Y COMENTARIOS A LA DERECHA) -->
            <div class="contenedor-ticket-comentarios">

                <!-- COLUMNA IZQUIERDA: DETALLE DEL TICKET -->
                <div class="ticket-col-izq">
                    <h2>Ticket #<?php echo $ticket['id_ticket']; ?> — <?php echo htmlspecialchars($ticket['titulo']); ?></h2>

                    <table class="tablaTickets" style="margin-bottom: 20px;">
                        <tbody>
                            <tr><th>Estado</th><td><span class="badge-estado estado<?php echo str_replace(' ', '', $ticket['estado']); ?>"><?php echo htmlspecialchars($ticket['estado']); ?></span></td></tr>
                            <tr><th>Tipo</th><td><?php echo htmlspecialchars($ticket['tipo_ticket']); ?></td></tr>
                            <tr><th>Prioridad</th><td><span class="badge-prioridad prioridad<?php echo htmlspecialchars($ticket['prioridad']); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></td></tr>
                            <tr><th>Categoría</th><td><?php echo htmlspecialchars($ticket['nombre_categoria'] ?? '—'); ?></td></tr>
                            <tr><th>Solicitante</th><td><?php echo htmlspecialchars($ticket['nombre_solicitante']); ?></td></tr>
                            <tr><th>Técnico asignado</th><td><?php echo htmlspecialchars($ticket['nombre_tecnico'] ?? 'Sin asignar'); ?></td></tr>
                            <tr>
                                <th>Equipo afectado</th>
                                <td>
                                    <?php if (!empty($ticket['id_equipo'])): ?>
                                        <a href="../inventario/ver_equipo.php?id=<?php echo $ticket['id_equipo']; ?>&origen=ticket&id_ticket=<?php echo $ticket['id_ticket']; ?>" style="color: #0066cc; text-decoration: underline; font-weight: 500;">
                                            <?php echo htmlspecialchars($ticket['nombre_equipo'] . ($ticket['numero_serie'] ? ' (' . $ticket['numero_serie'] . ')' : '')); ?> (Ver historial)
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><th>Fecha creación</th><td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></td></tr>
                            <tr><th>Última actualización</th><td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])); ?></td></tr>
                            <tr><th>Descripción</th><td><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></td></tr>
                        </tbody>
                    </table>

                    <!-- Diagnósticos registrados -->
                    <?php if ($res_diag && $res_diag->num_rows > 0): ?>
                    <div style="margin-top: 25px;">
                        <h3 style="font-size: 15px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Diagnósticos registrados</h3>
                        <?php while ($diag = $res_diag->fetch_assoc()): ?>
                        <div style="border: 1px solid #ddd; border-radius: 6px; padding: 12px; margin-bottom: 10px; background: #fafafa;">
                            <p><strong>Técnico:</strong> <?php echo htmlspecialchars($diag['nombre_tecnico']); ?></p>
                            <p><strong>Equipo:</strong> <?php echo htmlspecialchars($diag['nombre_equipo'] ?? '—'); ?></p>
                            <p><strong>Problema encontrado:</strong> <?php echo nl2br(htmlspecialchars($diag['diagnostico'])); ?></p>
                            <p><strong>Solución aplicada:</strong> <?php echo nl2br(htmlspecialchars($diag['solucion_aplicada'])); ?></p>
                            <p style="color: #888; font-size: 0.85em; margin-top: 4px;"><?php echo date('d/m/Y H:i', strtotime($diag['fecha_intervencion'])); ?></p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Sección de Estado y Acciones para técnicos y admins -->
                    <?php if ($rol == 1 || $rol == 2): ?>
                        <div class="panel-acciones-ticket">
                            <?php if ($ticket['estado'] === 'Resuelto'): ?>
                                <div class="tarjeta-resuelto">
                                    <div class="resuelto-icono">✓</div>
                                    <div class="resuelto-info">
                                        <h4>Ticket Resuelto</h4>
                                        <p>Este caso fue cerrado satisfactoriamente.</p>
                                        <p style="font-size: 12px; color: #1e4620; margin-top: 4px;">
                                            <strong>Técnico:</strong> <?php echo htmlspecialchars($ticket['nombre_tecnico'] ?? 'Técnico asignado'); ?> | 
                                            <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <button type="button" class="btn-accion-ticket" onclick="abrirModalResolver('Resuelto')">
                                        Modificar estado o solución
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="barra-acciones-rapidas">
                                    <?php if ($ticket['id_tecnico'] === null || empty($ticket['id_tecnico'])): ?>
                                        <form method="POST" action="../../acciones/mesa_ayuda/actualizar_ticket.php" style="margin: 0;">
                                            <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                                            <input type="hidden" name="origen" value="<?php echo htmlspecialchars($origen); ?>">
                                            <input type="hidden" name="accion" value="asignarme">
                                            <button type="submit" class="btn-accion-ticket btn-asignar">
                                                Asignarme este ticket
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <button type="button" class="btn-accion-ticket btn-primario-accion" onclick="abrirModalResolver('Resuelto')">
                                        Resolver Ticket
                                    </button>

                                    <button type="button" class="btn-accion-ticket" onclick="abrirModalResolver('<?php echo htmlspecialchars($ticket['estado']); ?>')">
                                        Actualizar estado
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- MODAL RESOLVER / ACTUALIZAR TICKET -->
                        <div id="modalResolverTicket" class="modal-overlay" onclick="cerrarModalFuera(event)">
                            <div class="modal-caja">
                                <div class="modal-caja-header">
                                    <h3 id="modalTitulo">Actualizar / Resolver Ticket</h3>
                                    <button type="button" class="modal-cerrar-btn" onclick="cerrarModalResolver()">&times;</button>
                                </div>

                                <form method="POST" action="../../acciones/mesa_ayuda/actualizar_ticket.php">
                                    <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                                    <input type="hidden" name="origen" value="<?php echo htmlspecialchars($origen); ?>">
                                    <input type="hidden" name="accion" value="actualizar_estado">

                                    <div class="grupoFormulario" style="margin-bottom: 12px;">
                                        <label for="modal_estado">Cambiar estado</label>
                                        <select id="modal_estado" name="estado" required style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 3px; margin-top: 4px;">
                                            <option value="Pendiente" <?php echo ($ticket['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="En Proceso" <?php echo ($ticket['estado'] == 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                            <option value="Resuelto" <?php echo ($ticket['estado'] == 'Resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                                        </select>
                                    </div>

                                    <div class="grupoFormulario" style="margin-bottom: 12px;">
                                        <label for="modal_diagnostico">Diagnóstico (opcional)</label>
                                        <textarea id="modal_diagnostico" name="diagnostico" rows="3" placeholder="Describí el problema encontrado..." style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 3px; margin-top: 4px; resize: vertical;"></textarea>
                                    </div>

                                    <div class="grupoFormulario" style="margin-bottom: 12px;">
                                        <label for="modal_solucion">Solución aplicada (opcional)</label>
                                        <textarea id="modal_solucion" name="solucion_aplicada" rows="3" placeholder="Describí qué se hizo para resolverlo..." style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 3px; margin-top: 4px; resize: vertical;"></textarea>
                                    </div>

                                    <div class="grupoFormulario" style="margin-bottom: 16px;">
                                        <label for="modal_equipo_diag">Equipo intervenido (opcional)</label>
                                        <select id="modal_equipo_diag" name="id_equipo_diag" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 3px; margin-top: 4px;">
                                            <option value="">— Ninguno —</option>
                                            <?php if ($res_equipos): $res_equipos->data_seek(0); while ($eq = $res_equipos->fetch_assoc()): ?>
                                                <option value="<?php echo $eq['id_equipo']; ?>" <?php echo ($ticket['id_equipo'] == $eq['id_equipo']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($eq['nombre']); ?>
                                                </option>
                                            <?php endwhile; endif; ?>
                                        </select>
                                    </div>

                                    <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #eee; padding-top: 12px;">
                                        <button type="button" class="btn-accion-ticket" onclick="cerrarModalResolver()">Cancelar</button>
                                        <button type="submit" class="btn-accion-ticket btn-primario-accion">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <script>
                            function abrirModalResolver(estadoInicial) {
                                const modal = document.getElementById('modalResolverTicket');
                                if (!modal) return;
                                if (estadoInicial) {
                                    const selectEstado = document.getElementById('modal_estado');
                                    if (selectEstado) selectEstado.value = estadoInicial;
                                }
                                modal.classList.add('activo');
                            }

                            function cerrarModalResolver() {
                                const modal = document.getElementById('modalResolverTicket');
                                if (modal) modal.classList.remove('activo');
                            }

                            function cerrarModalFuera(e) {
                                if (e.target.id === 'modalResolverTicket') {
                                    cerrarModalResolver();
                                }
                            }

                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape') {
                                    cerrarModalResolver();
                                }
                            });
                        </script>
                    <?php endif; ?>
                </div>

                <!-- COLUMNA DERECHA: COMENTARIOS -->
                <div class="ticket-col-der">
                    <h2>Comentarios</h2>

                    <!-- Lista de comentarios -->
                    <div class="comentarios-scroll-box">
                        <?php if ($res_com && $res_com->num_rows > 0): ?>
                            <?php while ($com = $res_com->fetch_assoc()): ?>
                                <div class="comentario-tarjeta <?php echo ($com['id_usuario'] == $id_usuario) ? 'es-mio' : ''; ?>">
                                    <div class="comentario-cabecera">
                                        <span class="comentario-autor">
                                            <b><?php echo htmlspecialchars($com['autor']); ?></b>
                                            <span style="font-size: 11px; color: #555;">(<?php echo htmlspecialchars($com['nombre_rol']); ?>)</span>
                                        </span>
                                        <span class="comentario-fecha"><?php echo date('d/m/Y H:i', strtotime($com['fecha_creacion'])); ?></span>
                                    </div>
                                    <div class="comentario-cuerpo">
                                        <?php echo nl2br(htmlspecialchars($com['comentario'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="comentarios-vacio">
                                No hay comentarios registrados en este ticket.
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

                    <!-- Formulario para agregar comentario -->
                    <h3 style="font-size: 14px; margin-bottom: 8px;">Agregar comentario</h3>
                    <form method="POST" action="../../acciones/mesa_ayuda/guardar_comentario.php" class="form-agregar-comentario">
                        <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                        <input type="hidden" name="origen" value="<?php echo htmlspecialchars($origen); ?>">
                        <textarea name="comentario" rows="3" placeholder="Escriba un comentario para el técnico..." required></textarea>
                        <div style="margin-top: 6px;">
                            <button type="submit" class="btn-enviar-comentario">Enviar comentario</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>