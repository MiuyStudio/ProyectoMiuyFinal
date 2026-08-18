<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

$rol = intval($_SESSION['usuario_rol']);
$error = $_GET['error'] ?? '';

// Categorías para tickets (ids 1-6)
$sql_cat = "SELECT id_categoria, nombre_categoria FROM categorias WHERE id_categoria BETWEEN 1 AND 6";
$res_cat = $conn->query($sql_cat);

// Equipos para el modal (excluyendo equipos dados de baja)
$sql_eq = "SELECT e.id_equipo, e.nombre, e.numero_serie, c.nombre_categoria 
           FROM equipos e 
           LEFT JOIN categorias c ON e.id_categoria = c.id_categoria 
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
                    <p
                        style="color: red; font-weight: bold; padding: 10px; background: #fce8e6; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
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
                            <button type="button" class="btn-secundario" id="btnLimpiarEquipo" onclick="limpiarEquipo()" style="display: none;" title="Quitar equipo seleccionado">✕</button>
                        </div>
                    </div>

                    <div class="botonesFormulario">
                        <button type="submit">Enviar Ticket</button>
                        <a href="mesa_ayuda.php" class="btn-cancelar">Cancelar</a>
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
                            <th style="text-align: center;">Acción</th>
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
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-seleccionar-modal" 
                                            onclick="seleccionarEquipo(<?php echo $eq['id_equipo']; ?>, '<?php echo htmlspecialchars($eq['nombre'], ENT_QUOTES); ?>')">
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #777; padding: 15px;">No hay equipos disponibles.</td>
                            </tr>
                        <?php endif; ?>
                        <tr id="sinCoincidencias" style="display: none;">
                            <td colspan="4" style="text-align: center; color: #777; padding: 15px;">No se encontraron equipos que coincidan con la búsqueda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModalEquipos()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
    function abrirModalEquipos() {
        document.getElementById('modalSeleccionarEquipo').classList.add('activo');
        const inputBuscar = document.getElementById('inputBuscarEquipo');
        inputBuscar.value = '';
        filtrarEquiposModal();
        setTimeout(() => inputBuscar.focus(), 100);
    }

    function cerrarModalEquipos() {
        document.getElementById('modalSeleccionarEquipo').classList.remove('activo');
    }

    function seleccionarEquipo(id, nombre) {
        document.getElementById('id_equipo').value = id;
        document.getElementById('nombre_equipo_seleccionado').value = nombre;
        document.getElementById('btnLimpiarEquipo').style.display = 'inline-block';
        cerrarModalEquipos();
    }

    function limpiarEquipo() {
        document.getElementById('id_equipo').value = '';
        document.getElementById('nombre_equipo_seleccionado').value = '';
        document.getElementById('btnLimpiarEquipo').style.display = 'none';
    }

    function filtrarEquiposModal() {
        const texto = document.getElementById('inputBuscarEquipo').value.toLowerCase().trim();
        const filas = document.querySelectorAll('#tablaEquiposModal tbody .fila-equipo');
        let visibles = 0;

        filas.forEach(fila => {
            const nombre = (fila.getAttribute('data-nombre') || '').toLowerCase();
            const serie = (fila.getAttribute('data-serie') || '').toLowerCase();
            const categoria = (fila.getAttribute('data-categoria') || '').toLowerCase();

            if (nombre.includes(texto) || serie.includes(texto) || categoria.includes(texto)) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        const sinCoincidencias = document.getElementById('sinCoincidencias');
        if (sinCoincidencias) {
            sinCoincidencias.style.display = (visibles === 0 && filas.length > 0) ? '' : 'none';
        }
    }

    // Cerrar al hacer clic fuera del contenido del modal
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modalSeleccionarEquipo');
        if (e.target === modal) {
            cerrarModalEquipos();
        }
    });
    </script>

    <?php $conn->close(); ?>
</body>

</html>