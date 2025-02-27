<?php
// Iniciar la sesión
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar por Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            height: 200px;
            object-fit: fill;
            border-radius: 10px;
        }

        .card-body {
            text-align: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .back-btn {
            display: block;
            margin: 20px auto;
        }

        a{
            text-decoration: none;
        }

        body{
          background-color: #d0c5c5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex align-items-center justify-content-center pb-4 mt-4">
            <a href="gestionar_alquileres.php" class="btn btn-outline-danger">Atrás</a>
        </div>
        <h1>Buscar alquiler por cliente</h1>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <!-- Card 1: Buscar por DNI -->
            <div class="col">
                <div class="card">
                    <a href="buscar_por_dni.php">
                        <img src="../../imagenes/dni.png" class="card-img-top" alt="Buscar por DNI">
                        <div class="card-body">
                            <h5 class="card-title">Buscar por DNI o NIE</h5>
                        </div>
                    </a>
                </div>
            </div>
            <!-- Card 2: Buscar por Correo -->
            <div class="col">
                <div class="card">
                    <a href="buscar_por_correo.php">
                        <img src="../../imagenes/correo.png" class="card-img-top" alt="Buscar por Correo">
                        <div class="card-body">
                            <h5 class="card-title">Buscar por Correo</h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
