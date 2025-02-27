<?php
// Incluir conexión a la base de datos
include '../conexion_be.php';

// Iniciar sesión
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'], $_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        
        $email = $_POST['email'];
        $contraseña = $_POST['password'];

        try {
            // Comprobar si el correo existe en la tabla administrador
            $sql_check = "SELECT * FROM administrador WHERE correo = :email";
            $preparada = $db->prepare($sql_check);
            $preparada->bindParam(':email', $email);
            $preparada->execute();

            if ($preparada->rowCount() > 0) {
                // Obtener los datos del administrador
                $admin = $preparada->fetch(PDO::FETCH_ASSOC);

                // Verificar la contraseña
                if (password_verify($contraseña, $admin['contraseña'])) {
                    // Iniciar sesión y guardar datos en la sesión
                    $_SESSION['admin_id'] = $admin['ID']; // ID del administrador
                    $_SESSION['admin_nombre'] = $admin['nombre']; // Nombre del administrador
                    $_SESSION['admin_correo'] = $admin['correo']; // Correo del administrador

                    // Redirigir al panel de administración
                    header("Location: index_admin.php");
                    exit(); // Asegura que no se siga ejecutando el código después de redirigir
                } else {
                    $error = "Contraseña incorrecta. Por favor, inténtalo de nuevo.";
                }
            } else {
                $error = "El correo no está registrado como administrador.";
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
    <title>Inicio de Sesión Administrador</title>
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
<body class="gradient-form">
  <section class="h-100 gradient-form">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-xl-10">
          <div class="card rounded-3 text-black">
            <div class="row g-0">
              <div class="col-lg-6">
                <div class="card-body p-md-5 mx-md-4">
                  <div class="text-center">
                    <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                    <h4 class="mt-1 mb-5 pb-1">Panel de Administración - Cheap Games</h4>
                  </div>

                  <!-- Mostrar mensaje de error si existe -->
                  <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                  <?php endif; ?>

                  <form method="POST" action="login_admin.php">
                    <div data-mdb-input-init class="form-outline mb-4">
                    <label class="form-label" for="email">Correo electrónico</label>
                      <input type="email" id="email" class="form-control" name="email" placeholder="Correo electrónico" required />
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <label class="form-label" for="password">Contraseña</label>
                      <input type="password" id="password" class="form-control" name="password" placeholder="Contraseña" required />
                    </div>

                    <div class="text-center pt-1 mb-5 pb-1">
                      <button data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Iniciar Sesión</button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                <div class="text-white px-3 p-md-5 mx-md-4">
                  <h4 class="mb-4">Acceso exclusivo para administradores.</h4>
                  <p class="medium mb-0">Accede al panel de control para gestionar los videojuegos, usuarios y más.</p>
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
