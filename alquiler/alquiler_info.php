<?php
// Iniciar sesión y conectar a la base de datos
session_start();
include '../conexion_be.php';

$mensaje = "";
$claseAlerta = "";

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje']['texto'];
    $claseAlerta = $_SESSION['mensaje']['clase'];
    unset($_SESSION['mensaje']);
}

// Obtener el ID del videojuego desde el parámetro GET
$videojuego_id = isset($_GET['videojuego_id']) ? intval($_GET['videojuego_id']) : 0;

// Si el ID del videojuego no está presente, redirigir o mostrar un error
if ($videojuego_id == 0) {
    echo "<h1>Videojuego no encontrado</h1>";
    exit;
}

// Obtener información del videojuego
$consulta_videojuegos = "SELECT * FROM videojuegos WHERE ID = :videojuego_id";
$preparada = $db->prepare($consulta_videojuegos);
$preparada->bindParam(':videojuego_id', $videojuego_id, PDO::PARAM_INT);
$preparada->execute();
$videojuego = $preparada->fetch(PDO::FETCH_ASSOC);

// Verificar si el videojuego existe
if (!$videojuego) {
    echo "<h1>Videojuego no encontrado</h1>";
    exit;
}

// Obtener las plataformas disponibles para el videojuego
$consulta_plataformas = "SELECT p.nombre FROM plataforma p
                    INNER JOIN videojuegos_plataforma vp ON p.ID = vp.plataforma_id
                    WHERE vp.videojuego_id = :videojuego_id";
$preparada_plataformas = $db->prepare($consulta_plataformas);
$preparada_plataformas->bindParam(':videojuego_id', $videojuego_id, PDO::PARAM_INT);
$preparada_plataformas->execute();
$plataformas_videojuego = $preparada_plataformas->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay una plataforma seleccionada para obtener precio y stock
$precio = null;
$stock = null;
if (isset($_GET['plataforma']) && !empty($_GET['plataforma'])) {
    $plataforma_seleccionada = $_GET['plataforma'];

    // Obtener el precio y la disponibilidad para la plataforma seleccionada
    $consulta_precio = "SELECT vp.precio, vp.unidades FROM videojuegos_plataforma vp
                   INNER JOIN plataforma p ON vp.plataforma_id = p.ID
                   WHERE vp.videojuego_id = :videojuego_id AND p.nombre = :plataforma";
    $preparada_precio = $db->prepare($consulta_precio);
    $preparada_precio->bindParam(':videojuego_id', $videojuego_id, PDO::PARAM_INT);
    $preparada_precio->bindParam(':plataforma', $plataforma_seleccionada, PDO::PARAM_STR);
    $preparada_precio->execute();
    $resultado_precio = $preparada_precio->fetch(PDO::FETCH_ASSOC);

    if ($resultado_precio) {
        $precio = $resultado_precio['precio'];
        $stock = $resultado_precio['unidades'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Alquiler</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
            .gradient-form {
      background-color: #eee;
    }
    </style>
</head>
<body class="gradient-form">
    <div class="container mt-5">
        <div class="d-flex justify-content-center mb-4">
            <a href="../pagina_principal/index.php" class="btn btn-outline-danger">Volver a la página principal</a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert <?php echo $claseAlerta; ?>" role="alert" style="display: flex; align-items: center; justify-content: flex-start;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mt-5">
                        <?php
                        $nombre_imagen_base = '../imagenes/' . htmlspecialchars($videojuego['nombre']);
                        if (file_exists($nombre_imagen_base . '.jpg')) {
                            $nombre_imagen = $nombre_imagen_base . '.jpg';
                        } elseif (file_exists($nombre_imagen_base . '.jpeg')) {
                            $nombre_imagen = $nombre_imagen_base . '.jpeg';
                        } elseif (file_exists($nombre_imagen_base . '.png')) {
                            $nombre_imagen = $nombre_imagen_base . '.png';
                        } else {
                            $nombre_imagen = '../imagenes/imagen_default.png';
                        }
                        ?>
                        <img src="<?php echo $nombre_imagen; ?>" alt="<?php echo htmlspecialchars($videojuego['nombre']); ?>" class="img-fluid mb-3">
                    </div>

                    <div class="col-md-8">
                        <h2 class="card-title"><?php echo htmlspecialchars($videojuego['nombre']); ?></h2>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($videojuego['descripcion'])); ?></p>

                        <!-- Formulario para seleccionar plataforma con el videojuego_id en un campo oculto -->
                        <form method="GET" action="alquiler_info.php">
                            <input type="hidden" name="videojuego_id" value="<?php echo $videojuego_id; ?>">
                            <label for="plataforma">Seleccionar Plataforma:</label>
                            <select name="plataforma" id="plataforma" class="form-control mb-3" onchange="this.form.submit()">
                                <option value="">Seleccionar plataforma</option>
                                <?php foreach ($plataformas_videojuego as $plataforma): ?>
                                    <option value="<?php echo htmlspecialchars($plataforma['nombre']); ?>"
                                        <?php echo (isset($_GET['plataforma']) && $_GET['plataforma'] == $plataforma['nombre']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($plataforma['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <!-- Mostrar precio y disponibilidad para la plataforma seleccionada -->
                        <?php if (isset($precio) && isset($stock)): ?>
                            <div class="mt-3">
                                <p><strong>Precio por día:</strong><?php echo number_format($precio, 2); ?> €</p>
                                <?php if ($stock > 0): ?>
                                    <!-- Formulario de añadir al carrito -->
                                    <form method="POST" action="agregar_al_carrito.php">
                                        <input type="hidden" name="videojuego_id" value="<?php echo $videojuego_id; ?>">
                                        <input type="hidden" name="plataforma" value="<?php echo htmlspecialchars($plataforma_seleccionada); ?>">
                                        <button type="submit" class="btn btn-primary">Añadir al carrito</button>
                                    </form>
                                <?php else: ?>
                                    <p class="text-danger">Sin stock disponible</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Selecciona una plataforma para ver precio y disponibilidad.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>