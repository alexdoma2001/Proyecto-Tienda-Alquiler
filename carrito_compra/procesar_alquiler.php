<?php
session_start();
include '../conexion_be.php';
include 'enviar_correo_confirmacion_alquiler.php';

// Verificar si existen todos los datos necesarios en la sesión
if (!isset($_SESSION['cliente_id'], $_SESSION['carrito'], $_SESSION['punto_recogida'], $_SESSION['dias_alquiler'])) {
    header("Location: ../pagina_principal/index.php");
    exit();
}

// Variables necesarias
$cliente_id = $_SESSION['cliente_id'];
$email = $_SESSION['correo'];
$nombreCliente = $_SESSION['nombre'];
$punto_recogida_id = $_SESSION['punto_recogida'];
$dias_alquiler = $_SESSION['dias_alquiler'];
$carrito = $_SESSION['carrito'];

$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;

unset($_SESSION['error_message']);


try {
    // Comenzar transacción
    $db->beginTransaction();

    // Insertar el pedido en la tabla `alquiler`
    $consulta = $db->prepare("
        INSERT INTO alquiler (cliente_id, punto_recogida, dias_alquiler, estado, fecha_inicio, referencia_recogida)
        VALUES (:cliente_id, :punto_recogida_id, :dias_alquiler, 1, NOW(), :referencia_recogida)
    ");

    // Generar referencia de recogida aleatoria
    $referencia_recogida = strtoupper(bin2hex(random_bytes(3))); 

    $consulta->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $consulta->bindParam(':punto_recogida_id', $punto_recogida_id, PDO::PARAM_INT);
    $consulta->bindParam(':dias_alquiler', $dias_alquiler, PDO::PARAM_INT);
    $consulta->bindParam(':referencia_recogida', $referencia_recogida, PDO::PARAM_STR);
    $consulta->execute();

    $alquiler_id = $db->lastInsertId();

    //Insert en la tabla alquiler_videojuegos_plataforma
    $consulta_detalle = $db->prepare("
        INSERT INTO alquiler_videojuegos_plataforma (alquiler_ID, videojuego_plataforma_ID, unidades)
        VALUES (:alquiler_id, :videojuego_plataforma_id, :unidades)
    ");

    $consulta_punto_recogida = $db->prepare("SELECT nombre FROM punto_recogida WHERE id = :punto_Recogida");
    $consulta_punto_recogida->bindParam(':punto_Recogida', $punto_recogida_id, PDO::PARAM_INT);
    $consulta_punto_recogida->execute();
    $punto_recogida_data = $consulta_punto_recogida->fetch(PDO::FETCH_ASSOC);

    if ($punto_recogida_data) {
        $punto_recogida = $punto_recogida_data['nombre'];
    } else {
        throw new Exception("No se pudo encontrar el nombre del punto de recogida.");
    }

    // Recorrer los artículos del carrito e insertar los detalles
    foreach ($carrito as $index => &$item) { // Usa referencia (&) para modificar directamente el carrito
        $consulta_nombre_videojuego = $db->prepare("SELECT v.nombre FROM videojuegos v WHERE v.id = :videojuego_id");
        $consulta_nombre_videojuego->bindParam(':videojuego_id', $item['videojuego_id'], PDO::PARAM_INT);
        $consulta_nombre_videojuego->execute();
        $videojuego = $consulta_nombre_videojuego->fetch(PDO::FETCH_ASSOC);

        if ($videojuego) {
            $item['nombre'] = $videojuego['nombre']; 
        } else {
            throw new Exception("No se pudo encontrar el nombre del videojuego con ID " . $item['videojuego_id']);
        }

        // Obtener el ID de `videojuegos_plataforma`
        $consulta_videojuego_plataforma = $db->prepare("
            SELECT vp.id, vp.unidades
            FROM videojuegos_plataforma vp
            WHERE vp.videojuego_id = :videojuego_id AND vp.plataforma_id = (
                SELECT p.id FROM plataforma p WHERE p.nombre = :plataforma_nombre
            )
        ");
        $consulta_videojuego_plataforma->bindParam(':videojuego_id', $item['videojuego_id'], PDO::PARAM_INT);
        $consulta_videojuego_plataforma->bindParam(':plataforma_nombre', $item['plataforma'], PDO::PARAM_STR);
        $consulta_videojuego_plataforma->execute();
        $videojuego_plataforma = $consulta_videojuego_plataforma->fetch(PDO::FETCH_ASSOC);

        if (!$videojuego_plataforma) {
            throw new Exception("No se encontró un registro en `videojuegos_plataforma` para el videojuego y plataforma especificados.");
        }

        // Verificar si hay suficientes unidades disponibles
        if ($videojuego_plataforma['unidades'] < $item['unidades']) {
            $_SESSION['error_message'] = "Lo sentimos, no hay suficientes unidades del videojuego '" . htmlspecialchars($videojuego['nombre']) . "' en la plataforma '" . htmlspecialchars($item['plataforma']) . "'.";
            header("Location: confirmar_alquiler.php");
            exit();
        }

        // Insertar en `alquiler_videojuegos_plataforma`
        $consulta_detalle->bindParam(':alquiler_id', $alquiler_id, PDO::PARAM_INT);
        $consulta_detalle->bindParam(':videojuego_plataforma_id', $videojuego_plataforma['id'], PDO::PARAM_INT);
        $consulta_detalle->bindParam(':unidades', $item['unidades'], PDO::PARAM_INT);
        $consulta_detalle->execute();

        // Actualizar el inventario en la tabla `videojuegos_plataforma`
        $consulta_update_inventario = $db->prepare("
            UPDATE videojuegos_plataforma
            SET unidades = unidades - :unidades
            WHERE id = :videojuego_plataforma_id
        ");
        $consulta_update_inventario->bindParam(':unidades', $item['unidades'], PDO::PARAM_INT);
        $consulta_update_inventario->bindParam(':videojuego_plataforma_id', $videojuego_plataforma['id'], PDO::PARAM_INT);
        $consulta_update_inventario->execute();

        // Verificar si se actualizó correctamente el inventario
        if ($consulta_update_inventario->rowCount() === 0) {
            throw new Exception("No se pudo actualizar el inventario.");
        }
    }
    unset($item); 
    // Confirmar la transacción
    $db->commit();

$resumenPedido = '<div style="display: flex; flex-wrap: wrap; gap: 16px;">';
foreach ($carrito as $item) {

    // Generar contenido para cada videojuego
    $resumenPedido .= "
        <div style='border: 1px solid #ddd; border-radius: 8px; width: 200px; padding: 16px; text-align: center;'>
            <h3 style='font-size: 16px; margin: 8px 0;'>{$item['videojuego_nombre']}</h3>
            <p style='margin: 4px 0;'>Plataforma: {$item['plataforma']}</p>
            <p style='margin: 4px 0;'>Precio:" . number_format($item['precio'], 2)."</p>
            <p style='margin: 4px 0;'>Unidades: {$item['unidades']}</p>
        </div>
    ";
}
$resumenPedido .= '</div>';
$resumenPedido .= '<p><strong>Días de alquiler:</strong> ' . htmlspecialchars($dias_alquiler) . '</p>';
$resumenPedido .= '<p><strong>Total:</strong> €' . number_format(array_sum(array_map(function($item) {
    return $item['precio'] * $item['unidades'];
}, $carrito)) * $dias_alquiler, 2) . '</p>';


    number_format(array_sum(array_map(function($item) {
        return $item['precio'] * $item['unidades'];
    }, $carrito)) * $dias_alquiler, 2) . '</p>';


    // Enviar correo
    $emailCliente = $email; // Reemplaza con el email del cliente
    $nombreClienteCorreo = $nombreCliente; // Reemplaza con el nombre del cliente

if (!enviarCorreoResumen($emailCliente, $nombreClienteCorreo, $resumenPedido, $referencia_recogida, $punto_recogida, $dias_alquiler, $carrito)) {
            exit("Error al enviar el correo electrónico.");
        }


    // Limpiar la sesión
    unset($_SESSION['carrito'], $_SESSION['dias_alquiler'], $_SESSION['punto_recogida']);

    // Redirigir a la página de éxito
    header("Location: ../pagina_principal/index.php");
    exit();

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}
?>
