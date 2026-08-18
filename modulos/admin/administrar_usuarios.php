<?php
session_start();
require_once '../../config/conexion.php';

// Verificar inicio de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

// Verificar permisos de Administrador (id_rol = 1)
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
    echo "<div style='padding: 30px; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: red;'>Acceso Restringido</h2>";
    echo "<p>Esta sección es de uso exclusivo para Administradores del sistema.</p>";
    echo "<a href='../../index.php' target='_top'>Volver al portal principal</a>";
    echo "</div>";
    exit();
}

$rol = intval($_SESSION['usuario_rol']);

$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Consultar todos los usuarios (activos e inactivos) junto con sus roles
$sql_usuarios = "SELECT u.*, r.nombre_rol 
                    FROM usuarios u 
                    INNER JOIN roles r ON u.id_rol = r.id_rol 
                    ORDER BY u.id_usuario ASC";
$res_usuarios = $conn->query($sql_usuarios);

// Consultar lista de roles para desplegables
$sql_roles = "SELECT * FROM roles ORDER BY id_rol ASC";
$res_roles = $conn->query($sql_roles);
$roles = [];
if ($res_roles) {
    while ($r = $res_roles->fetch_assoc()) {
        $roles[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Administrar Usuarios</title>
    <link rel="icon" type="image/png" href="../../assets/utu.png">
    <link rel="stylesheet" href="../css/administrar_usuarios.css">
</head>

<body>


    <!-- Cabecera de la pagina -->
    <div class="encabezado">
        <h1>Panel Admin</h1>
        <span>
            Usuario: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
            (<?php echo htmlspecialchars($_SESSION['nombre_rol']); ?>) |
            <a href="../../logout.php" target="_top">Cerrar sesión</a>
        </span>
    </div>

    <!-- Layout principal -->
    <div class="contenedorPrincipal">

        <!-- Menu lateral -->
        <div class="barraLateral">
            <ul>
                <li><a href="administrar_usuarios.php" class="activo">Administrar Usuarios</a></li>
            </ul>
        </div>

        <!-- Area de contenido -->
        <div class="areaContenido">

            <?php if (!empty($mensaje)): ?>
                <p
                    style="color: green; font-weight: bold; padding: 10px; background: #e6f4ea; border: 1px solid #b7e1cd; border-radius: 4px;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p
                    style="color: red; font-weight: bold; padding: 10px; background: #fce8e6; border: 1px solid #f5c6cb; border-radius: 4px;">
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php endif; ?>

            <!-- Formulario para Registrar Nuevo Usuario -->
            <div class="seccion">
                <h2>Registrar Nuevo Usuario</h2>
                <form action="../../acciones/admin/gestion_usuarios_proceso.php" method="POST"
                    class="formularioUsuario">
                    <input type="hidden" name="accion" value="crear">

                    <div class="grupoFormulario">
                        <label for="ci">Cédula de Identidad *</label>
                        <input type="text" id="ci" name="ci" placeholder="Ej: 55204710" required>
                    </div>

                    <div class="grupoFormulario">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Marcel" required>
                    </div>

                    <div class="grupoFormulario">
                        <label for="apellido">Apellido *</label>
                        <input type="text" id="apellido" name="apellido" placeholder="Ej: Matiaude" required>
                    </div>

                    <div class="grupoFormulario">
                        <label for="contrasena">Contraseña Inicial *</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña de acceso"
                            required>
                    </div>

                    <div class="grupoFormulario">
                        <label for="id_rol">Rol de Usuario *</label>
                        <select id="id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol</option>
                            <?php foreach ($roles as $r_opt): ?>
                                <option value="<?php echo $r_opt['id_rol']; ?>">
                                    <?php echo htmlspecialchars($r_opt['nombre_rol']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 10px;">
                        <button type="submit" class="btn btn-guardar">Crear Usuario</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de gestion de usuarios -->
            <div class="seccion">
                <h2>Lista de Usuarios Creados</h2>
                <table class="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula (CI)</th>
                            <th>Nombre Completo</th>
                            <th>Rol Actual</th>
                            <th>Estado</th>
                            <th>Cambiar Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_usuarios && $res_usuarios->num_rows > 0): ?>
                            <?php while ($u = $res_usuarios->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $u['id_usuario']; ?></td>
                                    <td><?php echo htmlspecialchars($u['ci']); ?></td>
                                    <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($u['nombre_rol']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($u['activo'] == 1): ?>
                                            <span style="color: #2e7d32; font-weight: bold; background: #e8f5e9; padding: 3px 8px; border-radius: 4px; font-size: 13px;">Activo</span>
                                        <?php else: ?>
                                            <span style="color: #c62828; font-weight: bold; background: #ffebee; padding: 3px 8px; border-radius: 4px; font-size: 13px;">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Formulario rápido para actualizar el rol -->
                                        <form action="../../acciones/admin/gestion_usuarios_proceso.php" method="POST"
                                            style="display: flex; gap: 6px; align-items: center;">
                                            <input type="hidden" name="accion" value="cambiar_rol">
                                            <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">

                                            <select name="id_rol" class="select-rol" required>
                                                <?php foreach ($roles as $r_opt): ?>
                                                    <option value="<?php echo $r_opt['id_rol']; ?>" <?php echo ($r_opt['id_rol'] == $u['id_rol']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($r_opt['nombre_rol']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-guardar" <?php echo ($u['id_usuario'] == $_SESSION['usuario_id']) ? 'onclick="return confirm(\'ATENCIÓN: Si cambias tu rol de Administrador, perderás el acceso a este panel de inmediato. ¿Deseas continuar?\');"' : ''; ?>>Cambiar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if ($u['id_usuario'] != $_SESSION['usuario_id']): ?>
                                            <?php if ($u['activo'] == 1): ?>
                                                <!-- Formulario para desactivar cuenta -->
                                                <form action="../../acciones/admin/gestion_usuarios_proceso.php" method="POST"
                                                    onsubmit="return confirm('¿Está seguro de que desea desactivar la cuenta de <?php echo htmlspecialchars($u['nombre']); ?>?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                                    <button type="submit" class="btn btn-eliminar"
                                                        style="background-color: #fff; color: #d33; border-color: #d33;">Desactivar</button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Formulario para reactivar cuenta -->
                                                <form action="../../acciones/admin/gestion_usuarios_proceso.php" method="POST"
                                                    onsubmit="return confirm('¿Desea reactivar la cuenta de <?php echo htmlspecialchars($u['nombre']); ?>?');">
                                                    <input type="hidden" name="accion" value="reactivar">
                                                    <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                                    <button type="submit" class="btn btn-guardar"
                                                        style="background-color: #2e7d32; color: #fff; border: none;">Reactivar</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: #888; font-size: 12px; font-style: italic;">(Cuenta actual)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay usuarios registrados.</td>
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
