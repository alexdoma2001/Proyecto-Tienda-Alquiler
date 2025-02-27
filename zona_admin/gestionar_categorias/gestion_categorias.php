<?php
// Iniciar la sesión
session_start();
require '../../conexion_be.php'; // Conexión a la base de datos

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

// Consulta para obtener todas las categorias
try {
    $query = "SELECT * FROM categoria";
    $consulta = $db->prepare($query);
    $consulta->execute();
    $categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Manejar la eliminación de categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $categoriaID = $_POST['categoria_id'] ?? '';
    
    if (!empty($categoriaID)) {
        try {
            // Eliminar las asociaciones de la categoria con los videojuegos
            $queryEliminar = "DELETE FROM videojuegos_categoria WHERE categoria_id = :categoriaID";
            $consultaEliminar = $db->prepare($queryEliminar);
            $consultaEliminar->bindParam(':categoriaID', $categoriaID);
            $consultaEliminar->execute();

            // Eliminar la categoria
            $query = "DELETE FROM categoria WHERE ID = :categoriaID";
            $consulta = $db->prepare($query);
            $consulta->bindParam(':categoriaID', $categoriaID);
            $consulta->execute();

            header("Location: gestion_categorias.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Manejar el cambio de nombre de categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_nombre') {
    $categoriaID = $_POST['categoria_id'] ?? '';
    $nuevoNombre = $_POST['nuevo_nombre'] ?? '';

    if (!empty($categoriaID) && !empty($nuevoNombre)) {
        try {
            // Actualizar el nombre de la categoria
            $query = "UPDATE categoria SET nombre = :nuevoNombre WHERE ID = :categoriaID";
            $consulta = $db->prepare($query);
            $consulta->bindParam(':nuevoNombre', $nuevoNombre);
            $consulta->bindParam(':categoriaID', $categoriaID);
            $consulta->execute();

            header("Location: gestion_categorias.php");
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
    <title>Gestionar Categorias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmarEliminacion(categoriaID) {
            if (confirm("¿Estás seguro de que deseas eliminar esta categoria? Esto también eliminará su asociación con los videojuegos.")) {
                document.getElementById("eliminar_form_" + categoriaID).submit();
            }
        }

        function editarNombre(categoriaID) {
            var nombreCategoria= document.getElementById("nombre_" + categoriaID);
            var editarBtn = document.getElementById("editar_" + categoriaID);
            var cancelarBtn = document.getElementById("cancelar_" + categoriaID);
            var confirmarBtn = document.getElementById("confirmar_" + categoriaID);

            nombreCategoria.contentEditable = true;
            nombreCategoria.style.backgroundColor = "#f0f0f0";
            editarBtn.style.display = 'none';
            cancelarBtn.style.display = 'inline-block';
            confirmarBtn.style.display = 'inline-block';
        }

        function cancelarEdicion(categoriaID) {
            var nombreCategoria = document.getElementById("nombre_" + categoriaID);
            var editarBtn = document.getElementById("editar_" + categoriaID);
            var cancelarBtn = document.getElementById("cancelar_" + categoriaID);
            var confirmarBtn = document.getElementById("confirmar_" + categoriaID);

            nombreCategoria.contentEditable = false;
            nombreCategoria.style.backgroundColor = "";
            editarBtn.style.display = 'inline-block';
            cancelarBtn.style.display = 'none';
            confirmarBtn.style.display = 'none';
        }

        function confirmarCambio(categoriaID) {
            var nuevoNombre = document.getElementById("nombre_" + categoriaID).innerText;
            document.getElementById("nuevo_nombre_" + categoriaID).value = nuevoNombre;
            document.getElementById("cambiar_nombre_form_" + categoriaID).submit();
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-center pb-1">
        <a href="../index_admin.php" class="btn btn-outline-danger mt-1">Atrás</a>
    </div>
    <h1>Gestionar Categorias</h1>
    
    <!-- Botón para agregar nueva categoria -->
    <div class="mb-3">
        <a href="agregar_categorias.php" class="btn btn-primary">Agregar Nueva Categoria</a>
    </div>

    <!-- Tabla de categorias -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de la Categoria</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($categorias) > 0): ?>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?= htmlspecialchars($categoria['ID']) ?></td>
                        <td id="nombre_<?= $categoria['ID'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></td>
                        <td>
                            <!-- Botones de Acciones -->
                            <button id="editar_<?= $categoria['ID'] ?>" class="btn btn-warning" onclick="editarNombre(<?= $categoria['ID'] ?>)">Cambiar nombre</button>
                            <button id="cancelar_<?= $categoria['ID'] ?>" class="btn btn-secondary" style="display:none;" onclick="cancelarEdicion(<?= $categoria['ID'] ?>)">Cancelar</button>
                            <button id="confirmar_<?= $categoria['ID'] ?>" class="btn btn-success" style="display:none;" onclick="confirmarCambio(<?= $categoria['ID'] ?>)">Confirmar cambio</button>
                            
                            <!-- Formulario para cambiar el nombre de la categoria -->
                            <form id="cambiar_nombre_form_<?= $categoria['ID'] ?>" action="gestion_categorias.php" method="POST" style="display:none;">
                                <input type="hidden" name="accion" value="cambiar_nombre">
                                <input type="hidden" name="categoria_id" value="<?= $categoria['ID'] ?>">
                                <input type="hidden" id="nuevo_nombre_<?= $categoria['ID'] ?>" name="nuevo_nombre">
                            </form>

                            <!-- Botón para eliminar la cateogoria -->
                            <form id="eliminar_form_<?= $categoria['ID'] ?>" action="gestion_categorias.php" method="POST" style="display:none;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="categoria_id" value="<?= $categoria['ID'] ?>">
                            </form>
                            <button class="btn btn-danger" onclick="confirmarEliminacion(<?= $categoria['ID'] ?>)">Eliminar categoria</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No hay categoria disponibles.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
