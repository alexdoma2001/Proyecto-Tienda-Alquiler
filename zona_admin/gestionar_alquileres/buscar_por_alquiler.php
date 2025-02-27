<?php
// Iniciar la sesión
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

include '../../conexion_be.php';

$alquiler = [];
$error = "";
$idAlquiler = isset($_GET['id_alquiler']) ? $_GET['id_alquiler'] : null;

if ($idAlquiler) {
    try {
        // Consulta para obtener los detalles del alquiler
        $consulta = "SELECT 
                    a.ID AS alquiler_id, 
                    a.fecha_Inicio, 
                    a.fecha_Final, 
                    a.precio_Final, 
                    CONCAT(pr.nombre, ', ', pr.direccion, ', ', pr.ciudad, ', ', pr.codigo_postal) AS punto_recogida, 
                    a.referencia_Recogida, 
                    a.estado, 
                    c.nombre AS cliente_nombre,
                    c.correo AS cliente_correo,
                    GROUP_CONCAT(
                        CONCAT(v.nombre, ' (Plataforma: ', p.nombre, ', Unidades: ', avp.unidades, ')')
                        SEPARATOR ', '
                    ) AS videojuegos
                FROM alquiler a
                INNER JOIN cliente c ON a.cliente_ID = c.ID
                INNER JOIN punto_recogida pr ON a.punto_Recogida = pr.ID
                LEFT JOIN alquiler_videojuegos_plataforma avp ON a.ID = avp.alquiler_id
                LEFT JOIN videojuegos_plataforma vp ON avp.videojuego_plataforma_ID = vp.ID
                LEFT JOIN videojuegos v ON vp.videojuego_id = v.ID
                LEFT JOIN plataforma p ON vp.plataforma_id = p.ID
                WHERE a.ID = :id_alquiler
                GROUP BY a.ID";
        
        $preparada = $db->prepare($consulta);
        $preparada->bindParam(':id_alquiler', $idAlquiler, PDO::PARAM_INT);
        $preparada->execute();
        $alquiler = $preparada->fetch(PDO::FETCH_ASSOC);

        if (!$alquiler) {
            $error = "No se encontró ningún pedido con el ID: " . htmlspecialchars($idAlquiler);
        }
    } catch (PDOException $e) {
        $error = "Error al obtener los detalles del alquiler: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar por Alquiler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        a {
            text-decoration: none;
            color: black;
        }
        body {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex align-items-center justify-content-center pb-4 mt-4">
            <a href="gestionar_alquileres.php" class="btn btn-outline-danger back-btn">Atrás</a>
        </div>

        <h1>Buscar por Alquiler</h1>

        <!-- Formulario para buscar un alquiler por ID -->
        <form action="buscar_por_alquiler.php" method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="id_alquiler" class="form-control" placeholder="Ingrese el ID del alquiler" required>
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </form>

        <!-- Mostrar errores si existen -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php else: ?>
            <!-- Mostrar detalles del alquiler si se encuentra -->
            <?php if (!empty($alquiler)): ?>
                <div class="table-container">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>ID Alquiler</th>
                            <td><?php echo htmlspecialchars($alquiler['alquiler_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Inicio</th>
                            <td><?php echo htmlspecialchars($alquiler['fecha_Inicio']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Final</th>
                            <td><?php echo htmlspecialchars($alquiler['fecha_Final']); ?></td>
                        </tr>
                        <tr>
                            <th>Punto de Recogida</th>
                            <td><?php echo htmlspecialchars($alquiler['punto_recogida']); ?></td>
                        </tr>
                        <tr>
                            <th>Precio Final</th>
                            <td><?php echo htmlspecialchars($alquiler['precio_Final']); ?></td>
                        </tr>
                        <tr>
                            <th>Referencia Recogida</th>
                            <td><?php echo htmlspecialchars($alquiler['referencia_Recogida']); ?></td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td><?php echo $alquiler['estado'] == 1 ? 'En vigor' : 'Finalizado'; ?></td>
                        </tr>
                        <tr>
                            <th>Cliente</th>
                            <td><?php echo htmlspecialchars($alquiler['cliente_nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Correo</th>
                            <td><?php echo htmlspecialchars($alquiler['cliente_correo']); ?></td>
                        </tr>
                        <tr>
                            <th>Videojuegos</th>
                            <td><?php echo htmlspecialchars($alquiler['videojuegos']); ?></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
