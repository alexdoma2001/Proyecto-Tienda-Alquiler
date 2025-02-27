<?php
// Iniciar sesión
session_start();
include('../conexion_be.php'); // Incluir archivo de conexión a la base de datos

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar el token en la base de datos
    $preparada = $db->prepare("SELECT * FROM cliente WHERE token_recuperacion = :token AND expiracion_token > NOW()");
    $preparada->bindParam(':token', $token);
    $preparada->execute();

    // Verificamos que hay toquen y si lo hay es porque ha solicitado el cambio de contraseña
    if ($preparada->rowCount() > 0) {
        
        $usuario = $preparada->fetch(PDO::FETCH_ASSOC);

        
        if (isset($_POST['cambiar'])) {
            $nueva_contraseña = $_POST['nueva_contraseña'];
            $confirmar_contraseña = $_POST['confirmar_contraseña'];

            
            if ($nueva_contraseña !== $confirmar_contraseña) {
                $mensaje = ['tipo' => 'error', 'texto' => 'Las contraseñas no coinciden.'];
            } else {
                $nueva_contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
                $actualizar_contrasena = $db->prepare("UPDATE cliente SET contraseña = :nueva_contrasena, token_recuperacion = NULL, expiracion_token = NULL WHERE token_recuperacion = :token");
                $actualizar_contrasena->bindParam(':nueva_contrasena', $nueva_contraseña_hash);
                $actualizar_contrasena->bindParam(':token', $token);
                
                // Ejecutar la actualización
                if ($actualizar_contrasena->execute()) {
                    $mensaje = ['tipo' => 'exito', 'texto' => 'Tu contraseña ha sido cambiada con éxito.'];
                } else {
                    $mensaje = ['tipo' => 'error', 'texto' => 'Error al actualizar la contraseña.'];
                }
            }
        }
    } else {
        $mensaje = ['tipo' => 'error', 'texto' => 'Este enlace ha caducado. Porfavor intentelo de nuevo'];
    }
} else {
    $mensaje = ['tipo' => 'error', 'texto' => 'No se ha proporcionado un token.'];
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
            background: linear-gradient(45deg, #FF512F, #DD2476);
            size: cover;
            background-repeat: no-repeat;
            min-height: 100vh;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-enviar {
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

        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body class="gradient-form">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-xl-5">
                <div class="card rounded-3 text-black">
                    <div class="card-body p-md-5 mx-md-4">
                        <div class="text-center">
                            <img src="../imagenes/logo_pagina.png" style="width: 185px;" alt="logo">
                            <h4 class="mt-1 mb-5 pb-1">Cambiar Contraseña</h4>
                        </div>

                        <!-- Mostrar mensaje de éxito o error -->
                        <?php if (isset($mensaje)): ?>
                            <div class="alert <?= $mensaje['tipo'] === 'error' ? 'alert-danger' : 'alert-success' ?>">
                                <?= $mensaje['texto'] ?>
                            </div>
                        <?php endif; ?>

                        <form action="cambiar_contrasena_olvidada.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                            <div class="form-outline mb-4">
                                <label class="form-label" for="nueva_contraseña">Nueva Contraseña</label>
                                <input type="password" id="nueva_contraseña" name="nueva_contraseña" class="form-control" required placeholder="Nueva contraseña">
                            </div>
                            <div class="form-outline mb-4">
                                <label class="form-label" for="confirmar_contraseña">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" class="form-control" required placeholder="Confirmar contraseña">
                            </div>
                            <div class="text-center pt-1 mb-5 pb-1">
                                <button type="submit" name="cambiar" class="btn btn-enviar btn-block mb-3">Cambiar Contraseña</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

