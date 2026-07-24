<?php
require_once '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_marca = isset($_POST['id_marca']) ? intval($_POST['id_marca']) : 0;
    $nombre_modelo = isset($_POST['nombre_modelo']) ? trim($_POST['nombre_modelo']) : '';

    // Validar que se haya seleccionado una marca, que no esté vacío y contenga letras o números
    if (!empty($id_marca) && !empty($nombre_modelo) && preg_match('/[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]/', $nombre_modelo)) {
        $nombre_clean = $conn->real_escape_string($nombre_modelo);

        $sql = "INSERT INTO modelos (nombre_modelo, id_marca) 
                VALUES ('$nombre_clean', $id_marca)";

        if ($conn->query($sql)) {
            header("Location: inventario.php");
            exit();
        } else {
            header("Location: agregar_modelo.php?error=" . urlencode("Error al guardar el modelo: " . $conn->error));
            exit();
        }
    } else {
        header("Location: agregar_modelo.php?error=" . urlencode("El nombre del modelo debe contener al menos letras o números válidos."));
        exit();
    }
} else {
    header("Location: agregar_modelo.php");
    exit();
}
?>
