<?php
include '../conexion_be.php';

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['cliente_id'])) {
  header("Location: ../inicio_sesion/inicio_sesion.php");
  exit();
}

$error = '';

// Obtener el saldo del monedero del usuario
$saldoMonedero = 0;
if (isset($_SESSION['cliente_id'])) {
  try {
    // Consulta para obtener el saldo del monedero
    $preparada = "SELECT monedero FROM cliente WHERE ID = :id";  
    $preparada = $db->prepare($preparada);
    $preparada->bindParam(':id', $_SESSION['cliente_id']);  
    $preparada->execute();
    $resultado = $preparada->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
      $saldoMonedero = $resultado['monedero'];
      $_SESSION['monedero'] = $saldoMonedero;
    } else {
      $error = "No se pudo obtener el saldo del monedero.";
    }
  } catch (PDOException $e) {
    $error = "Error en la base de datos: " . $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // cerrar sesion
  if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .gradient-form {
      background-color: #eee;
    }

    .gradient-custom-2 {
      background: linear-gradient(45deg, #FF512F, #DD2476);
    }

    .boton-perfil {
      background-color: #dd2476 !important;
      border: none;
    }

    .btn-outline-white {
      background-color: transparent;
      border: 2px solid white;
      color: white;
      transition: background-color 0.3s, color 0.3s;
    }

    .btn-outline-white:hover {
      background-color: white;
      color: #DD2476;
    }
  </style>
</head>

<body class="h-100 gradient-form">
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center mt-4">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>
  <section>
    <div class="container py-1 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-xl-10">
          <div class="card rounded-3 text-black">
            <div class="row g-0">
              <div class="col-lg-6">
                <div class="card-body p-md-5 mx-md-4">
                  <div class="d-flex align-items-center justify-content-center pb-4">
                    <a href="../pagina_principal/index.php" class="btn btn-outline-danger">Volver a la pagina principal</a>
                  </div>
                  <div class="text-center">
                    <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                    <h4 class="mt-1 mb-2 pb-1">Bienvenido, <?php echo $_SESSION['nombre']; ?></h4>
                    <!-- Mostrar el saldo del monedero -->
                    <p>Tu saldo monedero: <strong><?php echo number_format($saldoMonedero, 2); ?> €</strong></p>
                  </div>

                  <!-- Mostrar mensaje de error si existe -->
                  <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                  <?php endif; ?>

                  <form method="POST" action="perfil.php">
                    <div class="text-center">
                      <button class="btn boton-perfil mb-3 text-white" type="button" onclick="window.location.href='cambiar_contraseña.php'">Cambiar Contraseña</button>
                      <br>
                      <button class="btn boton-perfil mb-3 mt-4 text-white" type="button" onclick="window.location.href='agregar_saldo.php'">Agregar Saldo</button>
                      <br>
                      <button class="btn btn-danger mb-3 mt-4 text-white" type="button" onclick="confirmarEliminacion()">Eliminar Cuenta</button>
                      <br>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-lg-6 d-flex gradient-custom-2">
                <div class="text-white px-3 p-md-5 mx-md-4">
                  <h4 class="mb-4 text-center">Gestiona tu perfil.</h4>
                  <p class="medium mb-0">Desde aquí puedes cambiar tu contraseña, agregar saldo a tu cuenta o eliminar su cuenta.</p>
                  <p class="medium mb-0"><br>Gracias por confiar en nosotros.</p>
                  <p class="medium mb-0"><br>Aquí podrá consultar los pedidos que ha realizado y gestionar sus alquileres activos:</p>
                  <a href="info_pedidos.php" class="btn btn-outline-white border-white mt-3">Ver mis alquileres</a>
                  <div class="text-white p-md-5 mx-md-4">
                    <p class="bottom mb-0">Para cualquier incidencia, contacte con nosotros en: cheappppgamessss@gmail.com</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script>
    function confirmarEliminacion() {
      const confirmar = confirm("¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.");
      if (confirmar) {
        window.location.href = 'eliminar_cuenta.php';
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>