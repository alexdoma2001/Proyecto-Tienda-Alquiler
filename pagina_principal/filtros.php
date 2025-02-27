<?php
// Iniciar sesión y conectar la base de datos como siempre
include '../conexion_be.php';
include 'categorias.php';
include 'plataformas.php';

// Comprobar filtros de usuario
$categoria_seleccionada = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$plataforma_seleccionada = isset($_GET['plataformas']) ? intval($_GET['plataformas']) : 0;
$busqueda_nombre = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Paginación
$limite = 8; 
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$inicio = ($pagina_actual - 1) * $limite;

// Consulta principal con filtros
$consulta = "SELECT DISTINCT v.* FROM videojuegos v
        INNER JOIN videojuegos_categoria vc ON v.ID = vc.videojuego_id
        INNER JOIN videojuegos_plataforma vp ON v.ID = vp.videojuego_id
        WHERE 1 = 1";

// Aplicar filtros de categoría y plataforma
if ($categoria_seleccionada > 0) {
    $consulta .= " AND vc.categoria_id = :categoria_id";
}
if ($plataforma_seleccionada > 0) {
    $consulta .= " AND vp.plataforma_id = :plataformas";
}
if (!empty($busqueda_nombre)) {
    $consulta .= " AND v.nombre LIKE :busqueda_nombre";
}

// Configurar límites de paginación
$consulta .= " LIMIT :inicio, :limite";

$preparada = $db->prepare($consulta);

// Bindear los parámetros
if ($categoria_seleccionada > 0) {
    $preparada->bindParam(':categoria_id', $categoria_seleccionada, PDO::PARAM_INT);
}
if ($plataforma_seleccionada > 0) {
    $preparada->bindParam(':plataformas', $plataforma_seleccionada, PDO::PARAM_INT);
}
if (!empty($busqueda_nombre)) {
    $param_busqueda = '%' . $busqueda_nombre . '%';
    $preparada->bindParam(':busqueda_nombre', $param_busqueda, PDO::PARAM_STR);
}

// Bindear paginación
$preparada->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$preparada->bindParam(':limite', $limite, PDO::PARAM_INT);

// Ejecutar la consulta y obtener resultados
$preparada->execute();
$videojuegos = $preparada->fetchAll(PDO::FETCH_ASSOC);

// Total de videojuegos para paginación
$consulta_count = "SELECT COUNT(DISTINCT v.ID) as total FROM videojuegos v
              INNER JOIN videojuegos_categoria vc ON v.ID = vc.videojuego_id
              INNER JOIN videojuegos_plataforma vp ON v.ID = vp.videojuego_id
              WHERE 1 = 1";

// Aplicar filtros a la consulta de conteo total
if ($categoria_seleccionada > 0) {
    $consulta_count .= " AND vc.categoria_id = :categoria_id";
}
if ($plataforma_seleccionada > 0) {
    $consulta_count .= " AND vp.plataforma_id = :plataforma_id";
}
if (!empty($busqueda_nombre)) {
    $consulta_count .= " AND v.nombre LIKE :busqueda_nombre";
}

$preparada_count = $db->prepare($consulta_count);

// Bindear los mismos parámetros
if ($categoria_seleccionada > 0) {
    $preparada_count->bindParam(':categoria_id', $categoria_seleccionada, PDO::PARAM_INT);
}
if ($plataforma_seleccionada > 0) {
    $preparada_count->bindParam(':plataforma_id', $plataforma_seleccionada, PDO::PARAM_INT);
}
if (!empty($busqueda_nombre)) {
    $preparada_count->bindParam(':busqueda_nombre', $param_busqueda, PDO::PARAM_STR);
}

$preparada_count->execute();
$total_videojuegos = $preparada_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_videojuegos / $limite);

?>
