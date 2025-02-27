<?php
// Iniciar la sesión
session_start();

// Evitar el almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
    <title>Gestionar Alquileres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Centrando el contenido del título */
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        
        /* Estilos de las tarjetas (cards) */
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

        /* Centrar el botón de cerrar sesión */
        .logout-btn {
            display: block;
            margin: 20px auto;
            text-align: center;
        }

        /* Espaciado entre las tarjetas */
        .card-deck {
            margin-top: 20px;
        }

        .centered-card {
            display: flex;
            justify-content: center; /* Centra horizontalmente */
            margin: 20px 0; /* Espacio alrededor */
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
        <a href="../index_admin.php" class="btn btn-outline-danger">Atrás</a>
    </div>
    <!-- Título centrado -->
    <h1>Gestionar Alquileres</h1>

    
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <!-- Card 1: Buscar por Cliente -->
      <div class="col">
        <div class="card">
          <a href="buscar_por_cliente.php">
            <img src="../../imagenes/cliente.png" class="card-img-top" alt="Buscar por Cliente">
            <div class="card-body">
              <h5 class="card-title">Buscar por Cliente</h5>
            </div>
          </a>
        </div>
      </div>

      <!-- Card 2: Buscar por Alquiler -->
      <div class="col">
        <div class="card">
          <a href="buscar_por_alquiler.php">
            <img src="../../imagenes/alquiler.png" class="card-img-top" alt="Buscar por Alquiler">
            <div class="card-body">
              <h5 class="card-title">Buscar por Alquiler</h5>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
