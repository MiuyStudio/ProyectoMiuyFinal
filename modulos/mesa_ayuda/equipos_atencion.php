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

    function e($val, $defecto = '') {
        return htmlspecialchars($val ?? $defecto, ENT_QUOTES, 'UTF-8');
    }

    // Equipos vinculados a tickets de soporte en Mesa de Ayuda
    $sql = "SELECT e.id_equipo, e.numero_serie, e.nombre, e.estado,
                   c.nombre_categoria,
                   CONCAT_WS(' ', m.nombre_marca, mo.nombre_modelo) AS marca_modelo,
                   COUNT(t.id_ticket) AS total_tickets,
                   MAX(t.fecha_creacion) AS ultima_incidencia
            FROM equipos e
            INNER JOIN tickets t ON e.id_equipo = t.id_equipo
            LEFT JOIN marcas m ON e.id_marca = m.id_marca
            LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
            LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
            GROUP BY e.id_equipo
            ORDER BY total_tickets DESC, ultima_incidencia DESC";
    $res = $conn->query($sql);
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Equipos atendidos en soporte</title>
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

        <div class="barraLateral">
            <ul>
                <li><a href="mesa_ayuda.php">Mis Tickets</a></li>
                <li><a href="nuevo_ticket.php">Nuevo Ticket</a></li>
                <li><a href="todos_tickets.php">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php" class="activo">Equipos con atención</a></li>
                <li><a href="diagnosticos.php">Diagnósticos</a></li>
                <li><a href="soluciones.php">Soluciones Aplicadas</a></li>
            </ul>
        </div>

        <div class="areaContenido">

            <div class="seccion">
                <h2>Equipos Atendidos en Soporte Técnico</h2>

                <table class="tablaTickets">
                    <thead>
                        <tr>
                            <th>N° Serie</th>
                            <th>Equipo</th>
                            <th>Marca / Modelo</th>
                            <th>Categoría</th>
                            <th>Tickets Reportados</th>
                            <th>Estado Actual</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && $res->num_rows > 0): ?>
                            <?php foreach ($res as $eq): ?>
                            <tr>
                                <td><?= e($eq['numero_serie'], '—') ?></td>
                                <td><strong><?= e($eq['nombre']) ?></strong></td>
                                <td><?= e($eq['marca_modelo'], '—') ?></td>
                                <td><?= e($eq['nombre_categoria'], '—') ?></td>
                                <td>
                                    <span class="badge-tickets">
                                        <?= $eq['total_tickets']; ?> ticket(s)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-equipo estado<?= str_replace(' ', '', $eq['estado']); ?>">
                                        <?= e($eq['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../inventario/ver_equipo.php?id=<?= $eq['id_equipo']; ?>&origen=equipos_atencion">
                                        <button type="button" class="btn-ficha">Ver Historial</button>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No hay equipos vinculados a tickets de soporte registrados aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>