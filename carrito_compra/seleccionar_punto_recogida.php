<?php
session_start();
include '../conexion_be.php';

$error = ""; 

// Configuración de la paginación
$direcciones_por_pagina = 10; // Puedes cambiar este valor según tus necesidades
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $direcciones_por_pagina;

// Obtener el término de búsqueda si está definido
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Redirigir si no hay cliente conectado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}

//Esto es para que no puedan acceder desde la url si el carrito de la compra esta vacio
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../pagina_principal/index.php");
}

if (!isset($_SESSION['fecha_inicio'], $_SESSION['fecha_final'], $_SESSION['dias_alquiler'])) {
    header("Location: seleccionar_fechas.php");
    exit();
}

// Procesar selección del punto de recogida
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['punto_recogida_id'])) {
        $punto_recogida_id = intval($_POST['punto_recogida_id']);
        $_SESSION['punto_recogida'] = $punto_recogida_id;
        header("Location: confirmar_alquiler.php");
        exit();
    } else {
        $error = "Debes seleccionar un punto de recogida.";
    }
}

// Consultar total de puntos de recogida con filtro de búsqueda
$total_puntos_recogida_filtro = "SELECT COUNT(*) FROM punto_recogida WHERE 
    LOWER(nombre) LIKE :busqueda OR 
    LOWER(direccion) LIKE :busqueda OR 
    LOWER(ciudad) LIKE :busqueda OR 
    LOWER(codigo_Postal) LIKE :busqueda";
$total_consulta = $db->prepare($total_puntos_recogida_filtro);
$total_consulta->bindValue(':busqueda', '%' . strtolower($busqueda) . '%', PDO::PARAM_STR);
$total_consulta->execute();
$total_direcciones = $total_consulta->fetchColumn();
$total_paginas = ceil($total_direcciones / $direcciones_por_pagina);

// Consultar puntos de recogida para la página actual
$total_puntos_recogida = "SELECT * FROM punto_recogida WHERE 
    LOWER(nombre) LIKE :busqueda OR 
    LOWER(direccion) LIKE :busqueda OR 
    LOWER(ciudad) LIKE :busqueda OR 
    LOWER(codigo_Postal) LIKE :busqueda
    LIMIT :inicio, :direcciones_por_pagina";
$consulta = $db->prepare($total_puntos_recogida);
$consulta->bindValue(':busqueda', '%' . strtolower($busqueda) . '%', PDO::PARAM_STR);
$consulta->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$consulta->bindParam(':direcciones_por_pagina', $direcciones_por_pagina, PDO::PARAM_INT);
$consulta->execute();
$puntos_recogida = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Punto de Recogida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<div class="d-flex align-items-center justify-content-center pb-2">
        <a href="seleccionar_fechas.php" class="btn btn-outline-danger mt-2">Atrás</a>
    </div>
    <h2 class="text-center">Seleccionar Punto de Recogida</h2>

    <!-- Mostrar error si no se seleccionó un punto de recogida -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="seleccionar_punto_recogida.php" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="busqueda" placeholder="Buscar por Nombre, Dirección, Ciudad o Código Postal" value="<?php echo htmlspecialchars($busqueda); ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a href="seleccionar_punto_recogida.php" class="btn btn-secondary">Borrar</a>
        </div>
    </form>

    <!-- Formulario para seleccionar punto de recogida -->
    <form method="POST" action="seleccionar_punto_recogida.php">
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Ciudad</th>
                    <th>Código Postal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($puntos_recogida) > 0): ?>
                    <?php foreach ($puntos_recogida as $punto): ?>
                        <tr>
                            <td>
                                <input 
                                    type="radio" 
                                    name="punto_recogida_id" 
                                    value="<?php echo htmlspecialchars($punto['ID']); ?>" 
                                    required>
                            </td>
                            <td><?php echo htmlspecialchars($punto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($punto['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($punto['ciudad']); ?></td>
                            <td><?php echo htmlspecialchars($punto['codigo_Postal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No se encontraron puntos de recogida.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success">Confirmar Punto de Recogida</button>
            <a href="carrito.php" class="btn btn-secondary">Volver al Carrito</a>
        </div>

        <!-- Paginación -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($pagina_actual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Anterior</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
