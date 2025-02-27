<?php

include '../conexion_be.php';


session_start();


if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gradient-form {
            background-color: #eee;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05); 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); 
        }

        .card img {
            height: 200px;
            object-fit: fill;
        }

        .btn-custom {
            background-color: #dd2476;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            color: white;
            background-color: #c02067;
        }
    </style>
</head>
<body class="gradient-form">
    <section>
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-lg-8">
                    <h1 class="text-center mb-4">Gestiona tus alquileres</h1>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <!-- Card para Pedidos Pasados -->
                        <div class="col">
                            <div class="card">
                                <img src="../imagenes/pedidos_pasados.png" class="card-img-top" alt="Alquileres Pasados">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Alquileres Pasados</h5>
                                    <p class="card-text">Consulta los alquileres que ya has realizado.</p>
                                    <a href="pedidos_pasados.php" class="btn btn-custom">Ver alquileres pasados</a>
                                </div>
                            </div>
                        </div>
                        <!-- Card para Pedidos Activos -->
                        <div class="col">
                            <div class="card">
                                <img src="../imagenes/pedidos_activos.png" class="card-img-top" alt="Alquileres Activos">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Alquileres Activos</h5>
                                    <p class="card-text">Revisa los alquileres que están en curso.</p>
                                    <a href="pedidos_activos.php" class="btn btn-custom">Ver alquileres activos</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center pt-4">
                             <a href="perfil.php" class="btn btn-outline-danger">Volver atrás</a>
                        </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
