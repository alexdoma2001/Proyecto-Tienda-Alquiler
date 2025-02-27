<?php
// Iniciar la sesión
session_start();
require '../../conexion_be.php';


// Verificar si se ha proporcionado un ID de punto de recogida
if (isset($_GET['ID'])) {
    $punto_id = $_GET['ID'];

    // Consulta para eliminar el punto de recogida
    $consulta = "DELETE FROM punto_recogida WHERE ID = :id";
    $preparada = $db->prepare($consulta);
    $preparada->bindParam(':id', $punto_id, PDO::PARAM_INT);

    // Ejecutar la eliminación y verificar
    if ($preparada->execute()) {
        // Redirigir después de la eliminación
        header('Location: gestion_puntos_recogida.php');
        exit();
    } else {
        echo "Error: No se pudo eliminar el punto de recogida.";
    }
} else {
    echo "<div class='alert alert-danger'>ID de punto de recogida no especificado.</div>";
    exit();
}
?>
