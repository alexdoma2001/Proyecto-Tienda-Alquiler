<?php
include '../conexion_be.php';

// Iniciar la sesión
session_start();
if (!isset($_SESSION['cliente_id'])) {
  header("Location: ../inicio_sesion/inicio_sesion.php");
  exit();
}

// Variable para manejar errores y mensajes
$errorActual = '';
$errorNueva = '';
$errorConfirmar = '';
$mensajeExito = '';

// Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $actualPassword = $_POST['actualPassword'] ?? '';
    $nuevaPassword = $_POST['nuevaPassword'] ?? '';
    $confirmarPassword = $_POST['confirmarPassword'] ?? '';

    // Obtener el correo del usuario de la sesión
    $email = $_SESSION['correo'];

    // Consultar la contraseña actual del usuario en la base de datos
    $sql = "SELECT contraseña FROM cliente WHERE correo = :email";
    $consulta = $db->prepare($sql);
    $consulta->bindParam(':email', $email);
    $consulta->execute();
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($actualPassword, $usuario['contraseña'])) {
        // Validar que las nuevas contraseñas coinciden
        if ($nuevaPassword === $confirmarPassword) {
            // Encriptar la nueva contraseña
            $hashedPassword = password_hash($nuevaPassword, PASSWORD_BCRYPT);

            // Actualizar la contraseña en la base de datos
            $sql_update = "UPDATE cliente SET contraseña = :nuevaPassword WHERE correo = :email";
            $consulta_update = $db->prepare($sql_update);
            $consulta_update->bindParam(':nuevaPassword', $hashedPassword);
            $consulta_update->bindParam(':email', $email);

            if ($consulta_update->execute()) {
                $mensajeExito = "Contraseña cambiada correctamente.";
            } else {
                $errorNueva = "Hubo un problema al cambiar la contraseña. Inténtalo de nuevo.";
            }
        } else {
            $errorConfirmar = "Las contraseñas no coinciden.";
        }
    } else {
        $errorActual = "Contraseña actual incorrecta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .gradient-form {
        background-color: #eee;
      }

      .gradient-custom-2 {
        background: linear-gradient(45deg, #FF512F, #DD2476);
      }

      .boton-cambio {
        background-color: #dd2476 !important;
        border: none;
      }

      .text-danger {
        color: #ff0000 !important;
      }
    </style>
</head>
<body>
  <section class="h-100 gradient-form">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-xl-6">
          <div class="card rounded-3 text-black">
            <div class="card-body p-md-5 mx-md-4">
              <div class="text-center">
                <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                <h4 class="mt-1 mb-5 pb-1">Cambia tu contraseña</h4>
              </div>

              <!-- Mostrar mensajes de éxito o error -->
              <?php if (!empty($mensajeExito)): ?>
                <div class="alert alert-success"><?php echo $mensajeExito; ?></div>
              <?php endif; ?>

              <form method="POST" action="cambiar_contraseña.php">
                <!-- Contraseña actual -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="actualPassword">Contraseña actual</label>
                  <input type="password" id="actualPassword" class="form-control" name="actualPassword" required />
                  <!-- Mensaje de error de contraseña actual -->
                  <?php if (!empty($errorActual)): ?>
                    <div class="alert alert-danger mt-2"><?php echo $errorActual; ?></div>
                  <?php endif; ?>
                </div>

                <!-- Nueva contraseña -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="nuevaPassword">Nueva contraseña</label>
                  <input type="password" id="nuevaPassword" class="form-control" name="nuevaPassword" required />
                  <!-- Mensaje de error de nueva contraseña -->
                  <?php if (!empty($errorNueva)): ?>
                    <div class="alert alert-danger mt-2"><?php echo $errorNueva; ?></div>
                  <?php endif; ?>
                </div>

                <!-- Confirmar nueva contraseña -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="confirmarPassword">Confirmar nueva contraseña</label>
                  <input type="password" id="confirmarPassword" class="form-control" name="confirmarPassword" required />
                  <!-- Mensaje de error de confirmación -->
                  <?php if (!empty($errorConfirmar)): ?>
                    <div class="alert alert-danger mt-2"><?php echo $errorConfirmar; ?></div>
                  <?php endif; ?>
                </div>

                <!-- Botón para cambiar contraseña -->
                <div class="text-center pt-1 mb-3 pb-1">
                  <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3 boton-cambio" type="submit">Cambiar contraseña</button>
                </div>
              </form>

              <div class="d-flex align-items-center justify-content-center pb-4">
                <a href="perfil.php" class="btn btn-outline-danger">Cancelar</a>
              </div>

              <div class="d-flex align-items-center justify-content-center pb-4">
                <a href="../pagina_principal/index.php" class="btn btn-outline-danger">Volver a la pagina principal</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>
