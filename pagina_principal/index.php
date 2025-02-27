<?php
// Iniciar sesión y conectar la base de datos como siempre
session_start();
include '../conexion_be.php';
include 'categorias.php';
include 'plataformas.php';
include 'filtros.php';

function obtenerPlataformasPorVideojuego($videojuegoId, $db) {
    $consulta = "SELECT p.nombre FROM plataforma p
            INNER JOIN videojuegos_plataforma vp ON p.ID = vp.plataforma_id
            WHERE vp.videojuego_id = :videojuego_id";

    $preparada = $db->prepare($consulta);
    $preparada->bindParam(':videojuego_id', $videojuegoId, PDO::PARAM_INT);
    $preparada->execute();
    return $preparada->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Videojuegos</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center logo-container mx-auto mx-lg-0">
            <a href="index.php" class="d-flex align-items-center text-decoration-none">
                <img src="../imagenes/logo_pagina.png" alt="Logo Cheap Games" style="width: 50px; height: 50px;" class="me-2">
                <h2 class="navbar-brand mb-0">Cheap Games</h2>
            </a>
        </div>
        <h1 class="text-center flex-grow-1" style="font-size: 1.75rem; text-align: center;">Catálogo de Videojuegos</h1>
        <div class="d-flex ms-auto">
            <?php if (!isset($_SESSION['cliente_id'])): ?>
                <span class="me-2 align-self-center hide-on-sm">¿Todavía no tienes cuenta? ¡Regístrate!</span>
                <a href="../registro/registro.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-user-plus"></i> Registrarse
                </a>
                <a href="../inicio_sesion/inicio_Sesion.php" class="btn btn-outline-success">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
            <?php else: ?>
                <a href="../perfil/perfil.php" class="btn btn-outline-primary me-2">
                     <i class="fas fa-user"></i> Perfil de <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </a>
                <a href="../carrito_compra/carrito.php" class="btn btn-outline-success me-2">
                    <i class="fas fa-shopping-cart"></i> Carrito
                </a>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Formulario para filtros y búsqueda -->
<form method="GET" action="index.php" class="gradient text-white d-flex flex-column flex-sm-row justify-content-between">
    <!-- Botón para abrir el menú desplegable de filtros -->
    <div class="form-group">
        <button type="button" id="toggleFilters" class="btn btn-primary me-3">
            <i class="fas fa-filter"></i> Filtrar Videojuegos >>
        </button>
    </div>

    <!-- Filtro de búsqueda por nombre siempre visible -->
    <div class="form-group d-flex align-items-center mb-3 ">
        <a href="index.php" class="btn btn-primary text-white p-2 me-3">Atrás</a>
        <label for="busqueda" class="me-2 d-none d-md-block">Buscar por Nombre:</label>
        <input type="text" name="busqueda" id="busqueda" class="form-control" placeholder="Buscar videojuego" value="<?php echo htmlspecialchars($busqueda_nombre); ?>">
        <button type="submit" class="btn btn-primary ms-2">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>
</form>

<!-- Contenedor de los filtros, inicialmente oculto -->
<div id="filterContainer" class="filter-sidebar">
    <div class="filter-header">
        <button type="button" id="closeFilters" class="btn btn-danger">
            << Cerrar Filtros
        </button>
    </div>

    <div class="filter-content">
        <form method="GET" action="index.php">
            <!-- Pasar un parámetro oculto para mantener el filtro abierto -->
            <input type="hidden" name="categoria" value="<?php echo $categoria_seleccionada; ?>">
            <input type="hidden" name="plataformas" value="<?php echo $plataforma_seleccionada; ?>">
            <input type="hidden" name="busqueda" value="<?php echo htmlspecialchars($busqueda_nombre); ?>">
            <input type="hidden" name="pagina" value="<?php echo $pagina_actual; ?>">
            <input type="hidden" name="videojuego_id" value="<?php echo $videojuego['ID']; ?>">

            <div class="form-group">
                <label for="categoria">Filtrar por Categoría:</label>
                <select name="categoria" id="categoria" class="form-control">
                    <option value="0">Seleccionar Categoría (Todos)</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['ID']; ?>" <?php if ($categoria['ID'] == $categoria_seleccionada) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="plataformas">Filtrar por Plataformas:</label>
                <select name="plataformas" id="plataformas" class="form-control">
                    <option value="0">Seleccionar Plataformas (Todos)</option>
                    <?php foreach ($plataformas as $plataforma): ?>
                        <option value="<?php echo $plataforma['ID']; ?>" <?php if ($plataforma['ID'] == $plataforma_seleccionada) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($plataforma['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
        </form>
    </div>
</div>
<div class="container-fluid parte-videojuegos">
    <div class="row">
        <?php
        if (count($videojuegos) > 0) {
            foreach ($videojuegos as $videojuego) {
                $nombre_juego = htmlspecialchars($videojuego['nombre']);
                $ruta_imagen_base = '../imagenes/' . $nombre_juego;
                
                // Verificar el formato de la imagen
                if (file_exists($ruta_imagen_base . '.jpg')) { //© Square Enix, © Activision, © Santa Monica, © Nintendo, ©Open Ai
                    $nombre_imagen = $ruta_imagen_base . '.jpg';
                } elseif (file_exists($ruta_imagen_base . '.jpeg')) {
                    $nombre_imagen = $ruta_imagen_base . '.jpeg';
                } elseif (file_exists($ruta_imagen_base . '.png')) {
                    $nombre_imagen = $ruta_imagen_base . '.png';
                } else {
                    $nombre_imagen = '../imagenes/imagen_default.png';
                }

                // Obtener plataformas para este videojuego
                $plataformas_videojuego = obtenerPlataformasPorVideojuego($videojuego['ID'], $db);
                $nombres_plataformas = array_column($plataformas_videojuego, 'nombre');
                
                echo '
                <div class="col-lg-3 col-md-6 col-sm-12 d-flex align-items-stretch">
                    <div class="card mb-5">
                        <img src="' . $nombre_imagen . '" class="card-img-top" alt="' . $nombre_juego .  ' alt=""">
                        <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($videojuego['nombre']) . '</h5>                     
                            <!-- Mostrar plataformas -->
                            <p><strong>Plataformas:</strong> ' . implode(', ', $nombres_plataformas) . '</p>
                            
                            <!-- Botón para consultar información sobre alquiler -->
                            <form method="GET" action="../alquiler/alquiler_info.php">
                                <input type="hidden" name="videojuego_id" value="' . $videojuego['ID'] . '">
                                <button type="submit" class="btn btn-primary mt-2">Consultar información sobre el producto</button>
                            </form>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="col-12 text-white text-center"><h3>No hay resultados en su búsqueda.</h3>
            <img src="../imagenes/mando_roto.png" alt="Sin resultados"></div>';
        }
        ?>
    </div>
</div>



    <!-- Paginacion -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($pagina_actual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="index.php?categoria=<?php echo $categoria_seleccionada; ?>&plataforma=<?php echo $plataforma_seleccionada; ?>&busqueda=<?php echo urlencode($busqueda_nombre); ?>&pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                    <a class="page-link" href="index.php?categoria=<?php echo $categoria_seleccionada; ?>&plataforma=<?php echo $plataforma_seleccionada; ?>&busqueda=<?php echo urlencode($busqueda_nombre); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="index.php?categoria=<?php echo $categoria_seleccionada; ?>&plataforma=<?php echo $plataforma_seleccionada; ?>&busqueda=<?php echo urlencode($busqueda_nombre); ?>&pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
