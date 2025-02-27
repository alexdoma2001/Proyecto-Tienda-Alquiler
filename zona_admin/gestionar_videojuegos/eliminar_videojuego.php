<?php
session_start();
include '../../conexion_be.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

// Obtener el ID del videojuego de la URL
$videojuegoId = $_GET['id'] ?? null;

if ($videojuegoId) {
    try {
        // Iniciar transacción
        $db->beginTransaction();

        // 1. Obtener el nombre del videojuego (suponiendo que el campo es "nombre")
        $consultaNombre = $db->prepare("SELECT nombre FROM videojuegos WHERE ID = :videojuego_id");
        $consultaNombre->bindParam(':videojuego_id', $videojuegoId);
        $consultaNombre->execute();
        $videojuego = $consultaNombre->fetch(PDO::FETCH_ASSOC);

        // 2. Construir la ruta de la imagen usando el título del videojuego
        if ($videojuego && !empty($videojuego['nombre'])) {
            $titulo = $videojuego['nombre'];
            $rutaImagenJPG = "../../imagenes/" . $titulo . ".jpg";
            $rutaImagenPNG = "../../imagenes/" . $titulo . ".png";
            $rutaImagenJPEG = "../../imagenes/" . $titulo . ".jpeg";
            
            // 3. Eliminar la imagen si existe en formato JPG o PNG
            if (file_exists($rutaImagenJPG)) {
                unlink($rutaImagenJPG);
            } elseif (file_exists($rutaImagenPNG)) {
                unlink($rutaImagenPNG);
            } elseif (file_exists($rutaImagenJPEG)) {
                unlink($rutaImagenJPEG);
            }
        }

        // 4. Eliminar las relaciones del videojuego con categorías
        $consultaCategorias = $db->prepare("DELETE FROM videojuegos_categoria WHERE videojuego_id = :videojuego_id");
        $consultaCategorias->bindParam(':videojuego_id', $videojuegoId);
        $consultaCategorias->execute();

        // 5. Eliminar las relaciones del videojuego con plataformas
        $consultaPlataformas = $db->prepare("DELETE FROM videojuegos_plataforma WHERE videojuego_id = :videojuego_id");
        $consultaPlataformas->bindParam(':videojuego_id', $videojuegoId);
        $consultaPlataformas->execute();

        // 6. Eliminar el videojuego en sí
        $consultaVideojuego = $db->prepare("DELETE FROM videojuegos WHERE ID = :videojuego_id");
        $consultaVideojuego->bindParam(':videojuego_id', $videojuegoId);
        $consultaVideojuego->execute();

        // Confirmar transacción
        $db->commit();
        header("Location: gestion_videojuegos.php");
        exit();
    } catch (PDOException $e) {
        $db->rollBack();
        echo "<div class='alert alert-danger'>Error al eliminar el videojuego: " . $e->getMessage() . "</div>";
    }
}
?>
