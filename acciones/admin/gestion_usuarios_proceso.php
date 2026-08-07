<?php
session_start();
require_once '../../config/conexion.php';

// Verificar que sea Administrador (id_rol = 1)
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
    header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Acceso no autorizado. Solo los administradores pueden gestionar usuarios."));
    exit();
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion === 'crear') {
    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $id_rol = intval($_POST['id_rol'] ?? 0);

    if (!empty($ci) && !empty($nombre) && !empty($apellido) && !empty($contrasena) && $id_rol > 0) {
        $ci_clean = $conn->real_escape_string($ci);
        $nombre_clean = $conn->real_escape_string($nombre);
        $apellido_clean = $conn->real_escape_string($apellido);
        $contrasena_encriptada = md5($conn->real_escape_string($contrasena));

        $sql = "INSERT INTO usuarios (ci, nombre, apellido, contrasena, id_rol) 
                VALUES ('$ci_clean', '$nombre_clean', '$apellido_clean', '$contrasena_encriptada', $id_rol)";

        if ($conn->query($sql)) {
            header("Location: ../../modulos/admin/administrar_usuarios.php?mensaje=" . urlencode("Usuario creado con éxito."));
            exit();
        } else {
            header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Error al crear usuario (verifique si la cédula ya existe)."));
            exit();
        }
    } else {
        header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Por favor complete todos los campos obligatorios."));
        exit();
    }
} elseif ($accion === 'cambiar_rol') {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $id_rol = intval($_POST['id_rol'] ?? 0);

    if ($id_usuario > 0 && $id_rol > 0) {
        $sql = "UPDATE usuarios SET id_rol = $id_rol WHERE id_usuario = $id_usuario";
        if ($conn->query($sql)) {
            // Si el usuario modificado es la persona conectada actualmente, actualizar la sesión de inmediato
            if ($id_usuario == $_SESSION['usuario_id']) {
                $_SESSION['usuario_rol'] = $id_rol;

                $res_r = $conn->query("SELECT nombre_rol FROM roles WHERE id_rol = $id_rol");
                if ($res_r && $row_r = $res_r->fetch_assoc()) {
                    $_SESSION['nombre_rol'] = $row_r['nombre_rol'];
                }
            }

            header("Location: ../../modulos/admin/administrar_usuarios.php?mensaje=" . urlencode("Rol actualizado correctamente."));
            exit();
        } else {
            header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Error al actualizar el rol del usuario: " . $conn->error));
            exit();
        }
    } else {
        header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Datos no válidos para cambiar de rol."));
        exit();
    }
} elseif ($accion === 'eliminar') {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);

    // Evitar que el administrador se elimine a sí mismo
    if ($id_usuario > 0 && $id_usuario != $_SESSION['usuario_id']) {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = $id_usuario";
        if ($conn->query($sql)) {
            header("Location: ../../modulos/admin/administrar_usuarios.php?mensaje=" . urlencode("Usuario desactivado correctamente."));
            exit();
        } else {
            header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Error al desactivar el usuario: " . $conn->error));
            exit();
        }
    } else {
        header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("No puedes desactivar tu propia cuenta en sesión."));
        exit();
    }
} elseif ($accion === 'reactivar') {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);

    if ($id_usuario > 0) {
        $sql = "UPDATE usuarios SET activo = 1 WHERE id_usuario = $id_usuario";
        if ($conn->query($sql)) {
            header("Location: ../../modulos/admin/administrar_usuarios.php?mensaje=" . urlencode("Usuario reactivado correctamente."));
            exit();
        } else {
            header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Error al reactivar el usuario: " . $conn->error));
            exit();
        }
    } else {
        header("Location: ../../modulos/admin/administrar_usuarios.php?error=" . urlencode("Usuario no válido para reactivar."));
        exit();
    }
} else {
    header("Location: ../../modulos/admin/administrar_usuarios.php");
    exit();
}
?>