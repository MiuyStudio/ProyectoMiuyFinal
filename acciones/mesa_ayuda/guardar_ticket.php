<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../modulos/mesa_ayuda/nuevo_ticket.php");
    exit();
}

$titulo       = trim($_POST['titulo'] ?? '');
$tipo_ticket  = trim($_POST['tipo_ticket'] ?? '');
$id_categoria = intval($_POST['id_categoria'] ?? 0);
$prioridad    = trim($_POST['prioridad'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$id_equipo    = intval($_POST['id_equipo'] ?? 0);
$id_solicitante = intval($_SESSION['usuario_id']);

// Validar campos obligatorios
if (empty($titulo) || empty($tipo_ticket) || $id_categoria <= 0 || empty($prioridad) || empty($descripcion)) {
    header("Location: ../../modulos/mesa_ayuda/nuevo_ticket.php?error=" . urlencode("Por favor complete todos los campos obligatorios."));
    exit();
}

$titulo_clean      = $conn->real_escape_string($titulo);
$tipo_clean        = $conn->real_escape_string($tipo_ticket);
$prioridad_clean   = $conn->real_escape_string($prioridad);
$descripcion_clean = $conn->real_escape_string($descripcion);

// id_equipo es opcional (NULL si no se seleccionó)
$equipo_valor = ($id_equipo > 0) ? $id_equipo : 'NULL';

$sql = "INSERT INTO tickets (titulo, descripcion, tipo_ticket, prioridad, estado, id_solicitante, id_categoria, id_equipo)
        VALUES ('$titulo_clean', '$descripcion_clean', '$tipo_clean', '$prioridad_clean', 'Pendiente', $id_solicitante, $id_categoria, $equipo_valor)";

if ($conn->query($sql)) {
    header("Location: ../../modulos/mesa_ayuda/mesa_ayuda.php?mensaje=" . urlencode("Ticket creado correctamente."));
    exit();
} else {
    header("Location: ../../modulos/mesa_ayuda/nuevo_ticket.php?error=" . urlencode("Error al crear el ticket: " . $conn->error));
    exit();
}
?>
