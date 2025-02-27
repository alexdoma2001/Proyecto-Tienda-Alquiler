<?php
// Iniciar la sesión
session_start();
require '../../conexion_be.php'; // Conexión a la base de datos

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

try {
    $query = "SELECT * FROM plataforma";
    $consulta = $db->prepare($query);
    $consulta->execute();
    $plataformas = $consulta->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $plataformaID = $_POST['plataforma_id'] ?? '';
    
    if (!empty($plataformaID)) {
        try {
            //Elimina primero la plataforma en las relaciones de n a n
            $queryEliminar = "DELETE FROM videojuegos_plataforma WHERE plataforma_id = :plataformaID";
            $consultaEliminar = $db->prepare($queryEliminar);
            $consultaEliminar->bindParam(':plataformaID', $plataformaID);
            $consultaEliminar->execute();

            // Eliminar la plataforma en si en su tabla
            $query = "DELETE FROM plataforma WHERE ID = :plataformaID";
            $consulta = $db->prepare($query);
            $consulta->bindParam(':plataformaID', $plataformaID);
            $consulta->execute();

            header("Location: gestion_plataformas.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_nombre') {
    $plataformaID = $_POST['plataforma_id'] ?? '';
    $nuevoNombre = $_POST['nuevo_nombre'] ?? '';

    if (!empty($plataformaID) && !empty($nuevoNombre)) {
        try {
            // Actualizar el nombre de la plataforma
            $query = "UPDATE plataforma SET nombre = :nuevoNombre WHERE ID = :plataformaID";
            $consulta = $db->prepare($query);
            $consulta->bindParam(':nuevoNombre', $nuevoNombre);
            $consulta->bindParam(':plataformaID', $plataformaID);
            $consulta->execute();

            header("Location: gestion_plataformas.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Plataformas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmarEliminacion(plataformaID) {
            if (confirm("¿Estás seguro de que deseas eliminar esta plataforma? Esto también eliminará su asociación con los videojuegos.")) {
                document.getElementById("eliminar_form_" + plataformaID).submit();
            }
        }

        function editarNombre(plataformaID) {
            var nombrePlataforma = document.getElementById("nombre_" + plataformaID);
            var editarBtn = document.getElementById("editar_" + plataformaID);
            var cancelarBtn = document.getElementById("cancelar_" + plataformaID);
            var confirmarBtn = document.getElementById("confirmar_" + plataformaID);

            nombrePlataforma.contentEditable = true;
            nombrePlataforma.style.backgroundColor = "#f0f0f0";
            editarBtn.style.display = 'none';
            cancelarBtn.style.display = 'inline-block';
            confirmarBtn.style.display = 'inline-block';
        }

        function cancelarEdicion(plataformaID) {
            var nombrePlataforma = document.getElementById("nombre_" + plataformaID);
            var editarBtn = document.getElementById("editar_" + plataformaID);
            var cancelarBtn = document.getElementById("cancelar_" + plataformaID);
            var confirmarBtn = document.getElementById("confirmar_" + plataformaID);

            nombrePlataforma.contentEditable = false;
            nombrePlataforma.style.backgroundColor = "";
            editarBtn.style.display = 'inline-block';
            cancelarBtn.style.display = 'none';
            confirmarBtn.style.display = 'none';
        }

        function confirmarCambio(plataformaID) {
            var nuevoNombre = document.getElementById("nombre_" + plataformaID).innerText;
            document.getElementById("nuevo_nombre_" + plataformaID).value = nuevoNombre;
            document.getElementById("cambiar_nombre_form_" + plataformaID).submit();
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-center pb-1">
        <a href="../index_admin.php" class="btn btn-outline-danger mt-1">Atrás</a>
    </div>
    <h1>Gestionar Plataformas</h1>
    
    <!-- Botón para agregar nueva plataforma -->
    <div class="mb-3">
        <a href="agregar_plataforma.php" class="btn btn-primary">Agregar Nueva Plataforma</a>
    </div>

    <!-- Tabla de plataformas -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de la Plataforma</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($plataformas) > 0): ?>
                <?php foreach ($plataformas as $plataforma): ?>
                    <tr>
                        <td><?= htmlspecialchars($plataforma['ID']) ?></td>
                        <td id="nombre_<?= $plataforma['ID'] ?>"><?= htmlspecialchars($plataforma['nombre']) ?></td>
                        <td>
                            <!-- Botones de Acciones -->
                            <button id="editar_<?= $plataforma['ID'] ?>" class="btn btn-warning" onclick="editarNombre(<?= $plataforma['ID'] ?>)">Cambiar nombre</button>
                            <button id="cancelar_<?= $plataforma['ID'] ?>" class="btn btn-secondary" style="display:none;" onclick="cancelarEdicion(<?= $plataforma['ID'] ?>)">Cancelar</button>
                            <button id="confirmar_<?= $plataforma['ID'] ?>" class="btn btn-success" style="display:none;" onclick="confirmarCambio(<?= $plataforma['ID'] ?>)">Confirmar cambio</button>
                            
                            <!-- Formulario para cambiar el nombre de la plataforma -->
                            <form id="cambiar_nombre_form_<?= $plataforma['ID'] ?>" action="gestion_plataformas.php" method="POST" style="display:none;">
                                <input type="hidden" name="accion" value="cambiar_nombre">
                                <input type="hidden" name="plataforma_id" value="<?= $plataforma['ID'] ?>">
                                <input type="hidden" id="nuevo_nombre_<?= $plataforma['ID'] ?>" name="nuevo_nombre">
                            </form>

                            <!-- Botón para eliminar la plataforma -->
                            <form id="eliminar_form_<?= $plataforma['ID'] ?>" action="gestion_plataformas.php" method="POST" style="display:none;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="plataforma_id" value="<?= $plataforma['ID'] ?>">
                            </form>
                            <button class="btn btn-danger" onclick="confirmarEliminacion(<?= $plataforma['ID'] ?>)">Eliminar plataforma</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No hay plataformas disponibles.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
