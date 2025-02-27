<?php
// Iniciar sesión
session_start();
include('../conexion_be.php');

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Esto es lo de la configuracion del phpmailer

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Elimina caracteres raros para evitar inyecciones SQL

    // Verificar si el correo es válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = ['tipo' => 'error', 'texto' => 'Por favor, introduce un correo válido.'];
    } else {
        // Vemos que el correo existe o no en la bd
        $preparada = $db->prepare("SELECT * FROM cliente WHERE correo = :email");
        $preparada->bindParam(':email', $email);
        $preparada->execute();
        
        // Si existe el correo
        if ($preparada->rowCount() > 0) {
            // Generamos un token único para la recuperación
            $token = bin2hex(random_bytes(50));

            // Insertar el token en la base de datos junto con la fecha de expiración
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // El token expirará en 1 hora
            $consulta_update = $db->prepare("UPDATE cliente SET token_recuperacion = :token, expiracion_token = :expiracion WHERE correo = :email");
            $consulta_update ->bindParam(':token', $token);
            $consulta_update ->bindParam(':expiracion', $expiracion);
            $consulta_update ->bindParam(':email', $email);
            $consulta_update ->execute();

            // Enviar el correo de recuperación
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'symfonybasta@gmail.com';
                $mail->Password = 'ybcfphxhgmxqabbd';
                $mail->SMTPSecure = 'tls'; 
                $mail->Port = 587;

                $mail->setFrom('symfonybasta@gmail.com', 'cheap games');
                $mail->addAddress($email); // Agrega el destinatario

                $mail->isHTML(true);
                $mail->Subject = 'Recupera tu contraseña';
                $url = "http://localhost:3000/TFG%20Alejandro%20Donaire/inicio_sesion/cambiar_contrasena_olvidada.php?token=" . $token;
                $mail->Body = "Hola, hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para recuperarla: <a href='$url'>Click aqui para cambiar tu contraseña</a>";

                $mail->send();
                $mensaje = ['tipo' => 'exito', 'texto' => 'Hemos enviado un correo de verificación. Por favor, revisa tu buzón para recuperar el acceso a tu cuenta.'];
            } catch (Exception $e) {
                $mensaje = ['tipo' => 'error', 'texto' => "Hubo un error al enviar el correo: {$mail->ErrorInfo}"];
            }
        } else {
            $mensaje = ['tipo' => 'error', 'texto' => 'No hay ninguna cuenta asociada a ese correo.'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .gradient-form {
        background: linear-gradient(45deg, #FF512F, #DD2476);
        size: cover;
        background-repeat: no-repeat;
        min-height: 100vh;
      }

      .boton-perfil {
        background-color: #dd2476 !important;
        border: none;
      }

      .boton-inicio-sesion {
        background-color: #dd2476 !important;
        border: none;
      }

      .btn-enviar{
        background: linear-gradient(45deg, #FF512F, #DD2476);
        color: white;
      }

      .btn-enviar:hover {
        color: white; 
      }

      .error {
        color: red;
      }

      .exito {
        color: green;
      }
    </style>
</head>

<body class="gradient-form">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-xl-7">
                    <div class="card rounded-3 text-black">
                        <div class="row g-0">
                                <div class="card-body p-md-5 mx-md-4">
                                    <div class="text-center">
                                        <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                                        <h4 class="mt-1 mb-5 pb-1">Recuperar Contraseña</h4>
                                    </div>

                                    <!-- Mostrar mensaje de éxito o error -->
                                    <?php if (isset($mensaje)): ?>
                                        <div class="alert <?= $mensaje['tipo'] === 'error' ? 'alert-danger' : 'alert-success' ?>">
                                            <?= $mensaje['texto'] ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="olvidado_contrasena.php" method="POST">
                                        <div class="form-outline mb-4">
                                            <label class="form-label" for="email">Introduce el correo de tu cuenta</label>
                                            <input type="email" id="email" name="email" class="form-control" placeholder="Correo electrónico" required />
                                        </div>

                                        <div class="text-center pt-1 mb-5 pb-1">
                                            <button type="submit" name="submit" class="btn btn-block mb-3 btn-enviar">Enviar</button>
                                        </div>
                                    </form>

                                    <div class="text-center">
                                        <a href="../pagina_principal/index.php" class="btn boton-inicio-sesion mb-3 text-white">Ir a la Página Principal</a>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
