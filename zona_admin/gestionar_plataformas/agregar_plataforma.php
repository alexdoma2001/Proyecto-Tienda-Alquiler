<?php
// Iniciar sesión y conexión a la base de datos
session_start();
require '../../conexion_be.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

$nombrePlataforma = '';
$juegosSeleccionados = isset($_POST['juegosSeleccionados']) ? json_decode($_POST['juegosSeleccionados'], true) : [];

// Manejar el envío del formulario para guardar la plataforma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $nombrePlataforma = $_POST['nombre'] ?? '';

    if (!empty($nombrePlataforma) && !empty($juegosSeleccionados)) {
        try {
            // Insertar la plataforma
            $query = "INSERT INTO plataforma (nombre) VALUES (:nombre)";
            $consulta = $db->prepare($query);
            $consulta->bindParam(':nombre', $nombrePlataforma);
            $consulta->execute();
            $plataformaID = $db->lastInsertId();

            // Asociar videojuegos seleccionados con la plataforma
            $queryVideojuego = "INSERT INTO videojuegos_plataforma (plataforma_id, videojuego_id, precio, unidades) VALUES (:plataformaID, :videojuegoID, :precio, :unidades)";
            $consultaVideojuego = $db->prepare($queryVideojuego);

            foreach ($juegosSeleccionados as $videojuego) {
                $consultaVideojuego->execute([
                    ':plataformaID' => $plataformaID,
                    ':videojuegoID' => $videojuego['id'],
                    ':precio' => $videojuego['precio'],
                    ':unidades' => $videojuego['unidades'],
                ]);
            }

            header('Location: gestion_plataformas.php');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<div class='alert alert-danger'>Por favor, ingresa un nombre para la plataforma y selecciona al menos un videojuego con precio y unidades.</div>";
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
    <title>Agregar Plataforma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-center pb-4">
            <a href="gestion_plataformas.php" class="btn btn-outline-danger mt-4">Atrás</a>
        </div>

        <h1>Agregar Plataforma</h1>

        <!-- Formulario para agregar la plataforma -->
        <form method="POST" action="" onsubmit="return procesarSeleccion();" id="formPlataforma">
            <div class="mb-3">
                <h5>Selecciona los videojuegos para esta plataforma</h5>
                <div style="max-height: 500px; overflow-y: scroll; border: 1px solid #dee2e6; border-radius: 5px;">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Seleccionar</th>
                                <th>Nombre del Videojuego</th>
                                <th>Precio</th>
                                <th>Unidades</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Videojuegos as $videojuego): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="videojuego-checkbox" value="<?= htmlspecialchars($videojuego['ID']) ?>" data-id="<?= htmlspecialchars($videojuego['ID']) ?>">
                                    </td>
                                    <td><?= htmlspecialchars($videojuego['nombre']) ?></td>
                                    <td>
                                        <input type="number" class="form-control precio-input" data-id="<?= htmlspecialchars($videojuego['ID']) ?>" placeholder="Precio" step="0.01" disabled>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control unidades-input" data-id="<?= htmlspecialchars($videojuego['ID']) ?>" placeholder="Unidades" min="0" disabled>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Nombre de la Plataforma</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Introduce el nombre de la plataforma" required>
            </div>

            <!-- Input oculto para agregar los videojuegos seleccionados -->
            <input type="hidden" name="juegosSeleccionados" id="juegosSeleccionados">

            <button type="submit" class="btn btn-success" name="accion" value="guardar">Guardar Plataforma</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Habilitar/deshabilitar inputs de precio y unidades según el checkbox
        document.querySelectorAll('.videojuego-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const id = this.dataset.id;
                const precioInput = document.querySelector(`.precio-input[data-id="${id}"]`);
                const unidadesInput = document.querySelector(`.unidades-input[data-id="${id}"]`);

                if (this.checked) {
                    precioInput.disabled = false;
                    unidadesInput.disabled = false;
                } else {
                    precioInput.value = '';
                    unidadesInput.value = '';
                    precioInput.disabled = true;
                    unidadesInput.disabled = true;
                }
            });
        });

        // Preparar los datos seleccionados para enviarlos al servidor
        function procesarSeleccion() {
            const seleccionados = [];
            document.querySelectorAll('.videojuego-checkbox:checked').forEach(checkbox => {
                const id = checkbox.dataset.id;
                const precio = document.querySelector(`.precio-input[data-id="${id}"]`).value;
                const unidades = document.querySelector(`.unidades-input[data-id="${id}"]`).value;

                if (!precio || !unidades) {
                    return false;
                }

                seleccionados.push({ id, precio, unidades });
            });

            document.getElementById('juegosSeleccionados').value = JSON.stringify(seleccionados);
            return true;
        }
    </script>
</body>

</html>
