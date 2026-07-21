<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrar Usuarios</title>
    <link rel="stylesheet" href="../css/administrar_usuarios.css">
</head>

<body>
    <?php 
    require_once '../../conexion.php';
    $sql = "SELECT * FROM usuarios";
    $resultado = $conn->query($sql);
    ?>

    <!-- Cabecera de la pagina -->
    <div class="encabezado">
        <h1>Dashboard</h1>
        <span>Usuario: Marcel Matiaude | <a href="#">Cerrar sesión</a></span>
    </div>

    <!-- Layout principal -->
    <div class="contenedorPrincipal">

        <!-- Menu lateral -->
        <div class="barraLateral">
            <ul>
                <li><a href="dashboard.html">Panel General</a></li>
                <li><a href="diagnosticos.html">Diagnósticos</a></li>
                <li><a href="administrar_usuarios.php" class="activo">Administrar Usuarios</a></li>
            </ul>
        </div>

        <!-- Area de contenido -->
        <div class="areaContenido">
        
            <!-- Tabla de gestion de usuarios -->
            <div class="seccion">
                <h2>Lista de Usuarios</h2>
                <table class="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $fila['id_usuario']; ?></td>
                                <td><?php echo $fila['nombre'] . ' ' . $fila['apellido']; ?></td>
                                <td><?php echo $fila['ci']; ?></td>
                                <td><?php echo $fila['id_rol'] == 1 ? 'Administrador' : ($fila['id_rol'] == 2 ? 'Técnico' : 'Usuario'); ?></td>
                                <td>
                                    <button type="button" class="btn btn-guardar">Editar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Formulario para registrar un usuario nuevo -->
            <div class="seccion">
                <h2>Crear Nuevo Usuario</h2>
                <form class="formularioUsuario">
                    <div class="grupoFormulario">
                        <label for="nuevoNombre">Nombre Completo</label>
                        <input type="text" id="nuevoNombre" placeholder="Ej: Marcel Matiaude">
                    </div>
                    <div class="grupoFormulario">
                        <label for="nuevoUsuario">Cédula</label>
                        <input type="text" id="nuevoUsuario" placeholder="Ej: 512345678">
                    </div>
                    <div class="grupoFormulario">
                        <label for="nuevoRol">Rol Inicial</label>
                        <select id="nuevoRol">
                            <?php
                                $sql_roles = "SELECT * FROM roles";
                                $resultado_roles = $conn->query($sql_roles);
                                while($fila_rol = $resultado_roles->fetch_assoc()):
                            ?>
                                <option value="<?php echo $fila_rol['id_rol']; ?>">
                                    <?php echo $fila_rol['nombre_rol']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="botonesFormulario">
                        <button type="button" class="btn btn-crear">Crear Usuario</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</body>

</html>