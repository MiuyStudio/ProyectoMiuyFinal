<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php");
    exit();
}

$accion     = trim($_POST['accion'] ?? '');
$id_ticket  = intval($_POST['id_ticket'] ?? 0);
$id_usuario = intval($_SESSION['usuario_id']);
$rol        = intval($_SESSION['usuario_rol']);

if ($id_ticket <= 0) {
    header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php");
    exit();
}

// Solo técnicos y admins pueden actualizar tickets
if ($rol == 3) {
    header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php?error=" . urlencode("No tenés permisos para realizar esta acción."));
    exit();
}

if ($accion === 'asignarme') {
    // Asignarse el ticket y ponerlo En Proceso
    $sql = "UPDATE tickets SET id_tecnico = $id_usuario, estado = 'En Proceso' WHERE id_ticket = $id_ticket";
    if ($conn->query($sql)) {
        header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&mensaje=" . urlencode("Te asignaste el ticket correctamente."));
    } else {
        header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&error=" . urlencode("Error al asignarse el ticket."));
    }
    exit();

} elseif ($accion === 'actualizar_estado') {
    $nuevo_estado     = trim($_POST['estado'] ?? '');
    $diagnostico_txt  = trim($_POST['diagnostico'] ?? '');
    $solucion_txt     = trim($_POST['solucion_aplicada'] ?? '');
    $id_equipo_diag   = intval($_POST['id_equipo_diag'] ?? 0);

    $estados_validos = ['Pendiente', 'En Proceso', 'Resuelto'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&error=" . urlencode("Estado no válido."));
        exit();
    }

    $estado_clean = $conn->real_escape_string($nuevo_estado);
    $sql = "UPDATE tickets SET estado = '$estado_clean', id_tecnico = $id_usuario WHERE id_ticket = $id_ticket";

    if (!$conn->query($sql)) {
        header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&error=" . urlencode("Error al actualizar el estado."));
        exit();
    }

    // Si ingresó diagnóstico, registrarlo
    if (!empty($diagnostico_txt)) {
        $diag_clean     = $conn->real_escape_string($diagnostico_txt);
        $solucion_clean = $conn->real_escape_string($solucion_txt);

        // Si no seleccionó equipo en el form, usar el equipo del ticket (si tiene)
        if ($id_equipo_diag <= 0) {
            $res_tick_eq = $conn->query("SELECT id_equipo FROM tickets WHERE id_ticket = $id_ticket");
            if ($res_tick_eq && $row_eq = $res_tick_eq->fetch_assoc()) {
                $id_equipo_diag = intval($row_eq['id_equipo'] ?? 0);
            }
        }

        $equipo_valor = ($id_equipo_diag > 0) ? $id_equipo_diag : 'NULL';

        $sql_diag = "INSERT INTO diagnosticos (id_ticket, id_equipo, id_tecnico, diagnostico, solucion_aplicada, fecha_intervencion)
                     VALUES ($id_ticket, $equipo_valor, $id_usuario, '$diag_clean', '$solucion_clean', NOW())";

        if (!$conn->query($sql_diag)) {
            header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&error=" . urlencode("Ticket actualizado pero error al guardar diagnóstico: " . $conn->error));
            exit();
        }
    }

    header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&mensaje=" . urlencode("Ticket actualizado correctamente."));
    exit();

} else {
    header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php");
    exit();
}
?>
