<?php

include '../conexion_be.php';


session_start();


if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}

function obtenerVideojuegosConPlataformas($alquiler_id, $db) {
    try {
        $sql = "SELECT v.nombre AS juego, p.nombre AS plataforma
                FROM alquiler_videojuegos_plataforma avp
                INNER JOIN videojuegos_plataforma vp ON avp.videojuego_plataforma_ID = vp.ID
                INNER JOIN videojuegos v ON vp.videojuego_id = v.ID
                INNER JOIN plataforma p ON vp.plataforma_id = p.ID
                WHERE avp.alquiler_ID = :alquiler_id";
        
        $consulta = $db->prepare($sql);
        $consulta->bindParam(':alquiler_id', $alquiler_id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}


function obtenerPuntoRecogida($alquiler_id, $db) {
    try {
        $sql = "SELECT p.direccion, p.ciudad, p.codigo_Postal
                FROM alquiler a
                INNER JOIN punto_recogida p ON a.punto_Recogida = p.ID
                WHERE a.ID = :alquiler_id";
        $consulta = $db->prepare($sql);
        $consulta->bindParam(':alquiler_id', $alquiler_id, PDO::PARAM_INT);
        $consulta->execute();

        $punto_Recogida = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($punto_Recogida) {
            return $punto_Recogida['direccion'] . ", " . $punto_Recogida['ciudad'] . " (" . $punto_Recogida['codigo_Postal'] . ")";
        } else {
            return "Punto de recogida no encontrado.";
        }
    } catch (PDOException $e) {
        return "Error al obtener el punto de recogida: " . $e->getMessage();
    }
}

$cliente_id = $_SESSION['cliente_id'];

$alquileres_por_pagina = 4;

// Obtener la página actual
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $alquileres_por_pagina;

try {
    $sql_total = "SELECT COUNT(*) FROM alquiler WHERE cliente_id = :cliente_id AND estado = '1'";
    $consulta_total = $db->prepare($sql_total);
    $consulta_total->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $consulta_total->execute();
    $total_alquileres = $consulta_total->fetchColumn();
    $total_paginas = ceil($total_alquileres / $alquileres_por_pagina); // Calcular el número total de páginas
} catch (PDOException $e) {
    die("Error en la consulta total: " . $e->getMessage());
}

// Consultar los alquileres activos del cliente
$alquileres_activos = [];
try {
    $sql = "SELECT * FROM alquiler WHERE cliente_id = :cliente_id AND estado = '1' LIMIT :offset, :alquileres_por_pagina";
    $consulta = $db->prepare($sql);
    $consulta->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $consulta->bindParam(':offset', $offset, PDO::PARAM_INT);
    $consulta->bindParam(':alquileres_por_pagina', $alquileres_por_pagina, PDO::PARAM_INT);
    $consulta->execute();
    $alquileres_activos = $consulta->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alquileres Activos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gradient-form {
            background-color: #eee;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05); /* Efecto de aumento */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Sombra */
        }
    </style>
</head>
<body class="gradient-form">
    <div class="container py-5">
    <div class="text-center mb-2">
            <a href="info_pedidos.php" class="btn btn-secondary">Volver</a>
        </div>
        <h1 class="text-center mb-4">Tus Alquileres Activos</h1>

        <?php if (count($alquileres_activos) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($alquileres_activos as $alquiler): ?>
                    <?php
                   $videojuegosConPlataformas = obtenerVideojuegosConPlataformas($alquiler['ID'], $db);
                    $punto_Recogida = obtenerPuntoRecogida($alquiler['ID'], $db);
                    ?>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                            <h5 class="card-title">Juegos Alquilados:</h5>
                                <ul>
                                    <?php foreach ($videojuegosConPlataformas as $juego): ?>
                                        <li><?php echo htmlspecialchars($juego['juego']) . ' (' . htmlspecialchars($juego['plataforma']) . ')'; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p class="card-text">
                                    <strong>Fecha Inicio:</strong> <?php echo htmlspecialchars($alquiler['fecha_Inicio']); ?><br>
                                    <strong>Dias alquilados:</strong> <?php echo htmlspecialchars($alquiler['dias_Alquiler']); ?><br>
                                    <strong>Punto de Recogida:</strong> <?php echo htmlspecialchars($punto_Recogida); ?>
                                </p>
                                <a href="detalles_pedido.php?pedido_id=<?php echo htmlspecialchars($alquiler['ID']); ?>" class="btn btn-primary">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-center mt-4">
                <ul class="pagination">
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
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No tienes pedidos activos en este momento.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
