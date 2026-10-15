<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

$rol = intval($_SESSION['usuario_rol']);
$error = $_GET['error'] ?? '';
$origen = $_GET['origen'] ?? '';
$volver_cancelar = ($origen === 'todos') ? 'todos_tickets.php' : 'mesa_ayuda.php';

// Categorías para tickets (ids 1-6)
$sql_cat = "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_categoria BETWEEN 1 AND 6";
$res_cat = $conn->query($sql_cat);

// Equipos para el modal (excluyendo equipos dados de baja) y verificando si tienen ticket activo
$sql_eq = "SELECT e.id_equipo, e.nombre, e.numero_serie, c.nombre_categoria,
                  t.id_ticket AS id_ticket_activo, t.titulo AS titulo_ticket_activo, t.estado AS estado_ticket_activo
           FROM equipos e 
           LEFT JOIN categorias c ON e.id_categoria = c.id_categoria 
           LEFT JOIN tickets t ON e.id_equipo = t.id_equipo AND t.estado IN ('Pendiente', 'En Proceso')
           WHERE e.estado != 'De Baja' 
           ORDER BY e.nombre ASC";
$res_eq = $conn->query($sql_eq);
$equipos_lista = [];
if ($res_eq) {
    while ($eq = $res_eq->fetch_assoc()) {
        $equipos_lista[] = $eq;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Nuevo Ticket</title>
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
                <li><a href="mesa_ayuda.php">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php" class="activo">Nuevo Ticket</a></li>
                <?php if ($rol == 1 || $rol == 2): ?>
                    <li><a href="todos_tickets.php">Todos los Tickets</a></li>
                    <li><a href="equipos_atencion.php">Equipos con atención</a></li>
                    <li><a href="diagnosticos.php">Diagnósticos</a></li>
                    <li><a href="soluciones.php">Soluciones Aplicadas</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="areaContenido">

            <div class="seccion">
                <h2>Crear Nuevo Ticket</h2>

                <?php if (!empty($error)): ?>
                    <p class="mensaje-error"><?= htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form id="formularioNuevoTicket" action="../../acciones/mesa_ayuda/guardar_ticket.php" method="POST">

                    <div class="grupoFormulario">
                        <label for="titulo">Título del problema *</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Ej: No puedo abrir el programa"
                            required>
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
                            <?php if ($res_cat):
                                while ($cat = $res_cat->fetch_assoc()): ?>
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
                        <label for="nombre_equipo_seleccionado">Equipo afectado (opcional)</label>
                        <input type="hidden" id="id_equipo" name="id_equipo" value="">
                        <div class="selector-equipo-contenedor">
                            <input type="text" id="nombre_equipo_seleccionado" value="" placeholder="— Ninguno seleccionado —" readonly>
                            <button type="button" class="btn-secundario" onclick="abrirModalEquipos()">Buscar equipo</button>
                            <button type="button" class="btn-secundario" id="btnLimpiarEquipo" onclick="limpiarEquipo()" title="Quitar equipo seleccionado">✕</button>
                        </div>
                    </div>

                    <input type="hidden" name="origen" value="<?php echo htmlspecialchars($origen); ?>">
                    <div class="botonesFormulario">
                        <button type="submit">Enviar Ticket</button>
                        <a href="<?php echo htmlspecialchars($volver_cancelar); ?>" class="btn-cancelar">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- MODAL SELECTOR DE EQUIPO -->
    <div id="modalSeleccionarEquipo" class="modal-overlay">
        <div class="modal-equipos">
            <div class="modal-header">
                <h2>Seleccionar Equipo Afectado</h2>
                <span class="modal-cerrar" onclick="cerrarModalEquipos()">&times;</span>
            </div>

            <div class="modal-buscador">
                <input type="text" id="inputBuscarEquipo" placeholder="Buscar por nombre, n° de serie o categoría..." oninput="filtrarEquiposModal()">
            </div>

            <div class="modal-tabla-scroll">
                <table class="tablaEquiposModal" id="tablaEquiposModal">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>N° Serie</th>
                            <th>Categoría</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($equipos_lista)): ?>
                            <?php foreach ($equipos_lista as $eq): ?>
                                <tr class="fila-equipo" 
                                    data-nombre="<?php echo htmlspecialchars($eq['nombre'] ?? '', ENT_QUOTES); ?>"
                                    data-serie="<?php echo htmlspecialchars($eq['numero_serie'] ?? '', ENT_QUOTES); ?>"
                                    data-categoria="<?php echo htmlspecialchars($eq['nombre_categoria'] ?? '', ENT_QUOTES); ?>">
                                    <td><strong><?php echo htmlspecialchars($eq['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($eq['numero_serie'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($eq['nombre_categoria'] ?? '—'); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn-seleccionar-modal" 
                                            onclick="seleccionarEquipo(<?php echo $eq['id_equipo']; ?>, '<?php echo htmlspecialchars($eq['nombre'], ENT_QUOTES); ?>', <?php echo !empty($eq['id_ticket_activo']) ? intval($eq['id_ticket_activo']) : 0; ?>, '<?php echo htmlspecialchars($eq['titulo_ticket_activo'] ?? '', ENT_QUOTES); ?>')">
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="texto-vacio">No hay equipos disponibles.</td>
                            </tr>
                        <?php endif; ?>
                        <tr id="sinCoincidencias">
                            <td colspan="4" class="texto-vacio">No se encontraron equipos que coincidan con la búsqueda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModalEquipos()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE AVISO (TICKET EN CURSO - ESTILO NOTIF) -->
    <div id="modalAvisoTicketActivo" class="modal-aviso-overlay">
        <div class="cajita1">
            <h3>Aviso</h3>
            <div class="cajita2">
                <h4>Este equipo ya tiene un ticket en curso.</h4>
                <p>¿Deseas agregar un comentario para el técnico?</p>
                <div id="notifInfoDetalle" class="notif-ticket-info"></div>
                <div class="notif-botones">
                    <button type="button" class="notif-btn-click" id="btnAceptarNotif" onclick="redirigirATicketActivo()">Aceptar</button>
                    <button type="button" class="notif-btn-cancelar" onclick="cerrarModalAviso()">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/nuevo_ticket.js"></script>
    <?php $conn->close(); ?>
</body>

</html>