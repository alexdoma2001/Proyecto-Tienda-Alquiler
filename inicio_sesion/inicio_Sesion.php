<?php
// Incluir conexión a la base de datos
include '../conexion_be.php';

// Iniciar sesión
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar si los campos están definidos y no están vacíos
    if (isset($_POST['email'], $_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        
        $email = $_POST['email'];
        $contraseña = $_POST['password'];

        try {
            // Comprobar si el correo existe en la tabla cliente
            $sql_check = "SELECT * FROM cliente WHERE correo = :email";
            $preparada= $db->prepare($sql_check);
            $preparada->bindParam(':email', $email);
            $preparada->execute();

            if ($preparada->rowCount() > 0) {
                // Obtener los datos del usuario
                $cliente = $preparada->fetch(PDO::FETCH_ASSOC);

                // Verificar la contraseña
                if (password_verify($contraseña, $cliente['contraseña'])) {
                    // Iniciar sesión y guardar datos en la sesión
                    $_SESSION['cliente_id'] = $cliente['ID']; // ID del cliente
                    $_SESSION['nombre'] = $cliente['nombre']; // Nombre del cliente
                    $_SESSION['correo'] = $cliente['correo']; // Correo del cliente
                    $_SESSION['monedero'] = $cliente['monedero'];

                    // Restaurar el carrito si existe
                    if (isset($_SESSION['carrito'])) {
                        // El carrito ya está en la sesión, lo mantenemos
                        $_SESSION['carrito'] = $_SESSION['carrito']; 
                    } else {
                        // Si no hay carrito, inicializamos uno vacío
                        $_SESSION['carrito'] = [];
                    }

                    // Redirigir a la página principal
                    header("Location: ../pagina_principal/index.php");
                    exit(); // Asegura que no se siga ejecutando el código después de redirigir
                } else {
                    $error = "Contraseña incorrecta. Por favor, inténtalo de nuevo.";
                }
            } else {
                $error = "El correo no está registrado. Por favor, regístrate.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .gradient-form {
        background-color: #eee;
      }

      .gradient-custom-2 {
        background: linear-gradient(45deg, #FF512F, #DD2476);
      }

      .boton-inicio-sesion {
        background-color: #dd2476 !important;
        border: none;
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
                <div class="text-center">
                    <a href="../pagina_principal/index.php" class="btn boton-inicio-sesion mb-3 text-white">Ir a la Página Principal</a>
                </div>
                  <div class="text-center">
                    <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                    <h4 class="mt-1 mb-5 pb-1">Bienvenido a Cheap Games</h4>
                  </div>

                  <!-- Mostrar mensaje de error si existe -->
                  <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                  <?php endif; ?>

                  <form method="POST" action="inicio_sesion.php">

                    <div data-mdb-input-init class="form-outline mb-4">
                    <label class="form-label" for="form2Example11">Correo electrónico</label>
                      <input type="email" id="form2Example11" class="form-control" name="email" placeholder="Correo electrónico" required />
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <label class="form-label" for="form2Example22">Contraseña</label>
                      <input type="password" id="form2Example22" class="form-control" name="password" placeholder="Contraseña" required />
                    </div>

                    <div class="text-center pt-1 mb-5 pb-1">
                      <button data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Iniciar Sesión</button>
                      <br>
                      <a class="text-muted" href="olvidado_contrasena.php">¿Olvidaste tu contraseña?</a>
                    </div>

                    <div class="d-flex align-items-center justify-content-center pb-4">
                      <p class="mb-0 me-2">¿No tienes cuenta?</p>
                      <a href="../registro/registro.php" class="btn btn-outline-danger">Crear nueva cuenta</a>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                <div class="text-white px-3 p-md-5 mx-md-4">
                  <h4 class="mb-4">Nos alegra volver a verte por aquí.</h4>
                  <p class="medium mb-0">Inicia sesión para poder disfrutar de los videojuegos sin pagar un dineral por ellos. <br><br> Disfruta de los juegos que quieras, en el tiempo que necesites, gracias a los mejores precios.</p>
                
                  <p class="medium mb-0"><br>Y sobre todo, gracias por confiar en nosotros.</p>
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
