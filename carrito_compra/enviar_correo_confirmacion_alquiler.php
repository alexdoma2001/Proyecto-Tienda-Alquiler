<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function enviarCorreoResumen($email, $nombreClienteCorreo, $resumenPedido, $referenciaRecogida, $puntoRecogida, $dias_alquiler, $carrito)
{
    $mail = new PHPMailer(true);

    try {

        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'symfonybasta@gmail.com';
        $mail->Password = 'ybcfphxhgmxqabbd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('symfonybasta@gmail.com', 'Alquiler de videojuegos');
        $mail->addAddress($email, $nombreClienteCorreo);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Resumen de tu pedido - Alquiler de Videojuegos';
        $mail->Body = "<h2>Gracias por tu alquiler</h2>";
        $mail->Body .= "<p>Hola <strong>{$nombreClienteCorreo}</strong>, aquí dejamos un resumen de tu pedido. ¡Esperamos que lo disfrutes!</p>";
        $mail->Body .= "<div style='display: flex; flex-wrap: wrap; gap: 16px;'>";

        // Recorremos el carrito y agregamos las imágenes embebidas y el contenido correspondiente
        foreach ($carrito as $item) {

            // Agregar cada producto con su imagen al cuerpo del correo
            $mail->Body .= "
            <div style='border: 1px solid #ddd; border-radius: 8px; width: 200px; padding: 16px; text-align: center;'>
                <h3 style='font-size: 16px; margin: 8px 0;'>{$item['nombre']}</h3>
                <p style='margin: 4px 0;'>Plataforma: {$item['plataforma']}</p>
                <p style='margin: 4px 0;'>Precio: " . number_format($item['precio'], 2) . " €</p>
                <p style='margin: 4px 0;'>Unidades: {$item['unidades']}</p>
            </div>
            ";
        }

        $mail->Body .= "</div>";

        // Calcular el total del pedido
        $total = array_sum(array_map(function ($item) {
            return is_array($item) ? $item['precio'] * $item['unidades'] : 0;
        }, $carrito)) * $dias_alquiler;

        $mail->Body .= "<p><strong>Días de alquiler:</strong> {$dias_alquiler}</p>";
        $mail->Body .= "<p><strong>Total:</strong> " . number_format($total, 2) . " €</p>";
        $mail->Body .= '<p>Tienes tu pedido disponible en <strong>' . htmlspecialchars($puntoRecogida) . '</strong>.</p>';
        $mail->Body .= '<p>Gracias por confiar en nosotros.</p>';
        $mail->Body .= '<p>Atentamente,</p>';
        $mail->Body .= '<p><strong>El equipo de Cheap Games</strong></p>';

        // Enviar el correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Error al procesar el correo: " . $e->getMessage();
        echo "<br>Contenido del carrito: <pre>" . print_r($carrito, true) . "</pre>";
    }
}
?>
