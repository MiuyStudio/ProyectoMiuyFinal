<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Lista de Equipos</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>
    <?php
    require_once '../../conexion.php';

    // 1. Obtener categorías para el desplegable
    $sql_categorias = "SELECT id_categoria, nombre_categoria 
                   FROM categorias 
                   WHERE id_categoria BETWEEN 8 AND 12 
                   ORDER BY nombre_categoria ASC";
    $res_categorias = $conn->query($sql_categorias);

    // Capturar filtros
    $cat_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $est_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
    // Usamos isset que es una función para comprobar que categoria o estado no están vacíos
    // Si no están vacíos entonces los obtenemos y guardamos en cat_filtro y est_filtro
    


    // 2. Construir la consulta de equipos con filtros dinámicos
    $sql_equipos = "SELECT e.id_equipo, e.numero_serie, e.nombre, m.nombre_marca, mo.nombre_modelo, c.nombre_categoria, e.estado
                FROM equipos e
                INNER JOIN categorias c ON e.id_categoria = c.id_categoria
                LEFT JOIN marcas m ON e.id_marca = m.id_marca
                LEFT JOIN modelos mo ON e.id_modelo = mo.id_modelo
                WHERE 1=1";
    // Usamos where 1=1 que esto será siempre verdadero por lo cual nos dejará después concatenar los AND que queramos.
    
    if (!empty($cat_filtro)) {
        // Usar intval para sanitizar en caso de recibir enteros
        $sql_equipos .= " AND e.id_categoria = " . intval($cat_filtro);
    }
    // Usamos empty que verifica si la variable está vacía o es null. Si el usuario selecciona una categoría, se filtrará por esa categoría. Pero si selecciona "Todas" que tiene un valor de ("") vacío, entonces no se filtrará por categoría.
    //Usamos intval que convierte el valor de la categoría en un INT, para evitar alguna inyección SQL, si $cat_filtro vale "8", intval("8") devuelve 8.
    // Si un atacante intenta inyectar código malicioso enviando "8; DROP TABLE equipos;", intval() lo convierte a 8.
    

    if (!empty($est_filtro)) {
        // Escapar string para prevenir inyección SQL
        $est_clean = $conn->real_escape_string($est_filtro);
        $sql_equipos .= " AND e.estado = '$est_clean'";
    }
    // Hacemos lo mismo que con el filtro de categorías, pero para el filtro de estados.
    //La función real_escape_string() limpia la cadena buscando caracteres peligrosos (como comillas simples ' o dobles ") y les agrega una barra invertida delante (\').
    //Es como intval pero para string, si  alguien intenta enviar un texto malicioso para alterar la consulta, la función lo convierte en texto plano que no hará nada.
    

    $sql_equipos .= " ORDER BY e.id_equipo ASC";
    $res_equipos = $conn->query($sql_equipos);
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
                <li><a href="inventario.php" class="activo">Equipos</a></li>
                <li><a href="categorias.php">Categorías</a></li>
                <li><a href="agregar_marca.php">Agregar marca</a></li>
                <li><a href="agregar_modelo.php">Agregar modelo</a></li>
                <li><a href="agregar_equipo.php">Agregar equipo</a></li>
            </ul>
        </div>

        <!-- contenido principal -->
        <div class="areaContenido">

            <!-- filtros -->
            <div class="seccion">
                <h2>Lista de equipos</h2>

                <form method="GET" action="inventario.php" class="filtros">
                    <label>Categoría:
                        <select name="categoria" id="filtroCategoria">
                            <option value="">Todas</option>
                            <?php while ($cat = $res_categorias->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat_filtro == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>

                    <label>Estado:
                        <select name="estado" id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="Disponible" <?php echo ($est_filtro == 'Disponible') ? 'selected' : ''; ?>>
                                Disponible
                            </option>
                            <option value="En Uso" <?php echo ($est_filtro == 'En Uso') ? 'selected' : ''; ?>>
                                En Uso
                            </option>
                            <option value="En Mantenimiento" <?php echo ($est_filtro == 'En Mantenimiento') ? 'selected' : ''; ?>>
                                En Mantenimiento
                            </option>
                            <option value="De Baja" <?php echo ($est_filtro == 'De Baja') ? 'selected' : ''; ?>>
                                De Baja
                            </option>
                        </select>
                    </label>

                    <button type="submit">Filtrar</button>
                </form>

                <table class="tablaInventario">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>N° Serie</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_equipos && $res_equipos->num_rows > 0): ?>
                            <?php while ($equipo = $res_equipos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $equipo['id_equipo']; ?></td>
                                    <td><?php echo htmlspecialchars($equipo['numero_serie']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_marca']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_modelo']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_categoria']); ?></td>
                                    <!-- htmlspecialchars(string) Convierte caracteres especiales de HTML en entidades HTML (por ejemplo, < pasa a ser &lt; y " pasa a ser &quot;). Previene vulnerabilidades XSS (Cross-Site Scripting) al evitar que texto proveniente de la base de datos sea interpretado como código HTML o JavaScript por el navegador. -->
                                    <td>
                                        <span class="estado<?php echo str_replace(' ', '', $equipo['estado']); ?>">
                                            <?php echo htmlspecialchars($equipo['estado']); ?>
                                        </span>
                                        <!-- str_replace(' ', '', $equipo['estado']) Reemplaza todos los espacios en blanco en la cadena $equipo['estado'] por una cadena vacía. En las primeras comillas se especifica qué se debe reemplazar, en las segundas el valor que reemplazará a lo encontrado y en las terceras la variable.
                                            Si $equipo['estado'] por ejemplo tiene el valor "En Reparacion", PHP elimina el espacio y genera la clase CSS estadoEnReparacion.
                                            Esto nos permite aplicar estilos distintos en el css, ej: 
                                            .estadoEnReparacion {
                                                color: orange;
                                            }
                                            -->
                                    </td>
                                    <td><button type="button">Ver</button></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No hay equipos registrados.</td>
                                <!-- colspan="8" colspan es un atributo HTML que se utiliza en celdas de tablas (<td> o <th>) y sirve para indicar que esa celda debe ocupar el ancho de varias columnas.  -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>

</html>