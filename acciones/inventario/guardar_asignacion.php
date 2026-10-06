<?php
session_start();
require_once '../../config/conexion.php';

// Redirección con mensaje de error o éxito
function volver($error = null, $exito = null)
{
    $param = $error ? 'error=' . urlencode($error) : 'mensaje=' . urlencode($exito);
    header("Location: ../../modulos/inventario/asignaciones.php?$param");
    exit();
}

// 1. Control de sesión y permisos
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

if (intval($_SESSION['usuario_rol'] ?? 0) === 3) {
    volver("No tenés permisos para realizar esta acción.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../modulos/inventario/asignaciones.php");
    exit();
}

// 2. Parámetros del formulario
$accion = trim($_POST['accion'] ?? '');
$id_equipo = intval($_POST['id_equipo'] ?? 0);
$id_usuario = intval($_POST['id_usuario'] ?? 0);
$id_asig = intval($_POST['id_asignacion'] ?? 0);

// Normalizar formato de fecha (adaptar datetime-local a MySQL)
$fecha = trim($_POST['fecha_inicio'] ?? date('Y-m-d H:i:s'));
$fecha = str_replace('T', ' ', $fecha);
if (strlen($fecha) === 10) {
    $fecha .= ' ' . date('H:i:s');
} elseif (strlen($fecha) === 16) {
    $fecha .= ':00';
}
$fecha_clean = $conn->real_escape_string($fecha);

// ==========================================
// CASO 1: ASIGNAR EQUIPO A UN USUARIO
// ==========================================
if ($accion === 'asignar') {
    if ($id_equipo <= 0 || $id_usuario <= 0) {
        volver("Debe seleccionar un equipo y un usuario.");
    }

    // Verificar usuario activo y equipo disponible
    $user_check = $conn->query("SELECT 1 FROM usuarios WHERE id_usuario = $id_usuario AND activo = 1");
    if (!$user_check || $user_check->num_rows === 0) {
        volver("El usuario seleccionado no está activo en el sistema.");
    }

    $eq_check = $conn->query("SELECT 1 FROM equipos WHERE id_equipo = $id_equipo AND estado = 'Disponible'");
    if (!$eq_check || $eq_check->num_rows === 0) {
        volver("El equipo seleccionado no está disponible (puede estar En Uso, En Mantenimiento o De Baja).");
    }

    try {
        $conn->begin_transaction();

        // Cerrar asignación anterior si hubiera quedado abierta
        $conn->query("UPDATE asignaciones SET fecha_fin = NOW() WHERE id_equipo = $id_equipo AND (fecha_fin IS NULL OR fecha_fin = '0000-00-00')");

        // Registrar la nueva asignación
        $conn->query("INSERT INTO asignaciones (id_equipo, id_usuario, fecha_inicio) VALUES ($id_equipo, $id_usuario, '$fecha_clean')");

        // Cambiar estado del equipo a 'En Uso'
        $conn->query("UPDATE equipos SET estado = 'En Uso' WHERE id_equipo = $id_equipo");

        $conn->commit();
        volver(null, "Asignación registrada correctamente.");

    } catch (Exception $e) {
        $conn->rollback();
        volver("Error al registrar la asignación: " . $conn->error);
    }
}

// ==========================================
// CASO 2: FINALIZAR ASIGNACIÓN
// ==========================================
if ($accion === 'finalizar') {
    if ($id_asig <= 0 && $id_equipo <= 0) {
        volver("No se especificó la asignación a finalizar.");
    }

    // Si no tenemos el id_equipo pero sí el id_asignacion, lo averiguamos para poder liberarlo
    if ($id_equipo <= 0 && $id_asig > 0) {
        $res_asig = $conn->query("SELECT id_equipo FROM asignaciones WHERE id_asignacion = $id_asig LIMIT 1");
        if ($res_asig && $row = $res_asig->fetch_assoc()) {
            $id_equipo = intval($row['id_equipo']);
        }
    }

    $where = $id_asig > 0 ? "id_asignacion = $id_asig" : "id_equipo = $id_equipo AND (fecha_fin IS NULL OR fecha_fin = '0000-00-00')";

    try {
        $conn->begin_transaction();

        // 1. Marcar fecha de fin
        $conn->query("UPDATE asignaciones SET fecha_fin = NOW() WHERE $where");

        // 2. Liberar el equipo a 'Disponible'
        if ($id_equipo > 0) {
            $conn->query("UPDATE equipos SET estado = 'Disponible' WHERE id_equipo = $id_equipo");
        }

        $conn->commit();
        volver(null, "Asignación finalizada correctamente.");

    } catch (Exception $e) {
        $conn->rollback();
        volver("Error al finalizar la asignación: " . $conn->error);
    }
}

// Redirección por defecto si no coincide ninguna acción
header("Location: ../../modulos/inventario/asignaciones.php");
exit();