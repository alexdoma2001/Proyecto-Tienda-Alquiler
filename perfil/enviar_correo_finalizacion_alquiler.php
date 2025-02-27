<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function enviarCorreoResumen($email, $nombreClienteCorreo, $referenciaRecogida, $puntoRecogida, $dias_alquiler, $carrito, $dias_retraso = 0, $multa_total = 0)
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
        $mail->Subject = 'Resumen de tu alquiler - Alquiler de Videojuegos';
        $mail->Body = "<h2>Gracias por tu alquiler</h2>";
        $mail->Body .= "<p>Hola <strong>{$nombreClienteCorreo}</strong>, aquí está el resumen de tu alquiler. ¡Esperamos que lo hayas disfrutado!</p>";

        // Resumen del carrito
        $mail->Body .= "<div style='display: flex; flex-wrap: wrap; gap: 16px;'>";
        foreach ($carrito as $item) {
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

        // Resumen del alquiler
        $mail->Body .= "<p><strong>Días de alquiler:</strong> {$dias_alquiler}</p>";

        // Si hay días de retraso y multa, incluir en el correo
        if ($dias_retraso > 0) {
            $mail->Body .= "<p><strong>Días de retraso:</strong> {$dias_retraso}</p>";
            $mail->Body .= "<p><strong>Multa aplicada:</strong> " . number_format($multa_total, 2) . " €</p>";
        }

        // Punto de recogida
        $mail->Body .= "<p><strong>Referencia de recogida:</strong> {$referenciaRecogida}</p>";
        $mail->Body .= "<p><strong>Punto de recogida:</strong> {$puntoRecogida}</p>";

        // Total del alquiler (incluyendo la multa si existe)
        $total = array_sum(array_map(function ($item) {
            return is_array($item) ? $item['precio'] * $item['unidades'] : 0;
        }, $carrito)) * $dias_alquiler + $multa_total;

        $mail->Body .= "<p><strong>Total final:</strong> " . number_format($total, 2) . " €</p>";
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