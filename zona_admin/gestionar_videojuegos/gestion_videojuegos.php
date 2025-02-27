<?php
// Verificar si el administrador ha iniciado sesión
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

// Incluir conexión a la base de datos
include '../../conexion_be.php';

// Parámetros de búsqueda y paginación
$busqueda_nombre = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$limite = 6; // Número de videojuegos por página
$inicio = ($pagina_actual - 1) * $limite;

// Consulta SQL para obtener videojuegos con filtros de búsqueda y paginación
$sql = "SELECT * FROM videojuegos WHERE 1 = 1";

// Filtro de búsqueda por nombre
if (!empty($busqueda_nombre)) {
    $sql .= " AND nombre LIKE :busqueda_nombre";
}

// Añadir límite y desplazamiento (para paginación)
$sql .= " LIMIT :inicio, :limite";

// Preparar y ejecutar la consulta
$consulta = $db->prepare($sql);
if (!empty($busqueda_nombre)) {
    $param_busqueda = '%' . $busqueda_nombre . '%';
    $consulta->bindParam(':busqueda_nombre', $param_busqueda, PDO::PARAM_STR);
}
$consulta->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$consulta->bindParam(':limite', $limite, PDO::PARAM_INT);
$consulta->execute();
$videojuegos = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Contar total de videojuegos (para calcular el número de páginas)
$sql_count = "SELECT COUNT(*) as total FROM videojuegos WHERE 1 = 1";
if (!empty($busqueda_nombre)) {
    $sql_count .= " AND nombre LIKE :busqueda_nombre";
}
$consulta_count = $db->prepare($sql_count);
if (!empty($busqueda_nombre)) {
    $consulta_count->bindParam(':busqueda_nombre', $param_busqueda, PDO::PARAM_STR);
}
$consulta_count->execute();
$total_videojuegos = $consulta_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_videojuegos / $limite);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Videojuegos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .videojuego-card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 10px;
        margin-bottom: 20px;
      }
      .videojuego-img {
        height: 250px;
        object-fit: fill;
        border-radius: 10px 10px 0 0;
      }
      .videojuego-body {
        padding: 20px;
      }

      body{
          background-color: #eee;
        }
    </style>
</head>
<body>
  <div class="container">
    <?php if (isset($_GET['mensaje'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-center pb-1">
        <a href="../index_admin.php" class="btn btn-outline-danger mt-1">Atrás</a>
    </div>
    <h1 class="text-center">Gestionar Videojuegos</h1>

    <!-- Formulario de búsqueda -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Formulario de búsqueda centrado -->
        <form method="GET" action="gestion_videojuegos.php" class="d-flex align-items-center">
          <a href="gestion_videojuegos.php" class="btn btn-secondary text-white p-2 me-3">Atrás</a>
            <input type="text" name="busqueda" id="busqueda" class="form-control me-2" placeholder="Buscar videojuego" value="<?php echo htmlspecialchars($busqueda_nombre); ?>">
            <button type="submit" class="btn btn-primary me-3">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>

        <!-- Botón para agregar nuevo videojuego -->
        <a href="agregar_videojuego.php" class="btn btn-primary">Agregar Nuevo Videojuego</a>
    </div>

    <!-- Mostrar todos los videojuegos en formato de tarjetas -->
    <div class="row">
      <?php foreach ($videojuegos as $videojuego): ?>
        <div class="col-md-4">
          <div class="card videojuego-card">
            <?php
              $nombre_juego = htmlspecialchars($videojuego['nombre']);
              $ruta_imagen_base = '../../imagenes/' . $nombre_juego;
                
              // Esto es para chequear el formato de la imagen
              if (file_exists($ruta_imagen_base . '.jpg')) {
                  $nombre_imagen = $ruta_imagen_base . '.jpg';
              } elseif (file_exists($ruta_imagen_base . '.jpeg')) {
                  $nombre_imagen = $ruta_imagen_base . '.jpeg';
              } elseif (file_exists($ruta_imagen_base . '.png')) {
                  $nombre_imagen = $ruta_imagen_base . '.png';
              } else {
                  $nombre_imagen = '../../imagenes/imagen_default.png';
              }
            ?>
            <img src="<?php echo $nombre_imagen; ?>" alt="Imagen de <?php echo $videojuego['nombre']; ?>" class="card-img-top videojuego-img">
            <div class="card-body videojuego-body">
              <h5 class="card-title"><?php echo $videojuego['nombre']; ?></h5>
              <p class="card-text"><?php echo $videojuego['descripcion']; ?></p>
              <div class="d-flex justify-content-between">
                <a href="editar_videojuego.php?id=<?php echo $videojuego['ID']; ?>" class="btn btn-warning">Editar</a>
                <a href="javascript:void(0);" onclick="confirmarEliminacion('<?php echo $videojuego['ID']; ?>', '<?php echo $videojuego['nombre']; ?>')" class="btn btn-danger">Eliminar</a>
              </div>
              <div class="d-flex justify-content-center mt-2">
                <form method="POST" action="controlar_unidades.php?id=<?php echo $videojuego['ID']; ?>" class="d-inline">
                  <button type="submit" class="btn btn-success">Controlar Unidades</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($pagina_actual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="gestion_videojuegos.php?busqueda=<?php echo urlencode($busqueda_nombre); ?>&pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                    <a class="page-link" href="gestion_videojuegos.php?busqueda=<?php echo urlencode($busqueda_nombre); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="gestion_videojuegos.php?busqueda=<?php echo urlencode($busqueda_nombre); ?>&pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
  </div>
    
    
    
    <script>
      function confirmarEliminacion(videojuegoId, nombreVideojuego) {
        // Mensaje de confirmación
        if (confirm(`¿Estás seguro de que deseas eliminar el videojuego "${nombreVideojuego}" incluyendo todas sus unidades y relaciones con categorías y plataformas?`)) {
            // Redirigir a la página de eliminación con el ID del videojuego como parámetro
            window.location.href = `eliminar_videojuego.php?id=${videojuegoId}`;
        }
      }
    </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
