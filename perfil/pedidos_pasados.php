<?php
include '../conexion_be.php';

session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}

// Inicializar variable
$pedidos_pasados = []; 


$cliente_id = $_SESSION['cliente_id'];

// Obtener el número de página actual
$pagina = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$cards_por_pagina = 4;

// Calcular el offset
$limite = ($pagina - 1) * $cards_por_pagina;

try {
    // Consultar alquileres pasados con paginación
    $consulta = "SELECT 
                a.ID AS alquiler_id,
                a.fecha_inicio,
                a.fecha_Final,
                a.precio_Final,
                COALESCE(m.valor_Multa, 0) AS multa,
                GROUP_CONCAT(CONCAT(v.nombre, ' (', p.nombre, ')') SEPARATOR ', ') AS videojuegos
            FROM alquiler a
            JOIN alquiler_videojuegos_plataforma avp ON a.ID = avp.alquiler_id
            JOIN videojuegos_plataforma vp ON avp.videojuego_plataforma_ID = vp.ID
            JOIN videojuegos v ON vp.videojuego_id = v.ID
            JOIN plataforma p ON vp.plataforma_id = p.ID
            LEFT JOIN multa m ON a.ID = m.alquiler_ID AND m.cliente_ID = :cliente_id
            WHERE a.cliente_id = :cliente_id AND a.estado = 'finalizado'
            GROUP BY a.ID
            ORDER BY a.fecha_Final DESC
            LIMIT :limit OFFSET :offset";

    $preparada = $db->prepare($consulta);
    $preparada ->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $preparada ->bindValue(':limit', $cards_por_pagina, PDO::PARAM_INT);
    $preparada ->bindValue(':offset', $limite, PDO::PARAM_INT);
    $preparada ->execute();
    $pedidos_pasados = $preparada ->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el número total de registros
    $consulta_count = "SELECT COUNT(*) 
                  FROM alquiler a 
                  WHERE a.cliente_id = :cliente_id AND a.estado = 'finalizado'";
    $preparada_total = $db->prepare($consulta_count);
    $preparada_total->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $preparada_total->execute();
    $total_pedidos = $preparada_total->fetchColumn();

    $total_pages = ceil($total_pedidos / $cards_por_pagina);
} catch (PDOException $e) {
    die("Error al obtener los pedidos pasados: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alquileres Pasados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gradient-form {
            background-color: #eee;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05); 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card img {
            height: 250px;
            object-fit: fill;
        }

        .btn-custom {
            background-color: #dd2476;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            color: white;
            background-color: #c02067;
        }
    </style>
</head>
<body class="gradient-form">
    <section>
        <div class="container py-5">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-lg-10">
                <div class="d-flex align-items-center justify-content-center pt-4">
                        <a href="info_pedidos.php" class="btn btn-outline-danger">Volver atrás</a>
                    </div>
                    <h1 class="text-center mb-4">Alquileres Pasados</h1>
             <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php if (!empty($pedidos_pasados)): ?>
                    <?php foreach ($pedidos_pasados as $pedido): ?>
                        <div class="col">
                            <div class="card">
                                <img src="../imagenes/logo_pagina.png" class="card-img-top" alt="Imagen predeterminada">
                                <div class="card-body">
                                    <h5 class="card-title">Pedido #<?php echo $pedido['alquiler_id']; ?></h5>
                                    <p class="card-text">
                                        <strong>Videojuegos:</strong> <?php echo $pedido['videojuegos']; ?><br>
                                        <strong>Fecha de inicio:</strong> <?php echo $pedido['fecha_inicio']; ?><br>
                                        <strong>Fecha de finalización:</strong> <?php echo $pedido['fecha_Final']; ?><br>
                                        <strong>Precio final:</strong> <?php echo number_format($pedido['precio_Final'], 2); ?>€<br>
                                        <?php if ($pedido['multa'] > 0): ?>
                                            <strong style="color: red;">Multa:</strong> <?php echo number_format($pedido['multa'], 2); ?> €
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No tienes alquileres pasados.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Barra de paginación -->
            <div class="pagination-container text-center mt-4">
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagina - 1; ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagina < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagina + 1; ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>


                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>