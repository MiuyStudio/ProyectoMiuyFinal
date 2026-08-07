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

    // Equipos en mantenimiento o de baja
    $sql = "SELECT e.id_equipo, e.numero_serie, e.nombre, e.estado,
                   m.nombre_marca, mo.nombre_modelo, c.nombre_categoria
            FROM equipos e
            LEFT JOIN marcas m ON e.id_marca = m.id_marca
            LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
            INNER JOIN categorias c ON e.id_categoria = c.id_categoria
            WHERE e.estado IN ('En Mantenimiento', 'De Baja')
            ORDER BY e.estado ASC, e.nombre ASC";
    $res = $conn->query($sql);
    ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda - Equipos con atención</title>
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
                <li><a href="todos_tickets.php">Todos los Tickets</a></li>
                <li><a href="equipos_atencion.php" class="activo">Equipos con atención</a></li>
            </ul>
        </div>

        <div class="areaContenido">

            <div class="seccion">
                <h2>Equipos que requieren atención</h2>

                <table class="tablaTickets">
                    <thead>
                        <tr>
                            <th>N° Serie</th>
                            <th>Equipo</th>
                            <th>Marca / Modelo</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && $res->num_rows > 0): ?>
                            <?php while ($eq = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($eq['numero_serie'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($eq['nombre']); ?></td>
                                <td><?php echo htmlspecialchars(($eq['nombre_marca'] ?? '') . ' ' . ($eq['nombre_modelo'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($eq['nombre_categoria']); ?></td>
                                <td>
                                    <span class="estado<?php echo str_replace(' ', '', $eq['estado']); ?>">
                                        <?php echo htmlspecialchars($eq['estado']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No hay equipos en mantenimiento o de baja.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>