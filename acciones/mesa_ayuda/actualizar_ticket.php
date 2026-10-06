<?php
session_start();
require_once '../../config/conexion.php';

// 1. Control de sesión y método HTTP
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php");
    exit();
}

// 2. Parámetros principales
$accion     = trim($_POST['accion'] ?? '');
$id_ticket  = intval($_POST['id_ticket'] ?? 0);
$id_usuario = intval($_SESSION['usuario_id']);
$rol        = intval($_SESSION['usuario_rol'] ?? 0);
$origen     = trim($_POST['origen'] ?? '');
$param_orig = !empty($origen) ? '&origen=' . urlencode($origen) : '';

// Helper de redirección para no repetir código
function volver($error = null, $exito = null) {
    global $id_ticket, $param_orig;
    if ($id_ticket <= 0) {
        $param = $error ? '?error=' . urlencode($error) : ($exito ? '?mensaje=' . urlencode($exito) : '');
        header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php$param");
    } else {
        $param = $error ? '&error=' . urlencode($error) : '&mensaje=' . urlencode($exito);
        header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket" . $param_orig . $param);
    }
    exit();
}

// 3. Validar ticket y permisos (solo técnicos y administradores)
if ($id_ticket <= 0) {
    volver("Ticket inválido.");
}

if ($rol === 3) {
    volver("No tenés permisos para realizar esta acción.");
}

// ==========================================
// CASO 1: ASIGNARSE EL TICKET
// ==========================================
if ($accion === 'asignarme') {
    $sql = "UPDATE tickets SET id_tecnico = $id_usuario, estado = 'En Proceso' WHERE id_ticket = $id_ticket";
    if ($conn->query($sql)) {
        volver(null, "Te asignaste el ticket correctamente.");
    } else {
        volver("Error al asignarse el ticket: " . $conn->error);
    }
}

// ==========================================
// CASO 2: ACTUALIZAR ESTADO Y DIAGNÓSTICO
// ==========================================
if ($accion === 'actualizar_estado') {
    $nuevo_estado    = trim($_POST['estado'] ?? '');
    $diagnostico_txt = trim($_POST['diagnostico'] ?? '');
    $solucion_txt    = trim($_POST['solucion_aplicada'] ?? '');
    $id_equipo_diag  = intval($_POST['id_equipo_diag'] ?? 0);

    $estados_validos = ['Pendiente', 'En Proceso', 'Resuelto'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        volver("Estado no válido.");
    }

    try {
        $conn->begin_transaction();

        // 1. Actualizar estado y asignar técnico al ticket
        $estado_clean = $conn->real_escape_string($nuevo_estado);
        $sql_ticket = "UPDATE tickets SET estado = '$estado_clean', id_tecnico = $id_usuario WHERE id_ticket = $id_ticket";
        if (!$conn->query($sql_ticket)) {
            throw new Exception("Error al actualizar el estado: " . $conn->error);
        }

        // 2. Si se ingresó diagnóstico, guardarlo en la misma transacción
        if (!empty($diagnostico_txt)) {
            // Si no se eligió equipo en el formulario, tomar el equipo del ticket
            if ($id_equipo_diag <= 0) {
                $res_eq = $conn->query("SELECT id_equipo FROM tickets WHERE id_ticket = $id_ticket");
                if ($res_eq && $row = $res_eq->fetch_assoc()) {
                    $id_equipo_diag = intval($row['id_equipo'] ?? 0);
                }
            }

            $equipo_valor   = ($id_equipo_diag > 0) ? $id_equipo_diag : 'NULL';
            $diag_clean     = $conn->real_escape_string($diagnostico_txt);
            $solucion_clean = $conn->real_escape_string($solucion_txt);

            $sql_diag = "INSERT INTO diagnosticos (id_ticket, id_equipo, id_tecnico, diagnostico, solucion_aplicada, fecha_intervencion)
                         VALUES ($id_ticket, $equipo_valor, $id_usuario, '$diag_clean', '$solucion_clean', NOW())";

            if (!$conn->query($sql_diag)) {
                throw new Exception("Error al guardar el diagnóstico: " . $conn->error);
            }
        }

        // Todo correcto: confirmamos ambas operaciones
        $conn->commit();
        volver(null, "Ticket actualizado correctamente.");

    } catch (Exception $e) {
        // Si falló el diagnóstico o el ticket, revertimos para no perder consistencia
        $conn->rollback();
        volver($e->getMessage());
    }
}

// Redirección si la acción no coincide
volver("Acción no válida.");
