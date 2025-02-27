<?php
// Iniciar la sesión
session_start();
require '../../conexion_be.php'; 

// Evitar el almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y validar los datos de entrada
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $cp = $_POST['codigo_Postal'] ?? '';

    // Validar que los campos no estén vacíos
    if (!empty($nombre) && !empty($direccion)&& !empty($ciudad)) {
        // Preparar la consulta para insertar en la base de datos
        $consulta = "INSERT INTO punto_recogida (nombre, direccion, ciudad, codigo_Postal) VALUES (:nombre, :direccion, :ciudad, :codigo_Postal)";
        $preparada = $db->prepare($consulta);
        $preparada->bindParam(':nombre', $nombre);
        $preparada->bindParam(':direccion', $direccion);
        $preparada->bindParam(':ciudad', $ciudad);
        $preparada->bindParam(':codigo_Postal', $cp);

        // Ejecutar la consulta y verificar el resultado
        if ( $preparada->execute()) {
            // Redirigir después de agregar el punto
            header('Location: gestion_puntos_recogida.php');
            exit();
        } else {
            echo "Error: No se pudo agregar el punto de recogida.";
        }
    } else {
        echo "<div class='alert alert-danger'>Por favor, completa todos los campos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Punto de Recogida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <h1>Agregar Nuevo Punto de Recogida</h1>

    <!-- Botón para volver a gestionar puntos de recogida -->
    <div class="mb-3">
        <a href="gestion_puntos_recogida.php" class="btn btn-secondary">Volver a Gestión Puntos de Recogida</a>
    </div>

    <!-- Formulario para agregar un nuevo punto de recogida -->
    <form method="POST" action="">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Direccion</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required>
        </div>
        <div class="mb-3">
            <label for="ciudad" class="form-label">Ciudad</label>
            <input type="text" class="form-control" id="ciudad" name="ciudad" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Codigo Postal</label>
            <input type="number" class="form-control" id="codigo_Postal" name="codigo_Postal" required>
        </div>
        <button type="submit" class="btn btn-primary">Agregar Punto de Recogida</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
