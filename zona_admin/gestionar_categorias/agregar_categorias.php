<?php
// Iniciar sesión y conexión a la base de datos
session_start();
require '../../conexion_be.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

$nombreCategoria = '';
$juegosSeleccionados = isset($_POST['juegosSeleccionados']) ? explode(',', $_POST['juegosSeleccionados']) : [];
$errorMessage = '';  // Variable para el mensaje de error

// Manejar el envío del formulario para guardar la categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $nombreCategoria = $_POST['nombre'] ?? '';
    $juegosSeleccionados = isset($_POST['juegos']) ? $_POST['juegos'] : [];

    // Verificar si se ingresó un nombre y si se seleccionó al menos un videojuego
    if (empty($nombreCategoria)) {
        $errorMessage = 'Por favor, ingresa un nombre para la categoría.';  // Mensaje si el nombre está vacío
    } elseif (empty($juegosSeleccionados)) {
        $errorMessage = 'Debes seleccionar al menos un videojuego.';  // Mensaje si no se seleccionó ningún videojuego
    } else {
        try {
            // Insertar la categoría
            $query = "INSERT INTO categoria (nombre) VALUES (:nombre)";
            $consulta = $db->prepare($query);
            $consulta->bindParam(':nombre', $nombreCategoria);
            $consulta->execute();
            $categoriaID = $db->lastInsertId();

            // Asociar videojuegos seleccionados con la categoría
            $queryVideojuego = "INSERT INTO videojuegos_categoria (categoria_id, videojuego_id) VALUES (:categoriaID, :videojuegoID)";
            $consultaVideojuego = $db->prepare($queryVideojuego);

            foreach ($juegosSeleccionados as $videojuegoID) {
                $consultaVideojuego->execute([
                    ':categoriaID' => $categoriaID,
                    ':videojuegoID' => $videojuegoID,
                ]);
            }

            // Redirigir solo si no hay errores
            header('Location: gestion_categorias.php');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}


// Consulta para obtener todos los videojuegos
$queryVideojuegos = "SELECT * FROM videojuegos ORDER BY nombre ASC";
$consultaVideojuegos = $db->prepare($queryVideojuegos);
$consultaVideojuegos->execute();
$Videojuegos = $consultaVideojuegos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-center pb-4">
            <a href="gestion_categorias.php" class="btn btn-outline-danger mt-4">Atrás</a>
        </div>

        <h1>Agregar Categoría</h1>

        <!-- Mostrar mensaje de error si existe -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Formulario para agregar la categoría -->
        <form method="POST" action="">
            <div class="mb-3">
                <h5>Selecciona los videojuegos para esta categoría</h5>
                <div style="max-height: 500px; overflow-y: scroll; border: 1px solid #dee2e6; border-radius: 5px;">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Seleccionar</th>
                                <th>Nombre del Videojuego</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Videojuegos as $videojuego): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="juegos[]" value="<?= htmlspecialchars($videojuego['ID']) ?>"
                                        <?= in_array($videojuego['ID'], $juegosSeleccionados) ? 'checked' : ''; ?>>
                                    </td>
                                    <td><?= htmlspecialchars($videojuego['nombre']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Nombre de la Categoría</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Introduce el nombre de la categoría" value="<?= htmlspecialchars($nombreCategoria); ?>" required>
            </div>

            <!-- Input oculto para agregar los videojuegos seleccionados -->
            <input type="hidden" name="juegosSeleccionados" id="juegosSeleccionados" value="">

<button type="submit" class="btn btn-success" name="accion" value="guardar">Guardar Categoría</button>
        </form>
    </div>

    <script>
    // Antes de enviar el formulario, recoger los videojuegos seleccionados
    document.getElementById("formCategoria").onsubmit = function() {
    var juegosSeleccionados = [];
    document.querySelectorAll('input[name="juegos[]"]:checked').forEach(function(checkbox) {
        juegosSeleccionados.push(checkbox.value);
    });
    // Actualizar el campo oculto con los valores seleccionados
    document.getElementById('juegosSeleccionados').value = juegosSeleccionados.join(',');
};
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
