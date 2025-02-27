<?php
// Incluir conexión a la base de datos
include '../conexion_be.php';

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}

// Verificar si se ha proporcionado el ID del pedido
if (!isset($_GET['pedido_id'])) {
    die("No se proporcionó un ID de pedido válido.");
}

$pedido_id = (int) $_GET['pedido_id'];
$precio_total = 0;
$dias_retraso = 0;
$multa_total = 0;
$precio_suma_juegos = 0;
$fecha_limite_entrega = null;

try {
    // Consultar los datos del pedido
    $sql = "SELECT 
        v.nombre AS nombre,
        p.nombre AS plataforma,
        avp.unidades AS unidades, 
        vp.precio AS precio_unitario,
        a.ID as ID,
        a.fecha_inicio AS fecha_inicio,
        a.dias_alquiler AS dias_alquiler,
        pr.nombre AS punto_recogida
        FROM alquiler_videojuegos_plataforma avp
        INNER JOIN videojuegos_plataforma vp ON avp.videojuego_plataforma_ID = vp.ID
        INNER JOIN videojuegos v ON vp.videojuego_id = v.ID
        INNER JOIN plataforma p ON vp.plataforma_id = p.ID
        INNER JOIN alquiler a ON avp.alquiler_ID = a.ID
        INNER JOIN punto_recogida pr ON a.punto_recogida = pr.ID
        WHERE avp.alquiler_ID = :pedido_id";

    $consulta = $db->prepare($sql);
    $consulta->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $consulta->execute();
    $videojuegos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Procesar los datos
    if ($videojuegos) {
        $fecha_inicio = $videojuegos[0]['fecha_inicio'];
        $dias_alquiler = $videojuegos[0]['dias_alquiler'];
        $punto_recogida = $videojuegos[0]['punto_recogida'];

        // Calcular el total del pedido
        foreach ($videojuegos as $videojuego) {
            $subtotal = $videojuego['unidades'] * $videojuego['precio_unitario'];
            $precio_suma_juegos += $subtotal;
        }

        $precio_total = $precio_suma_juegos * $dias_alquiler;

        // Calcular la fecha límite de entrega
        if ($fecha_inicio && $dias_alquiler) {
            $fecha_limite_entrega = date('Y-m-d', strtotime($fecha_inicio . " + $dias_alquiler days"));
        }

        // Calcular la multa si hay retraso
        if ($fecha_limite_entrega) {
            $fecha_actual = date('Y-m-d');
            if ($fecha_actual > $fecha_limite_entrega) {
                $dias_retraso = (strtotime($fecha_actual) - strtotime($fecha_limite_entrega)) / (60 * 60 * 24); // Días de retraso
                $multa_diaria = $precio_suma_juegos + ($precio_suma_juegos * 0.25); // El 25% del precio total original por día
                $multa_total = $dias_retraso * $multa_diaria; // Multa acumulada por días de retraso
            }
        }
    } else {
        $mensaje_error = "No se encontraron videojuegos para este pedido.";
    }

    $precio_final = $precio_total + $multa_total;

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Función para obtener la ruta de la imagen del videojuego
function obtenerRutaImagen($nombre) {
    $ruta_base = '../imagenes/';
    $extensiones = ['jpg', 'jpeg', 'png'];

    foreach ($extensiones as $ext) {
        $ruta_completa = $ruta_base . $nombre . '.' . $ext;
        if (file_exists($ruta_completa)) {
            return $ruta_completa;
        }
    }
    return $ruta_base . 'imagen_default.png';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gradient-form {
            background-color: #f7f7f7;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 10px auto;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            width: 100%;
            height: 250px;
            object-fit: fill;
            object-position: center;
        }

        .saldo-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 80px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .saldo-card h5 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .saldo-card .btn {
            width: 100%;
        }

        #referencia_recogida {
        border: 1px solid black;
        border-radius: 4px;
        padding: 8px;
        font-size: 16px;
    }
    </style>
</head>
<body class="gradient-form">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mt-5">
                <div class="saldo-card">
                    <h5>Saldo del Usuario</h5>
                    <p><strong><?php echo ($_SESSION['monedero']); ?> € </strong></p>
                    <form action="agregar_saldo.php" method="get">
                        <button type="submit" class="btn btn-primary">Agregar Saldo</button>
                    </form>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-8">
                <div class="text-center mb-3">
                    <a href="pedidos_Activos.php" class="btn btn-secondary">Volver</a>
                </div>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'saldo_insuficiente'): ?>
                        <div class="alert alert-danger text-center">
                            Saldo insuficiente para finalizar el alquiler.
                        </div>
                    <?php endif; ?> 
                    <h1 class="text-center mb-4">Detalles del Pedido <?php echo htmlspecialchars($pedido_id); ?></h1>
                    <?php if (isset($mensaje_error)): ?>
                        <div class="alert alert-warning text-center"><?php echo htmlspecialchars($mensaje_error); ?></div>
                    <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($videojuegos as $videojuego): ?>
                        <?php $nombre_imagen = obtenerRutaImagen($videojuego['nombre']); ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($nombre_imagen); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($videojuego['nombre']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($videojuego['nombre']); ?></h5>
                                    <p class="card-text">
                                        <strong>Plataforma:</strong> <?php echo htmlspecialchars($videojuego['plataforma']); ?><br>
                                        <strong>Unidades:</strong> <?php echo htmlspecialchars($videojuego['unidades']); ?><br>
                                        <strong>Precio Unitario:</strong><?php echo htmlspecialchars(number_format($videojuego['precio_unitario'], 2)); ?>€<br>
                                        <strong>Total:</strong><?php echo htmlspecialchars(number_format($videojuego['unidades'] * $videojuego['precio_unitario'], 2)); ?>€
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <h4><strong>Punto de Recogida:</strong> <?php echo htmlspecialchars($punto_recogida); ?></h4>
                        <h4><strong>Fecha de Pedido:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($fecha_inicio))); ?></h4>
                        <h4><strong>Días Alquilados:</strong> <?php echo htmlspecialchars($dias_alquiler); ?> días</h4>
                        <h4><strong>Fecha Límite de Entrega:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($fecha_limite_entrega))); ?></h4>
                        <h4><strong>Precio Total del Pedido:</strong> <?php echo number_format($precio_total, 2); ?>€</h4>
                        <?php if ($dias_retraso > 0): ?>
                            <h4 class="text-danger"><strong>Días de Retraso:</strong> <?php echo $dias_retraso; ?> días</h4>
                            <h4 class="text-danger"><strong>Multa Acumulada por Retraso:</strong> <?php echo number_format($multa_total, 2); ?>€</h4>
                        <?php endif; ?>
                        <h4><strong>Precio Final del Pedido:</strong><?php echo number_format($precio_final, 2); ?>€</h4>
                        <div class="text-center mt-3">
                            <form action="finalizar_alquiler.php" method="post">
                                <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido_id); ?>">
                                <div class="mb-3">
                                    <label for="referencia_recogida" class="form-label">Referencia de Recogida:</label>
                                    <input type="text" class="form-control" id="referencia_recogida" name="referencia_recogida" required>
                                    <?php if (isset($_GET['error']) && $_GET['error'] === 'referencia_incorrecta'): ?>
                                        <div class="text-danger mt-2">Referencia de recogida incorrecta.</div>
                                    <?php endif; ?>
                                    <?php if (isset($_GET['error']) && $_GET['error'] === 'datos_invalidos'): ?>
                                        <div class="text-danger mt-2">Datos invalidos.</div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-success">Finalizar Alquiler</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
