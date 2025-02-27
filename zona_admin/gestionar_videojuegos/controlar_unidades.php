<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

include '../../conexion_be.php';

// Obtener ID del videojuego y su nombre
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de videojuego no especificado.");
}

// Consulta para obtener el nombre del videojuego
$sql_nombre = "SELECT nombre FROM videojuegos WHERE ID = :id";
$consulta_nombre = $db->prepare($sql_nombre);
$consulta_nombre->bindParam(':id', $id, PDO::PARAM_INT);
$consulta_nombre->execute();
$nombre_videojuego = $consulta_nombre->fetch(PDO::FETCH_ASSOC)['nombre'] ?? "Videojuego Desconocido";

// Obtener plataformas en las que el videojuego tiene unidades
$sql = "SELECT p.nombre, vp.unidades, vp.plataforma_id 
        FROM plataforma AS p
        INNER JOIN videojuegos_plataforma AS vp ON p.id = vp.plataforma_id
        WHERE vp.videojuego_id = :id";
$consulta = $db->prepare($sql);
$consulta->bindParam(':id', $id, PDO::PARAM_INT);
$consulta->execute();
$plataformas_con_unidades = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Verificación de datos enviados por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['plataforma_id'], $_POST['cantidad'])) {
    $accion = $_POST['accion'];
    $plataforma_id = $_POST['plataforma_id'];
    $cantidad = intval($_POST['cantidad']);

    // Verificar acción y realizar la operación correspondiente
    switch ($accion) {
        case 'agregar':
            $sql_update = "UPDATE videojuegos_plataforma SET unidades = unidades + :cantidad 
                           WHERE videojuego_id = :id AND plataforma_id = :plataforma_id";
            break;

        case 'eliminar':
            // Obtener unidades actuales para la validación
            $consulta_verificar = $db->prepare("SELECT unidades FROM videojuegos_plataforma WHERE videojuego_id = :id AND plataforma_id = :plataforma_id");
            $consulta_verificar->bindParam(':id', $id, PDO::PARAM_INT);
            $consulta_verificar->bindParam(':plataforma_id', $plataforma_id, PDO::PARAM_INT);
            $consulta_verificar->execute();
            $unidades_actuales = $consulta_verificar->fetch(PDO::FETCH_ASSOC)['unidades'];

            if ($cantidad > $unidades_actuales) {
                echo "<script>alert('No se pueden restar {$cantidad} unidades, solo hay {$unidades_actuales} disponibles.');
                window.history.back();
                </script>";
                break;
            }

            $sql_update = "UPDATE videojuegos_plataforma SET unidades = unidades - :cantidad 
                           WHERE videojuego_id = :id AND plataforma_id = :plataforma_id";
            break;

        case 'fijar unidades':
            $sql_update = "UPDATE videojuegos_plataforma SET unidades = :cantidad 
                           WHERE videojuego_id = :id AND plataforma_id = :plataforma_id";
            break;

        default:
            die("Acción no válida.");
    }

    // Ejecutar actualización
    $consulta_update = $db->prepare($sql_update);
    $consulta_update->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta_update->bindParam(':plataforma_id', $plataforma_id, PDO::PARAM_INT);
    $consulta_update->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
    $consulta_update->execute();

    // Redireccionar para evitar reenviar el formulario
    header("Location: controlar_unidades.php?id=$id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Unidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-center pb-4">
        <a href="gestion_videojuegos.php" class="btn btn-outline-danger mt-4">Atrás</a>
    </div>
    <h2 class="text-center">Control de Unidades</h2>
    <h4>Videojuego: "<?php echo htmlspecialchars($nombre_videojuego); ?>"</h4>
    <?php if (empty($plataformas_con_unidades)): ?>
        <p>No hay plataformas con unidades disponibles para este videojuego.</p>
    <?php else: ?>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th>Unidades Disponibles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plataformas_con_unidades as $plataforma): ?>
                    <tr>
                        <td id="unidades_<?php echo htmlspecialchars($plataforma['nombre']);  ?>">
                            <?php echo $plataforma['nombre']; ?>
                        </td>
                        <td id="unidades_<?php echo htmlspecialchars($plataforma['unidades']);  ?>">
                            <?php echo $plataforma['unidades']; ?>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm me-1" 
                                    onclick="mostrarFormulario('agregar', <?php echo $plataforma['plataforma_id']; ?>, <?php echo $plataforma['unidades']; ?>)">Agregar</button>
                            <button class="btn btn-danger btn-sm me-1" 
                                    onclick="mostrarFormulario('eliminar', <?php echo $plataforma['plataforma_id']; ?>, <?php echo $plataforma['unidades']; ?>)">Eliminar</button>
                            <button class="btn btn-warning btn-sm" 
                                    onclick="mostrarFormulario('fijar unidades', <?php echo $plataforma['plataforma_id']; ?>, <?php echo $plataforma['unidades']; ?>)">Fijar Unidades</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Formulario dinámico para operaciones -->
        <form id="formulario_cantidad" method="POST" class="mt-3 d-none">
            <input type="hidden" name="plataforma_id" id="plataforma_id">
            <input type="hidden" name="accion" id="accion">
            <div class="mb-3">
                <label id="label_cantidad" for="cantidad" class="form-label">Cantidad:</label>
                <input type="number" min="0" class="form-control" id="cantidad" name="cantidad" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-secondary" onclick="ocultarFormulario()">Cancelar</button>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function mostrarFormulario(accion, plataforma_id, unidades_actuales) {
        document.getElementById('formulario_cantidad').classList.remove('d-none');
        document.getElementById('accion').value = accion;
        document.getElementById('plataforma_id').value = plataforma_id;

        const labelCantidad = document.getElementById('label_cantidad');
        const inputCantidad = document.getElementById('cantidad');
        
        if (accion === 'agregar') {
            labelCantidad.innerText = 'Cantidad a agregar:';
            inputCantidad.value = '';
            inputCantidad.min = 0;
        } else if (accion === 'eliminar') {
            labelCantidad.innerText = 'Cantidad a eliminar:';
            inputCantidad.value = '';
            inputCantidad.min = 0;
        } else if (accion === 'fijar unidades') {
            labelCantidad.innerText = 'Cantidad total:';
            inputCantidad.value = unidades_actuales;
            inputCantidad.min = 0;
        }
    }

    function ocultarFormulario() {
        document.getElementById('formulario_cantidad').classList.add('d-none');
        document.getElementById('cantidad').value = '';
    }
</script>
</body>
</html>
