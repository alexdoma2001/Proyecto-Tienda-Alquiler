<?php
session_start(); 

include '../conexion_be.php'; 


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Verificar si el correo ya está registrado
    $consulta_verificar_existencia_correo = "SELECT COUNT(*) FROM cliente WHERE correo = :correo";
    $preparada_correo = $db->prepare($consulta_verificar_existencia_correo);
    $preparada_correo->execute([':correo' => $correo]);
    $existe_correo = $preparada_correo->fetchColumn() > 0;

    // Verificar si el DNI ya está registrado
    $consulta_verificar_existencia_dni = "SELECT COUNT(*) FROM cliente WHERE dni = :dni";
    $preparada_dni = $db->prepare($consulta_verificar_existencia_dni);
    $preparada_dni->execute([':dni' => $dni]);
    $existe_dni = $preparada_dni->fetchColumn() > 0;

        // Si el correo o el DNI ya existen, redirigir con mensaje de error
        if ($existe_correo && $existe_dni) {
            $_SESSION['error'] = 'El correo y el DNI ya están registrados.';
            header('Location: registro.php'); 
            exit;
        }

    if ($existe_correo) {
        $_SESSION['error'] = 'El correo ya esta registrado.';
        header('Location: registro.php'); 
        exit;
    }

    if ( $existe_dni) {
        $_SESSION['error'] = 'El DNI ya están registrado.';
        header('Location: registro.php'); 
        exit;
    }

    // Si todo está bien, insertar el nuevo usuario
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $consulta = "INSERT INTO cliente (DNI, nombre, correo, contraseña, monedero) 
            VALUES (:DNI, :nombre, :correo, :contrasena, :monedero)";
    $preparada_insert = $db->prepare($consulta);
    $preparada_insert->execute([
        ':DNI' => $dni,
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':contrasena' => $hashedPassword,
        ':monedero' => 0.0,
    ]);

    // Obtener el ID del nuevo usuario
    $lastInsertId = $db->lastInsertId();

    // Iniciar la sesión
    $_SESSION['cliente_id'] = $lastInsertId;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['correo'] = $correo;
    $_SESSION['monedero'] = 0.0;

    // Redirigir al usuario a la página principal
    header('Location: ../pagina_principal/index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
.gradient-form {
      background-color: #eee;
    }
    .gradient-custom-2 {
      background: linear-gradient(45deg, #FF512F, #DD2476);
    }
    .boton-registro {
      background-color: #dd2476 !important;
      border: none;
    }
    .boton-inicio-sesion {
        background-color: #dd2476 !important;
        border: none;
    }

    .error-msg {
      color: red;
      font-size: 0.9em;
    }


    .card-body {
      padding: 2rem;
    }
    .form-label {
      font-weight: bold;
    }


    #error-message {
      color: red;
      font-size: 1em;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
<section class="h-100 gradient-form">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-xl-10">
          <div class="card rounded-3 text-black">
            <div class="row g-0">
              <div class="col-lg-6">
                <div class="card-body p-md-5 mx-md-4">
                  <div class="d-flex justify-content-center">
                    <a href="../pagina_principal/index.php" class="btn boton-inicio-sesion mb-3 text-white text-center">Ir a la Página Principal</a>
                  </div>
                  <div class="text-center">
                    <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                    <h4 class="mt-1 mb-5 pb-1">Regístrate en Cheap Games</h4>
                  </div>

                  <!-- Mostrar mensaje de error si existe -->
                  <?php if (isset($_SESSION['error'])): ?>
                    <div id="error-message" class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                  <?php endif; ?>

                  <form id="registroForm" method="POST" action="registro.php">
                    <div class="mb-3">
                      <label for="dni" class="form-label">DNI o NIE</label>
                      <input type="text" class="form-control" id="dni" name="dni" placeholder="DNI o NIE" required>
                      <div id="dni-error" class="error-msg" style="display:none;">Formato de DNI/NIE inválido.</div>
                    </div>

                    <div class="mb-3">
                      <label for="nombre" class="form-label">Nombre</label>
                      <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required>
                    </div>

                    <div class="mb-3">
                      <label for="correo" class="form-label">Correo electrónico</label>
                      <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo electrónico" required>
                      <div id="correo-error" class="error-msg" style="display:none;">Correo ya registrado.</div>
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label">Contraseña</label>
                      <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    </div>

                    <div class="mb-3">
                      <label for="confirmPassword" class="form-label">Repite la contraseña</label>
                      <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Repite la contraseña" required>
                      <div id="password-error" class="error-msg" style="display:none;">Las contraseñas no coinciden.</div>
                    </div>

                    <div class="text-center pt-1 mb-5 pb-1">
                      <button class="btn btn-primary btn-block gradient-custom-2 mb-3" type="submit">Registrar</button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                <div class="text-white px-3 p-md-5 mx-md-4">
                  <h4 class="mb-4">Únete a nuestra comunidad.</h4>
                  <p class="medium mb-0">Crea tu cuenta y empieza a alquilar los mejores videojuegos a los mejores precios.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </section>

  <script>
    document.getElementById('registroForm').addEventListener('submit', function(event) {
      document.getElementById('dni-error').style.display = 'none';
      document.getElementById('correo-error').style.display = 'none';
      document.getElementById('password-error').style.display = 'none';

      // Validaciones de formulario antes de enviar
      if (!validateDni(document.getElementById('dni').value)) {
        event.preventDefault();
        document.getElementById('dni-error').style.display = 'block';
      }

      if (!validateCorreo(document.getElementById('correo').value)) {
        event.preventDefault();
        document.getElementById('correo-error').style.display = 'block';
      }

      if (document.getElementById('password').value !== document.getElementById('confirmPassword').value) {
        event.preventDefault();
        document.getElementById('password-error').style.display = 'block';
      }
    });

    function validateDni(dni) {
      // Validar DNI
      const dniRegex = /^[0-9]{8}[A-Za-z]{1}$/;
      return dniRegex.test(dni);
    }

    function validateCorreo(correo) {
      // Validar correo electrónico
      const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      return emailRegex.test(correo);
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
