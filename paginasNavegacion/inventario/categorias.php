<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Categorías</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>
    <?php
    require_once '../../conexion.php';

    // Consulta filtrada por el rango de IDs de categorías (8 a 12)
    $sql_categorias = "SELECT c.id_categoria, c.nombre_categoria, c.descripcion, COUNT(e.id_equipo) AS total_equipos
                   FROM categorias c
                   LEFT JOIN equipos e ON c.id_categoria = e.id_categoria
                   WHERE c.id_categoria BETWEEN 8 AND 12
                   GROUP BY c.id_categoria, c.nombre_categoria, c.descripcion
                   ORDER BY c.id_categoria ASC";

    $res_categorias = $conn->query($sql_categorias);
    ?>
    <!-- cabecera de la página -->
    <div class="encabezado">
        <h1>Inventario</h1>
        <span>Usuario: Marcel Matiaude | <a href="#">Cerrar sesión</a></span>
    </div>

    <!-- layout principal -->
    <div class="contenedorPrincipal">

        <!-- menú de la izquierda -->
        <div class="barraLateral">
            <ul>
                <li><a href="inventario.php">Equipos</a></li>
                <li><a href="categorias.php" class="activo">Categorías</a></li>
                <li><a href="agregar_marca.php">Agregar marca</a></li>
                <li><a href="agregar_modelo.php">Agregar modelo</a></li>
                <li><a href="agregar_equipo.php">Agregar equipo</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <div class="seccion">
                <h2>Categorías de equipos</h2>

                <table class="tablaInventario">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Equipos registrados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_categorias && $res_categorias->num_rows > 0): ?>
                            <?php while ($cat = $res_categorias->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php echo $cat['id_categoria']; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($cat['descripcion']); ?>
                                    </td>
                                    <td>
                                        <?php echo $cat['total_equipos']; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No hay categorías registradas.</td>
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