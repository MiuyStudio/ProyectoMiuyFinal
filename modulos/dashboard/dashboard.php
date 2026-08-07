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

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';

// 1. Consultar historial de asignaciones
$sql_asignaciones = "SELECT a.*, 
                            e.nombre AS nombre_equipo, e.numero_serie, e.id_equipo,
                            c.nombre_categoria,
                            CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario,
                            r.nombre_rol
                     FROM asignaciones a
                     INNER JOIN equipos e ON a.id_equipo = e.id_equipo
                     LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
                     INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                     LEFT JOIN roles r ON u.id_rol = r.id_rol
                     ORDER BY a.fecha_inicio DESC";
$res_asignaciones = $conn->query($sql_asignaciones);

// 2. Consultar equipos disponibles para el select
$res_equipos = $conn->query("SELECT id_equipo, nombre, numero_serie, estado FROM equipos WHERE estado = 'Disponible' ORDER BY nombre ASC");

// 3. Consultar usuarios activos para el select
$res_usuarios = $conn->query("SELECT u.id_usuario, u.nombre, u.apellido, r.nombre_rol FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id_rol WHERE u.activo = 1 ORDER BY u.nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel General y Asignaciones</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=2">
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
                <li><a href="dashboard.php" class="activo">Panel General</a></li>
                <li><a href="diagnosticos.php">Diagnósticos</a></li>
                <li><a href="soluciones.php">Soluciones Aplicadas</a></li>
                <li><a href="metricas.php">Métricas y Reportes</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-exito">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="mensaje-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- ===== REGISTRAR NUEVA ASIGNACIÓN ===== -->
            <div class="seccion">
                <h2>Registrar Nueva Asignación de Equipo</h2>
                <form method="POST" action="../../acciones/dashboard/guardar_asignacion.php" class="formularioAsignacion">
                    <input type="hidden" name="accion" value="asignar">

                    <div class="grupoFormulario">
                        <label for="id_equipo">Equipo *</label>
                        <select name="id_equipo" id="id_equipo" required>
                            <option value="">-- Seleccionar Equipo --</option>
                            <?php if ($res_equipos): ?>
                                <?php while ($eq = $res_equipos->fetch_assoc()): ?>
                                    <option value="<?php echo $eq['id_equipo']; ?>">
                                        <?php echo htmlspecialchars($eq['nombre'] . ' (' . ($eq['numero_serie'] ?? 'S/N') . ') - Estado: ' . $eq['estado']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="id_usuario">Usuario a Asignar *</label>
                        <select name="id_usuario" id="id_usuario" required>
                            <option value="">-- Seleccionar Usuario --</option>
                            <?php if ($res_usuarios): ?>
                                <?php while ($u = $res_usuarios->fetch_assoc()): ?>
                                    <option value="<?php echo $u['id_usuario']; ?>">
                                        <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido'] . ' (' . $u['nombre_rol'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="grupoFormulario">
                        <label for="fecha_inicio">Fecha y Hora de Inicio *</label>
                        <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-guardar">Asignar Equipo</button>
                    </div>
                </form>
            </div>

            <!-- ===== ASIGNACIONES E HISTORIAL ===== -->
            <div class="seccion">
                <h2>Historial de Asignaciones</h2>
                <table class="tablaSimple">
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Categoría</th>
                            <th>Asignado a</th>
                            <th>Rol</th>
                            <th>Desde</th>
                            <th>Hasta / Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_asignaciones && $res_asignaciones->num_rows > 0): ?>
                            <?php while ($asig = $res_asignaciones->fetch_assoc()): ?>
                                <?php 
                                    $ffin = $asig['fecha_fin'] ?? '';
                                    $es_activa = (empty($ffin) || strpos($ffin, '0000-00-00') !== false);
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($asig['nombre_equipo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($asig['nombre_categoria'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($asig['nombre_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($asig['nombre_rol'] ?? 'Usuario'); ?></td>
                                    <td><?php echo htmlspecialchars($asig['fecha_inicio']); ?></td>
                                    <td>
                                        <?php if ($es_activa): ?>
                                            <span class="badge-activa">Activa</span>
                                        <?php else: ?>
                                            <span class="badge-finalizada"><?php echo htmlspecialchars($asig['fecha_fin']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($es_activa): ?>
                                            <form method="POST" action="../../acciones/dashboard/guardar_asignacion.php" class="form-finalizar" onsubmit="return confirm('¿Desea finalizar esta asignación?');">
                                                <input type="hidden" name="accion" value="finalizar">
                                                <input type="hidden" name="id_equipo" value="<?php echo $asig['id_equipo']; ?>">
                                                <?php if (isset($asig['id_asignacion'])): ?>
                                                    <input type="hidden" name="id_asignacion" value="<?php echo $asig['id_asignacion']; ?>">
                                                <?php endif; ?>
                                                <button type="submit" class="btn-peligro-sm">Finalizar</button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay asignaciones registradas en la base de datos.</td>
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
