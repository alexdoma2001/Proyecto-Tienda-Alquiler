<?php
// Iniciar la sesión
session_start();

// Evitar el almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
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

        .fondito_clarito{
          background-color: #eee;
        }
    </style>
</head>
<body class="fondito_clarito">
  <div class="container">
    <!-- Título centrado -->
    <h1 class="text-black bg-light">Panel de Administración</h1>



    <h1>Bienvenido <?php echo $_SESSION['admin_nombre']; ?></h1>
            <!-- Botón de cerrar sesión centrado debajo de la imagen -->
            <form method="POST" action="logout.php" class="logout-btn">
        <button type="submit" class="btn btn-danger">Cerrar Sesión</button>
    </form>
    
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <!-- Card 1: Gestionar Alquileres -->
      <div class="col">
        <div class="card">
          <a href="gestionar_alquileres/gestionar_alquileres.php">
            <img src="../imagenes/alquiler.png" class="card-img-top" alt="Gestionar Alquileres">
            <div class="card-body">
              <h5 class="card-title">Gestionar Alquileres</h5>
            </div>
          </a>
        </div>
      </div>

      <!-- Card 2: Gestionar Puntos de Recogida -->
      <div class="col">
        <div class="card">
          <a href="gestionar_puntos_de_recogida/gestion_puntos_recogida.php">
            <img src="../imagenes/mapa.png" class="card-img-top" alt="Gestionar Puntos de recogida">
            <div class="card-body">
              <h5 class="card-title">Gestionar Puntos de recogida</h5>
            </div>
          </a>
        </div>
      </div>

      <!-- Card 3: Gestionar Videojuegos -->
      <div class="col">
        <div class="card">
          <a href="gestionar_videojuegos/gestion_videojuegos.php">
            <img src="../imagenes/videojuegos.png" class="card-img-top" alt="Gestionar Videojuegos">
            <div class="card-body">
              <h5 class="card-title">Gestionar Videojuegos</h5>
            </div>
          </a>
        </div>
      </div>

      <!-- Card 4: Gestionar Plataformas -->
      <div class="col">
        <div class="card">
          <a href="gestionar_plataformas/gestion_plataformas.php">
            <img src="../imagenes/consolas.png" class="card-img-top" alt="Gestionar Plataformas">
            <div class="card-body">
              <h5 class="card-title">Gestionar Plataformas</h5>
            </div>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Card 5: Gestionar Categorias -->
    <div class="row">
      <div class="col centered-card">
        <div class="card">
          <a href="gestionar_categorias/gestion_categorias.php">
            <img src="../imagenes/categorias.png" class="card-img-top" alt="Gestionar Categorías">
            <div class="card-body">
              <h5 class="card-title">Gestionar Categorías</h5>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
