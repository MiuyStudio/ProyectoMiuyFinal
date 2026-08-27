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

// 1. KPI RESUMEN: Equipos
$sql_kpi_eq = "SELECT 
                   COUNT(*) AS total,
                   SUM(CASE WHEN estado = 'Disponible' THEN 1 ELSE 0 END) AS disponibles,
                   SUM(CASE WHEN estado = 'En Uso' THEN 1 ELSE 0 END) AS en_uso,
                   SUM(CASE WHEN estado = 'En Mantenimiento' THEN 1 ELSE 0 END) AS en_mantenimiento,
                   SUM(CASE WHEN estado = 'De Baja' THEN 1 ELSE 0 END) AS de_baja
               FROM equipos";
$res_kpi_eq = $conn->query($sql_kpi_eq);
$kpi_eq = $res_kpi_eq ? $res_kpi_eq->fetch_assoc() : ['total' => 0, 'disponibles' => 0, 'en_uso' => 0, 'en_mantenimiento' => 0, 'de_baja' => 0];

// 2. KPI RESUMEN: Tickets
$sql_kpi_tk = "SELECT 
                   COUNT(*) AS total,
                   SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                   SUM(CASE WHEN estado = 'En Proceso' THEN 1 ELSE 0 END) AS en_proceso,
                   SUM(CASE WHEN estado = 'Resuelto' THEN 1 ELSE 0 END) AS resueltos
               FROM tickets";
$res_kpi_tk = $conn->query($sql_kpi_tk);
$kpi_tk = $res_kpi_tk ? $res_kpi_tk->fetch_assoc() : ['total' => 0, 'pendientes' => 0, 'en_proceso' => 0, 'resueltos' => 0];

$porcentaje_resueltos = ($kpi_tk['total'] > 0) ? round(($kpi_tk['resueltos'] / $kpi_tk['total']) * 100) : 0;

// 3. MÉTRICA: Equipos con más fallas
$sql_fallas = "SELECT e.nombre AS equipo, e.numero_serie, COUNT(t.id_ticket) AS fallas
               FROM equipos e
               INNER JOIN tickets t ON e.id_equipo = t.id_equipo
               GROUP BY e.id_equipo, e.nombre, e.numero_serie
               ORDER BY fallas DESC
               LIMIT 6";
$res_fallas = $conn->query($sql_fallas);

// 4. MÉTRICA: Tiempos promedio de resolución
// Promedio General
$sql_prom_gen = "SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_actualizacion)) AS min_promedios
                 FROM tickets
                 WHERE estado = 'Resuelto' AND fecha_actualizacion >= fecha_creacion";
$res_prom_gen = $conn->query($sql_prom_gen);
$row_prom_gen = ($res_prom_gen) ? $res_prom_gen->fetch_assoc() : null;
$promedio_general_texto = formatearTiempo($row_prom_gen['min_promedios'] ?? 0);

// Casos Críticos o Altos
$sql_prom_crit = "SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_actualizacion)) AS min_promedios
                  FROM tickets
                  WHERE estado = 'Resuelto' 
                    AND fecha_actualizacion >= fecha_creacion
                    AND (prioridad = 'Crítica' OR prioridad = 'Alta')";
$res_prom_crit = $conn->query($sql_prom_crit);
$row_prom_crit = ($res_prom_crit) ? $res_prom_crit->fetch_assoc() : null;
$promedio_critico_texto = formatearTiempo($row_prom_crit['min_promedios'] ?? 0);

// 5. MÉTRICA: Tickets por técnico y efectividad (%)
$sql_tecnicos = "SELECT CONCAT(u.nombre, ' ', u.apellido) AS nombre_tecnico,
                        COUNT(t.id_ticket) AS asignados,
                        SUM(CASE WHEN t.estado = 'Resuelto' THEN 1 ELSE 0 END) AS resueltos
                 FROM usuarios u
                 INNER JOIN tickets t ON u.id_usuario = t.id_tecnico
                 GROUP BY u.id_usuario, u.nombre, u.apellido
                 ORDER BY resueltos DESC";
$res_tecnicos = $conn->query($sql_tecnicos);

// 6. MÉTRICA: Incidencias por categoría
$sql_cat_tk = "SELECT COALESCE(c.nombre_categoria, 'Sin Categoría') AS categoria, COUNT(t.id_ticket) AS total
               FROM tickets t
               LEFT JOIN categorias c ON t.id_categoria = c.id_categoria
               GROUP BY t.id_categoria, c.nombre_categoria
               ORDER BY total DESC
               LIMIT 5";
$res_cat_tk = $conn->query($sql_cat_tk);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Métricas y Reportes</title>
    <link rel="icon" type="image/png" href="../../assets/utu.png">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
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
                <li><a href="dashboard.php" class="activo">Métricas y Reportes</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <!-- TARJETAS DE INDICADORES CLAVE (KPIs) -->
            <div class="filaTarjetas">
                <!-- Tarjeta Equipos -->
                <div class="tarjeta">
                    <div class="tarjeta-titulo">Equipos en Inventario</div>
                    <div class="tarjeta-numero"><?php echo intval($kpi_eq['total']); ?></div>
                    <div class="tarjeta-detalle">
                        <span class="punto verde"></span> <?php echo intval($kpi_eq['disponibles']); ?> disponibles<br>
                        <span class="punto naranja"></span> <?php echo intval($kpi_eq['en_uso']); ?> en uso<br>
                        <span class="punto amarillo"></span> <?php echo intval($kpi_eq['en_mantenimiento']); ?> en mantenimiento<br>
                        <span class="punto" style="background:#888;"></span> <?php echo intval($kpi_eq['de_baja']); ?> de baja
                    </div>
                </div>

                <!-- Tarjeta Tickets -->
                <div class="tarjeta">
                    <div class="tarjeta-titulo">Mesa de Ayuda</div>
                    <div class="tarjeta-numero"><?php echo intval($kpi_tk['total']); ?></div>
                    <div class="tarjeta-detalle">
                        <span class="punto rojo"></span> <?php echo intval($kpi_tk['pendientes']); ?> pendientes<br>
                        <span class="punto naranja"></span> <?php echo intval($kpi_tk['en_proceso']); ?> en proceso<br>
                        <span class="punto verde"></span> <?php echo intval($kpi_tk['resueltos']); ?> resueltos
                    </div>
                </div>

                <!-- Tarjeta Tiempos -->
                <div class="tarjeta">
                    <div class="tarjeta-titulo">Tiempo de Resolución</div>
                    <div class="tarjeta-numero" style="font-size: 26px;"><?php echo htmlspecialchars($promedio_general_texto); ?></div>
                    <div class="tarjeta-detalle">
                        <strong>Promedio General</strong><br>
                        Casos Críticos: <strong><?php echo htmlspecialchars($promedio_critico_texto); ?></strong><br>
                        Efectividad global: <strong><?php echo $porcentaje_resueltos; ?>%</strong>
                    </div>
                </div>
            </div>

            <!-- FILA 1: EQUIPOS CON MÁS FALLAS Y TIEMPOS DE RESOLUCIÓN -->
            <div class="filaDoble">

                <!-- Equipos con más fallas -->
                <div class="seccion">
                    <h2>Equipos con más fallas</h2>
                    <table class="tablaSimple">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipo</th>
                                <th>N° Serie</th>
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
                                        <td><?php echo $i; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['equipo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['numero_serie'] ?? '—'); ?></td>
                                        <td class="text-center text-rojo fw-bold"><?php echo intval($row['fallas']); ?></td>
                                    </tr>
                                    <?php
                                    $i++;
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay fallas de equipos registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Incidencias por Categoría -->
                <div class="seccion">
                    <h2>Incidencias por Categoría</h2>
                    <table class="tablaSimple">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th class="text-center">Total Tickets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($res_cat_tk && $res_cat_tk->num_rows > 0): ?>
                                <?php while ($cat = $res_cat_tk->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['categoria']); ?></td>
                                        <td class="text-center fw-bold"><?php echo intval($cat['total']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">No hay datos de categorías registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- FILA 2: RENDIMIENTO POR TÉCNICO -->
            <div class="seccion">
                <h2>Rendimiento y Productividad por Técnico</h2>
                <table class="tablaSimple">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th class="text-center">Tickets Asignados</th>
                            <th class="text-center">Tickets Resueltos</th>
                            <th class="text-center">Tasa de Efectividad</th>
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
                                    <td><strong><?php echo htmlspecialchars($row['nombre_tecnico']); ?></strong></td>
                                    <td class="text-center"><?php echo $asignados; ?></td>
                                    <td class="text-center text-verde fw-bold"><?php echo $resueltos; ?></td>
                                    <td class="text-center">
                                        <span class="badge-activa" style="font-size: 13px;">
                                            <?php echo $efectividad; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay datos de técnicos asignados registrados.</td>
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
