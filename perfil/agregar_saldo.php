<?php
include '../conexion_be.php';

session_start();

if (!isset($_SESSION['cliente_id'])) {
  header("Location: ../inicio_sesion/inicio_sesion.php");
  exit();
}

// Variable inicializadas
$errorTarjeta = '';
$errorFecha = '';
$errorCVC = '';
$errorSaldo = '';
$mensajeExito = '';

// Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tarjeta = $_POST['tarjeta'] ?? '';
    $fechaValidez = $_POST['fechaValidez'] ?? '';
    $cvc = $_POST['cvc'] ?? '';
    $saldo = $_POST['saldo'] ?? '';

    // Validaciones
    if (!preg_match('/^[0-9]{12}$/', $tarjeta)) {
        $errorTarjeta = "La tarjeta de crédito debe tener 12 números.";
    }

    // Validar formato de la fecha de validez (MM/YY)
    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $fechaValidez)) {
        $errorFecha = "La fecha de validez debe tener el formato MM/AA.";
    }

    if (!preg_match('/^[0-9]{3}$/', $cvc)) {
        $errorCVC = "El CVC debe tener 3 números.";
    }

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $saldo) || $saldo <= 0) {
        $errorSaldo = "El saldo debe ser un número positivo";
    }

    // Si no hay errores, actualizar el saldo del usuario
    if (empty($errorTarjeta) && empty($errorFecha) && empty($errorCVC) && empty($errorSaldo)) {
        $email = $_SESSION['correo']; // Obtener el correo del usuario desde la sesión

        $saldo = str_replace(',', '.', $saldo);
    
        try {
            // Obtener el monedero actual del usuario
            $consulta_monedero = "SELECT monedero FROM cliente WHERE correo = :email";
            $preparada= $db->prepare($consulta_monedero);
            $preparada->bindParam(':email', $email);
            $preparada->execute();
            $usuario = $preparada->fetch(PDO::FETCH_ASSOC);
    
            if ($usuario) {
                // Sumar el saldo al monedero actual
                $nuevoMonedero = $usuario['monedero'] + $saldo;
    
                // Actualizar el monedero en la base de datos
                $actualizar_monedero = "UPDATE cliente SET monedero = :nuevoMonedero WHERE correo = :email";
                $consulta_actualizar = $db->prepare($actualizar_monedero);
                $consulta_actualizar->bindParam(':nuevoMonedero', $nuevoMonedero);
                $consulta_actualizar->bindParam(':email', $email);
    
                if ($consulta_actualizar->execute()) {
                    $mensajeExito = "Saldo agregado correctamente. Nuevo saldo en el monedero: $nuevoMonedero €";
                } else {
                    $errorSaldo = "Hubo un problema al agregar el saldo. Inténtalo de nuevo.";
                }
            }
        } catch (PDOException $e) {
            $errorSaldo = "Error en la base de datos: " . $e->getMessage();
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Saldo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .gradient-form {
        background-color: #eee;
      }

      .gradient-custom-2 {
        background: linear-gradient(45deg, #FF512F, #DD2476);
      }

      .boton-saldo {
        background-color: #dd2476 !important;
        border: none;
      }

      .text-danger {
        color: #ff0000 !important;
      }

      .text-success {
        color: #28a745 !important;
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
                <h4 class="mt-1 mb-5 pb-1">Agregar Saldo</h4>
              </div>

              <!-- Mostrar mensajes de éxito o error -->
              <?php if (!empty($mensajeExito)): ?>
                <div class="alert alert-success"><?php echo $mensajeExito; ?></div>
              <?php endif; ?>

              <form method="POST" action="agregar_saldo.php">
                <!-- Tarjeta de crédito -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="tarjeta">Tarjeta de crédito</label>
                  <input type="text" id="tarjeta" class="form-control" name="tarjeta" required placeholder="Inserte su tarjeta de credito (12 digitos)"/>
                  <!-- Mensaje de error de la tarjeta -->
                  <?php if (!empty($errorTarjeta)): ?>
                    <div class="text-danger"><?php echo $errorTarjeta; ?></div>
                  <?php endif; ?>
                </div>

                <!-- Fecha de validez -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="fechaValidez">Fecha de validez (MM/AA)</label>
                  <input type="text" id="fechaValidez" class="form-control" name="fechaValidez" required maxlength="5" placeholder="MM/AA" oninput="formatoFechaValidez(this)" />
                  <!-- Mensaje de error de la fecha -->
                  <?php if (!empty($errorFecha)): ?>
                    <div class="text-danger"><?php echo $errorFecha; ?></div>
                  <?php endif; ?>
                </div>

                <!-- CVC -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="cvc">CVC</label>
                  <input type="text" id="cvc" class="form-control" name="cvc" placeholder="CVC" required />
                  <!-- Mensaje de error del CVC -->
                  <?php if (!empty($errorCVC)): ?>
                    <div class="text-danger"><?php echo $errorCVC; ?></div>
                  <?php endif; ?>
                </div>

                <!-- Saldo a ingresar -->
                <div class="form-outline mb-4">
                  <label class="form-label" for="saldo">Saldo a ingresar (€)</label>
                  <input type="text" id="saldo" class="form-control" name="saldo" placeholder="Ej: 10,20" required />
                  <!-- Mensaje de error del saldo -->
                  <?php if (!empty($errorSaldo)): ?>
                    <div class="text-danger"><?php echo $errorSaldo; ?></div>
                  <?php endif; ?>
                </div>

                <!-- Botón para agregar saldo -->
                <div class="text-center pt-1 mb-5 pb-1">
                  <button class="btn btn-primary btn-block fa-lg gradient-custom-2 boton-saldo" type="submit">Agregar Saldo</button>
                </div>
              </form>

              <div class="d-flex align-items-center justify-content-center pb-4">
                <a href="perfil.php" class="btn btn-outline-danger">Volver atrás</a>
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

  <script>
  function formatoFechaValidez(input) {
    let value = input.value;
    
    // Elimina cualquier caracter no numérico y slash
    value = value.replace(/[^0-9]/g, '');
    
    // Inserta una barra después de los dos primeros dígitos
    if (value.length >= 3) {
      input.value = value.slice(0, 2) + '/' + value.slice(2, 4);
    } else {
      input.value = value;
    }
  }
</script>
</body>
</html>
