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

$id_ticket  = intval($_POST['id_ticket'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');
$id_usuario = intval($_SESSION['usuario_id']);

if ($id_ticket <= 0 || empty($comentario)) {
    header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&error=" . urlencode("El comentario no puede estar vacío."));
    exit();
}

$comentario_clean = $conn->real_escape_string($comentario);
$sql = "INSERT INTO comentarios (id_ticket, id_usuario, comentario, fecha_creacion)
        VALUES ($id_ticket, $id_usuario, '$comentario_clean', NOW())";

if ($conn->query($sql)) {
    $conn->query("UPDATE tickets SET fecha_actualizacion = NOW() WHERE id_ticket = $id_ticket");
    header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&mensaje=" . urlencode("Comentario agregado correctamente."));
    exit();
} else {
    header("Location: ../../modulos/mesa_ayuda/ver_ticket.php?id=$id_ticket&error=" . urlencode("Error al guardar el comentario: " . $conn->error));
    exit();
}
?>
