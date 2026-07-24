<?php
require_once '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $numero_serie = isset($_POST['numero_serie']) ? trim($_POST['numero_serie']) : '';
    $id_marca = isset($_POST['id_marca']) ? intval($_POST['id_marca']) : 0;
    $id_modelo = isset($_POST['id_modelo']) ? intval($_POST['id_modelo']) : 0;
    $id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    if (!empty($nombre) && !empty($id_marca) && !empty($id_modelo) && !empty($id_categoria) && !empty($estado)) {
        $nombre_clean = $conn->real_escape_string($nombre);
        $num_serie_clean = $conn->real_escape_string($numero_serie);
        $estado_clean = $conn->real_escape_string($estado);

        $sql = "INSERT INTO equipos (nombre, numero_serie, id_marca, id_modelo, id_categoria, estado) 
                VALUES ('$nombre_clean', '$num_serie_clean', $id_marca, $id_modelo, $id_categoria, '$estado_clean')";

        if ($conn->query($sql)) {
            header("Location: inventario.php");
            exit();
        } else {
            header("Location: agregar_equipo.php?error=" . urlencode("Error al guardar el equipo: " . $conn->error));
            exit();
        }
    } else {
        header("Location: agregar_equipo.php?error=" . urlencode("Por favor complete todos los campos obligatorios."));
        exit();
    }
} else {
    header("Location: agregar_equipo.php");
    exit();
}
?>
