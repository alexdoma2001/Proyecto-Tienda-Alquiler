<?php
// Iniciar la sesión y conectar a la base de datos
session_start();
require '../../conexion_be.php';

// Verificar si los datos fueron enviados
if (isset($_GET['ID'], $_GET['nombre'], $_GET['direccion'], $_GET['ciudad'], $_GET['codigo_postal'])) {
    $id = $_GET['ID'];
    $nombre = $_GET['nombre'];
    $direccion = $_GET['direccion'];
    $ciudad = $_GET['ciudad'];
    $codigo_postal = $_GET['codigo_postal'];

    // Actualizar el punto de recogida
    $consulta = "UPDATE punto_recogida SET nombre = :nombre, direccion = :direccion, ciudad = :ciudad, codigo_Postal = :codigo_postal WHERE ID = :id";
    $preparada = $db->prepare($consulta);
    $preparada->bindParam(':nombre', $nombre);
    $preparada->bindParam(':direccion', $direccion);
    $preparada->bindParam(':ciudad', $ciudad);
    $preparada->bindParam(':codigo_postal', $codigo_postal);
    $preparada->bindParam(':id', $id, PDO::PARAM_INT);

    if ($preparada->execute()) {
        header('Location: gestion_puntos_recogida.php');
        exit();
    } else {
        echo "Error: No se pudo actualizar el punto de recogida.";
    }
} else {
    echo "Error: Datos incompletos para actualizar el punto de recogida.";
    exit();
}
?>
