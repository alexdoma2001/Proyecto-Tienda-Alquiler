<?php
include '../conexion_be.php';

// Realizar la consulta para obtener las plataformas
$consulta = "SELECT * FROM plataforma";
$preparada = $db->query($consulta); 

$plataformas = $preparada->fetchAll(PDO::FETCH_ASSOC);

return $plataformas;
?>
