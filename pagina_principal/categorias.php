<?php
include '../conexion_be.php'; // Incluye la conexión a la base de datos

// Realizar la consulta para obtener las categorías
$consulta = "SELECT * FROM categoria";
$preparada = $db->query($consulta); // Ejecutar la consulta con PDO

// Verificar si hay resultados
$categorias = $preparada->fetchAll(PDO::FETCH_ASSOC); // Obtener todas las categorías como un array asociativo

// Retornar las categorías
return $categorias;
?>
