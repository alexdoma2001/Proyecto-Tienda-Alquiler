<?php
session_start();
include '../conexion_be.php';

// Verifica que el usuario está logueado
if (!isset($_SESSION['cliente_id'])) {
    $_SESSION['mensaje'] = [
        'texto' => "<p style= 'margin-top: 15px;'>Debe iniciar sesión para añadir artículos al carrito.</p>
                    <a href='../inicio_sesion/inicio_sesion.php' style='padding: 8px 16px; margin-left: 10px; align-self: center; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 4px;'>Iniciar sesión</a>",
        'clase' => 'alert-danger' // Clase de alerta para mensajes de error
    ];
    header("Location: alquiler_info.php?videojuego_id=" . $_POST['videojuego_id']);
    exit;
}

// Obtener los datos enviados mediante POST
$videojuego_id = isset($_POST['videojuego_id']) ? intval($_POST['videojuego_id']) : 0;
$plataforma = isset($_POST['plataforma']) ? $_POST['plataforma'] : '';

// Verificar que el ID del videojuego y la plataforma no están vacíos
if ($videojuego_id == 0 || empty($plataforma)) {
    $_SESSION['mensaje'] = "Error: datos incompletos.";
    header("Location: alquiler_info.php?videojuego_id=" . $videojuego_id);
    exit;
}

// Consulta para obtener el precio del videojuego en la plataforma seleccionada
$consulta_precio = "SELECT vp.precio FROM videojuegos_plataforma vp
               INNER JOIN plataforma p ON vp.plataforma_id = p.ID
               WHERE vp.videojuego_id = :videojuego_id AND p.nombre = :plataforma";
$preparada = $db->prepare($consulta_precio);
$preparada->bindParam(':videojuego_id', $videojuego_id, PDO::PARAM_INT);
$preparada->bindParam(':plataforma', $plataforma, PDO::PARAM_STR);
$preparada->execute();
$resultado_precio = $preparada->fetch(PDO::FETCH_ASSOC);

if ($resultado_precio) {
    // Inicializar el carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Inicializar cantidad en 1 al agregar al carrito
    $cantidad = 1;

    // Verificar si el producto ya existe en el carrito
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['videojuego_id'] == $videojuego_id && $item['plataforma'] == $plataforma) {
            // Si el producto ya existe y es de la misma plataforma, solo incrementamos su cantidad
            $item['unidades'] += $cantidad;
            $encontrado = true;
            break;
        }
    }

    // Si no se encontró, añadir como nuevo artículo en el carrito
    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'videojuego_id' => $videojuego_id,
            'plataforma' => $plataforma,
            'precio' => $resultado_precio['precio'],
            'unidades' => $cantidad, // Empieza con 1 unidad
        ];
    }

    // Mensaje de éxito
    $_SESSION['mensaje'] = [
        'texto' => "El videojuego se ha añadido correctamente al carrito.",
        'clase' => 'alert-success' // Clase de alerta para mensajes de éxito
    ];
} else {
    $_SESSION['mensaje'] = [
        'texto' => "El videojuego no se ha podido añadir.",
        'clase' => 'alert-danger' // Clase de alerta para mensajes de éxito
    ];
}

// Redirigir de vuelta a la página de alquiler_info.php
header("Location: alquiler_info.php?videojuego_id=" . $videojuego_id);
exit;
?>