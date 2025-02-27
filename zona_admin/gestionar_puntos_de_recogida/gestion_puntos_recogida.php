<?php
// Iniciar la sesión
session_start();
require '../../conexion_be.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

$direcciones_por_pagina = 15;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $direcciones_por_pagina;

// Obtener el término de búsqueda si se esta utilizando
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Consulta para realizar la paginación. Se cuenta primero los puntos de recogida que coinciden con la busqueda y calcula cuantas paginas van a ser necesarias
$consulta_paginacion = "SELECT COUNT(*) FROM punto_recogida WHERE 
    LOWER(nombre) LIKE :busqueda OR 
    LOWER(direccion) LIKE :busqueda OR 
    LOWER(ciudad) LIKE :busqueda OR 
    LOWER(codigo_Postal) LIKE :busqueda";
$total_consulta = $db->prepare($consulta_paginacion);
$total_consulta->bindValue(':busqueda', '%' . strtolower($busqueda) . '%', PDO::PARAM_STR);
$total_consulta->execute();
$total_direcciones = $total_consulta->fetchColumn();
$total_paginas = ceil($total_direcciones / $direcciones_por_pagina);

// Selecciona la pagina actual y muestra los datos especificos en la página actual
$consulta_total = "SELECT * 
FROM punto_recogida 
WHERE 
    LOWER(nombre) LIKE :busqueda OR 
    LOWER(direccion) LIKE :busqueda OR 
    LOWER(ciudad) LIKE :busqueda OR 
    LOWER(codigo_Postal) LIKE :busqueda
    LIMIT :inicio, :direcciones_por_pagina";
$consulta = $db->prepare($consulta_total);
$consulta->bindValue(':busqueda', '%' . strtolower($busqueda) . '%', PDO::PARAM_STR);
$consulta->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$consulta->bindParam(':direcciones_por_pagina', $direcciones_por_pagina, PDO::PARAM_INT);
$consulta->execute();
$resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Puntos de Recogida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-center pb-2">
        <a href="../index_admin.php" class="btn btn-outline-danger mt-2">Atrás</a>
    </div>
    <h1>Gestionar Puntos de Recogida</h1>

    <form method="GET" action="gestion_puntos_recogida.php" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="busqueda" placeholder="Buscar por Nombre, Dirección, Ciudad o Código Postal" value="<?php echo htmlspecialchars($busqueda); ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a href="gestion_puntos_recogida.php" class="btn btn-secondary">Borrar</a>
        </div>
    </form>

    <!-- Botón para agregar un nuevo punto de recogida -->
    <div class="mb-3">
        <a href="agregar_punto_recogida.php" class="btn btn-primary">Agregar Nuevo Punto de Recogida</a>
    </div>

    <!-- Tabla de puntos de recogida -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Direccion</th>
                <th>Ciudad</th>
                <th>Codigo Postal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($resultado as $punto): ?>
                <tr>
                    <td><?= htmlspecialchars($punto['ID']) ?></td>
                    <td><input type="text" id="nombre_<?= $punto['ID'] ?>" value="<?= htmlspecialchars($punto['nombre']) ?>" class="form-control" disabled></td>
                    <td><input type="text" id="direccion_<?= $punto['ID'] ?>" value="<?= htmlspecialchars($punto['direccion']) ?>" class="form-control" disabled></td>
                    <td><input type="text" id="ciudad_<?= $punto['ID'] ?>" value="<?= htmlspecialchars($punto['ciudad']) ?>" class="form-control" disabled></td>
                    <td><input type="text"  id="codigo_postal_<?= $punto['ID'] ?>" value="<?= htmlspecialchars($punto['codigo_Postal']) ?>" class="form-control" disabled></td>
                    <td>
                        <button id="editar_<?= $punto['ID'] ?>" class="btn btn-warning" onclick="editarPunto(<?= $punto['ID'] ?>)">Editar</button>
                        <button id="confirmar_<?= $punto['ID'] ?>" class="btn btn-success" style="display: none;" onclick="confirmarCambio(<?= $punto['ID'] ?>)">Confirmar cambio</button>
                        <a href="eliminar_punto_recogida.php?ID=<?= htmlspecialchars($punto['ID']) ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este punto de recogida?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($pagina_actual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
