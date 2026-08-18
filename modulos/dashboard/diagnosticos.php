<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

$rol = intval($_SESSION['usuario_rol'] ?? 0);
if ($rol == 3) {
    header("Location: ../../index.php");
    exit();
}

// 1. Consultar todos los equipos y la cantidad de diagnósticos que tienen
//    Cuenta diagnósticos vinculados directamente (d.id_equipo) o via su ticket (t.id_equipo)
$sql_equipos = "SELECT e.id_equipo, e.nombre, e.numero_serie, e.estado,
                       c.nombre_categoria, m.nombre_marca, mo.nombre_modelo,
                       COUNT(d.id_diagnostico) AS total_diagnosticos
                FROM equipos e
                LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
                LEFT JOIN marcas m ON e.id_marca = m.id_marca
                LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
                LEFT JOIN diagnosticos d ON (
                    d.id_equipo = e.id_equipo
                    OR (d.id_equipo IS NULL AND d.id_ticket IN (SELECT id_ticket FROM tickets WHERE id_equipo = e.id_equipo))
                )
                GROUP BY e.id_equipo, e.nombre, e.numero_serie, e.estado, c.nombre_categoria, m.nombre_marca, mo.nombre_modelo
                ORDER BY e.nombre ASC";
$res_equipos = $conn->query($sql_equipos);

// 2. Consultar todos los diagnósticos incluyendo el equipo efectivo (directo o via ticket)
$sql_diag = "SELECT d.*,
                    COALESCE(d.id_equipo, t.id_equipo) AS equipo_efectivo,
                    t.titulo AS titulo_ticket,
                    CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico
             FROM diagnosticos d
             LEFT JOIN tickets t ON d.id_ticket = t.id_ticket
             LEFT JOIN usuarios u ON d.id_tecnico = u.id_usuario
             ORDER BY d.id_diagnostico DESC";
$res_diag = $conn->query($sql_diag);

$diagnosticos_por_equipo = [];
if ($res_diag) {
    while ($row = $res_diag->fetch_assoc()) {
        $eq_id = $row['equipo_efectivo'];
        if ($eq_id) {
            if (!isset($diagnosticos_por_equipo[$eq_id])) {
                $diagnosticos_por_equipo[$eq_id] = [];
            }
            $diagnosticos_por_equipo[$eq_id][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Diagnósticos por Equipo</title>
    <link rel="icon" type="image/png" href="../../assets/utu.png">
    <link rel="stylesheet" href="../css/diagnosticos.css">
</head>

<body>

    <!-- cabecera de la página -->
    <div class="encabezado">
        <h1>Dashboard</h1>
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
                <li><a href="dashboard.php">Panel General</a></li>
                <li><a href="diagnosticos.php" class="activo">Diagnósticos</a></li>
                <li><a href="soluciones.php">Soluciones Aplicadas</a></li>
                <li><a href="metricas.php">Métricas y Reportes</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <div class="seccion">
                <h2>Diagnósticos por Equipo</h2>
                <p class="subtitulo-seccion">Seleccione un equipo para consultar el historial de diagnósticos realizados por el equipo técnico.</p>
                <table class="tablaSimple">
                    <thead>
                        <tr>
                            <th>N° Serie</th>
                            <th>Nombre Equipo</th>
                            <th>Categoría</th>
                            <th>Marca / Modelo</th>
                            <th>Estado</th>
                            <th>Total Diagnósticos</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_equipos && $res_equipos->num_rows > 0): ?>
                            <?php while ($eq = $res_equipos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($eq['numero_serie'] ?? 'S/N'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($eq['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($eq['nombre_categoria'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(($eq['nombre_marca'] ?? '') . ' ' . ($eq['nombre_modelo'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($eq['estado']); ?></td>
                                    <td class="text-center fw-bold"><?php echo $eq['total_diagnosticos']; ?></td>
                                    <td>
                                        <button class="btn-ver-diag" onclick="abrirModalDiagnosticos(<?php echo $eq['id_equipo']; ?>, '<?php echo htmlspecialchars(addslashes($eq['nombre'])); ?>')">
                                            Ver diagnósticos
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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

    <!-- Modal de Diagnósticos por Equipo -->
    <div id="modalDiagnosticos" class="modal-overlay">
        <div class="modal-contenido">
            <div class="modal-header">
                <h3 id="tituloModalDiagnostico">Diagnósticos del Equipo</h3>
                <button class="btn-cerrar" onclick="cerrarModalDiagnosticos()">Cerrar</button>
            </div>
            <div id="cuerpoModalDiagnostico">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        const diagnosticosPorEquipo = <?php echo json_encode($diagnosticos_por_equipo); ?>;

        function abrirModalDiagnosticos(idEquipo, nombreEquipo) {
            document.getElementById('tituloModalDiagnostico').innerText = 'Diagnósticos: ' + nombreEquipo;
            const contenedor = document.getElementById('cuerpoModalDiagnostico');
            
            const lista = diagnosticosPorEquipo[idEquipo] || [];

            if (lista.length === 0) {
                contenedor.innerHTML = '<p style="text-align: center; color: #777; padding: 20px 0;">No hay diagnósticos registrados para este equipo.</p>';
            } else {
                let html = '<table class="tablaSimple">';
                html += '<thead><tr><th>Ticket</th><th>Técnico</th><th>Diagnóstico</th><th>Solución Aplicada</th><th>Fecha</th></tr></thead>';
                html += '<tbody>';
                lista.forEach(function(d) {
                    let ticketTxt = d.id_ticket ? ('#' + d.id_ticket + (d.titulo_ticket ? ' - ' + d.titulo_ticket : '')) : 'N/A';
                    let fechaTxt = d.fecha_intervencion ? d.fecha_intervencion : (d.fecha ? d.fecha : 'N/A');
                    html += '<tr>';
                    html += '<td>' + escapeHtml(ticketTxt) + '</td>';
                    html += '<td>' + escapeHtml(d.nombre_tecnico || 'N/A') + '</td>';
                    html += '<td>' + escapeHtml(d.diagnostico || '') + '</td>';
                    html += '<td>' + escapeHtml(d.solucion_aplicada || '') + '</td>';
                    html += '<td>' + escapeHtml(fechaTxt) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                contenedor.innerHTML = html;
            }

            document.getElementById('modalDiagnosticos').style.display = 'flex';
        }

        function cerrarModalDiagnosticos() {
            document.getElementById('modalDiagnosticos').style.display = 'none';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
    <?php $conn->close(); ?>
</body>
</html>
