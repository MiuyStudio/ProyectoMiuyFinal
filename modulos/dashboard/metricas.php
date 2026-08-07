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

// Función auxiliar para formatear minutos en formato legible (ej: 4h 15m)
function formatearTiempo($minutosTotales)
{
    if (!$minutosTotales || $minutosTotales <= 0) {
        return "N/A";
    }
    $minutosTotales = round($minutosTotales);
    $horas = floor($minutosTotales / 60);
    $minutos = $minutosTotales % 60;

    if ($horas > 0) {
        return $horas . "h " . $minutos . "m";
    }
    return $minutos . "m";
}

// 1. MÉTRICA 1: Equipos con más fallas
$sql_fallas = "SELECT e.nombre AS equipo, COUNT(t.id_ticket) AS fallas
                   FROM equipos e
                   INNER JOIN tickets t ON e.id_equipo = t.id_equipo
                   GROUP BY e.id_equipo, e.nombre
                   ORDER BY fallas DESC
                   LIMIT 6";
$res_fallas = $conn->query($sql_fallas);

// 2. MÉTRICA 2: Tiempos promedio de resolución
// Promedio General
$sql_prom_gen = "SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_actualizacion)) AS min_promedios
                     FROM tickets
                     WHERE estado = 'Resuelto' AND fecha_actualizacion >= fecha_creacion";
$res_prom_gen = $conn->query($sql_prom_gen);
$row_prom_gen = ($res_prom_gen) ? $res_prom_gen->fetch_assoc() : null;
$promedio_general_texto = formatearTiempo($row_prom_gen['min_promedios'] ?? 0);

// Casos Críticos (Alta o Crítica)
$sql_prom_crit = "SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_actualizacion)) AS min_promedios
                      FROM tickets
                      WHERE estado = 'Resuelto' 
                        AND fecha_actualizacion >= fecha_creacion
                        AND (prioridad = 'Crítica' OR prioridad = 'Alta')";
$res_prom_crit = $conn->query($sql_prom_crit);
$row_prom_crit = ($res_prom_crit) ? $res_prom_crit->fetch_assoc() : null;
$promedio_critico_texto = formatearTiempo($row_prom_crit['min_promedios'] ?? 0);

// 3. MÉTRICA 3: Tickets por técnico y efectividad (%)
$sql_tecnicos = "SELECT CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico,
                            COUNT(t.id_ticket) AS asignados,
                            SUM(CASE WHEN t.estado = 'Resuelto' THEN 1 ELSE 0 END) AS resueltos
                     FROM usuarios u
                     INNER JOIN tickets t ON u.id_usuario = t.id_tecnico
                     GROUP BY u.id_usuario, u.nombre, u.apellido
                     ORDER BY resueltos DESC";
$res_tecnicos = $conn->query($sql_tecnicos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Métricas y Reportes</title>
    <link rel="stylesheet" href="../css/metricas.css">
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
                <li><a href="diagnosticos.php">Diagnósticos</a></li>
                <li><a href="soluciones.php">Soluciones Aplicadas</a></li>
                <li><a href="metricas.php" class="activo">Métricas y Reportes</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <h2>Dashboard de métricas</h2>

            <!-- Fila Superior -->
            <div class="fila-doble-metricas">

                <!-- Equipos con más fallas -->
                <div class="seccion">
                    <h2>Equipos con más fallas</h2>
                    <table class="tablaSimple tabla-fallas">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th class="text-center">Fallas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if ($res_fallas && $res_fallas->num_rows > 0):
                                while ($row = $res_fallas->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $i . ". " . htmlspecialchars($row['equipo']); ?></td>
                                        <td class="num-fallas"><?php echo intval($row['fallas']); ?></td>
                                    </tr>
                                    <?php
                                    $i++;
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="2" class="text-center">No hay fallas registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tiempos promedio de resolución -->
                <div class="seccion">
                    <h2>Tiempos promedio de resolución</h2>
                    <div class="caja-promedio-contenedor">
                        <div class="caja-promedio-item">
                            <div class="titulo-item">Promedio General</div>
                            <div class="valor-item"><?php echo htmlspecialchars($promedio_general_texto); ?></div>
                        </div>
                        <div class="caja-promedio-item">
                            <div class="titulo-item">Casos Críticos</div>
                            <div class="valor-item"><?php echo htmlspecialchars($promedio_critico_texto); ?></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Fila Inferior: Tickets por Técnico -->
            <div class="seccion">
                <h2>Tickets por Técnico</h2>
                <table class="tablaSimple tabla-tecnicos">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th class="text-center">Asignados</th>
                            <th class="text-center">Resueltos</th>
                            <th class="text-center">Efectividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_tecnicos && $res_tecnicos->num_rows > 0): ?>
                            <?php while ($row = $res_tecnicos->fetch_assoc()): ?>
                                <?php
                                $asignados = intval($row['asignados']);
                                $resueltos = intval($row['resueltos']);
                                $efectividad = ($asignados > 0) ? round(($resueltos / $asignados) * 100) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nombre_tecnico']); ?></td>
                                    <td class="text-center"><?php echo $asignados; ?></td>
                                    <td class="text-center"><?php echo $resueltos; ?></td>
                                    <td class="porcentaje-efectividad"><span
                                            class="badge-porcentaje"><?php echo $efectividad; ?>%</span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay datos de técnicos asignados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>