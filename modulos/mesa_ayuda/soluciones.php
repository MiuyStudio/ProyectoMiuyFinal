<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

$rol = intval($_SESSION['usuario_rol'] ?? 0);
if ($rol == 3) {
    header("Location: mesa_ayuda.php");
    exit();
}

function e($val, $defecto = '') {
    return htmlspecialchars($val ?? $defecto, ENT_QUOTES, 'UTF-8');
}

// 1. Equipos con cantidad de soluciones aplicadas registradas (solucion_aplicada no vacía)
$sql_equipos = "SELECT e.id_equipo, e.nombre, e.numero_serie, e.estado,
                       c.nombre_categoria,
                       CONCAT_WS(' ', m.nombre_marca, mo.nombre_modelo) AS marca_modelo,
                       COUNT(d.id_diagnostico) AS total_soluciones
                FROM equipos e
                LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
                LEFT JOIN marcas m ON e.id_marca = m.id_marca
                LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
                LEFT JOIN diagnosticos d ON d.id_equipo = e.id_equipo
                    AND d.solucion_aplicada IS NOT NULL 
                    AND d.solucion_aplicada != ''
                GROUP BY e.id_equipo, e.nombre, e.numero_serie, e.estado, c.nombre_categoria, m.nombre_marca, mo.nombre_modelo
                ORDER BY e.nombre ASC";
$res_equipos = $conn->query($sql_equipos);

// 2. Todas las soluciones aplicadas (registros donde solucion_aplicada no está vacía)
$sql_sol = "SELECT d.*,
                   d.id_equipo AS equipo_efectivo,
                   t.titulo AS titulo_ticket,
                   CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico
            FROM diagnosticos d
            INNER JOIN tickets t ON d.id_ticket = t.id_ticket
            LEFT JOIN usuarios u ON d.id_tecnico = u.id_usuario
            WHERE d.solucion_aplicada IS NOT NULL AND d.solucion_aplicada != ''
            ORDER BY d.id_diagnostico DESC";
$res_sol = $conn->query($sql_sol);

$soluciones_por_equipo = [];
if ($res_sol) {
    foreach ($res_sol as $row) {
        if ($row['equipo_efectivo']) {
            $soluciones_por_equipo[$row['equipo_efectivo']][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Soluciones Aplicadas</title>
    <link rel="icon" type="image/png" href="../../assets/utu.png">
    <link rel="stylesheet" href="../css/soluciones.css?v=<?php echo time(); ?>">
</head>

<body>

    <!-- cabecera de la página -->
    <div class="encabezado">
        <h1>Mesa de Ayuda</h1>
        <span>
            Usuario: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
            (<?php echo htmlspecialchars($_SESSION['nombre_rol']); ?>) |
            <a href="../../logout.php" target="_top">Cerrar sesión</a>
        </span>
    </div>

    <!-- layout principal -->
    <div class="contenedorPrincipal">

        <!-- menú izquierdo -->
        <div class="barraLateral">
            <ul>
                <li><a href="mesa_ayuda.php">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php">Nuevo Ticket</a></li>
                <?php if ($rol == 1 || $rol == 2): ?>
                <li><a href="todos_tickets.php">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php">Equipos con atención</a></li>
                <li><a href="diagnosticos.php">Diagnósticos</a></li>
                <li><a href="soluciones.php" class="activo">Soluciones Aplicadas</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <div class="seccion">
                <h2>Soluciones Aplicadas por Equipo</h2>
                <p class="subtitulo-seccion">Listado de equipos con soluciones registradas por el equipo técnico. Haga clic en "Ver soluciones" para consultar el detalle.</p>
                <table class="tablaSimple">
                    <thead>
                        <tr>
                            <th>N° Serie</th>
                            <th>Nombre Equipo</th>
                            <th>Categoría</th>
                            <th>Marca / Modelo</th>
                            <th>Estado</th>
                            <th>Total Soluciones</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_equipos && $res_equipos->num_rows > 0): ?>
                            <?php foreach ($res_equipos as $eq): ?>
                                <tr>
                                    <td><?= e($eq['numero_serie'], 'S/N') ?></td>
                                    <td><strong><?= e($eq['nombre']) ?></strong></td>
                                    <td><?= e($eq['nombre_categoria'], 'N/A') ?></td>
                                    <td><?= e($eq['marca_modelo'], 'N/A') ?></td>
                                    <td><?= e($eq['estado']) ?></td>
                                    <td class="col-centrada">
                                        <?php if ($eq['total_soluciones'] > 0): ?>
                                            <?= $eq['total_soluciones'] ?>
                                        <?php else: ?>
                                            <span class="badge-sin-sol">Sin soluciones</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acciones-fila">
                                            <?php if ($eq['total_soluciones'] > 0): ?>
                                                <button class="btn-ver-sol" onclick="abrirModal(<?= $eq['id_equipo'] ?>, '<?= e(addslashes($eq['nombre'])) ?>')">
                                                    Ver soluciones
                                                </button>
                                            <?php else: ?>
                                                <span style="color: #777;">—</span>
                                            <?php endif; ?>
                                            <a href="../inventario/ver_equipo.php?id=<?= $eq['id_equipo'] ?>&origen=soluciones">
                                                <button type="button" class="btn-ficha">Ficha Equipo</button>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay equipos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Modal de Soluciones por Equipo -->
    <div id="modalSoluciones" class="modal-overlay">
        <div class="modal-contenido">
            <div class="modal-header">
                <h3 id="tituloModal">Soluciones Aplicadas</h3>
                <button class="btn-cerrar" onclick="cerrarModal()">Cerrar</button>
            </div>
            <div id="cuerpoModal">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        const solucionesPorEquipo = <?= json_encode($soluciones_por_equipo); ?>;
    </script>
    <script src="js/soluciones.js"></script>
    <?php $conn->close(); ?>
</body>
</html>
