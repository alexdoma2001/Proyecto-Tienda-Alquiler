<?php
// Iniciar la sesión
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login_admin.php');
    exit();
}

// Conectar a la base de datos
include '../../conexion_be.php';

$pedidos = [];
$dni = isset($_GET['dni']) ? $_GET['dni'] : null;
$error = "";
$nombre = "";

if ($dni) {
    try {

        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 5; // Número de resultados por página
        $offset = ($pagina - 1) * $limite;
        $ordenEstado = isset($_GET['orden_estado']) && $_GET['orden_estado'] === 'desc' ? 'DESC' : 'ASC';

        $consultaPedido = "SELECT 
            a.ID AS alquiler_id, 
            a.fecha_Inicio, 
            a.dias_Alquiler,
            a.fecha_Final, 
            a.precio_Final, 
            CONCAT(pr.nombre, ', ', pr.direccion, ', ', pr.ciudad, ', ', pr.codigo_postal) AS punto_recogida, 
            a.referencia_Recogida, 
            a.estado, 
            c.ID AS cliente_id, 
            c.DNI, 
            c.nombre,
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
        WHERE c.DNI = :dni
        GROUP BY a.ID
        ORDER BY a.estado $ordenEstado, a.ID -- Ordena primero por estado y luego por ID
        LIMIT :limite OFFSET :offset";
        
        $consultaNombre = "SELECT nombre FROM cliente WHERE DNI = :dni";
        $preparadaNombre = $db->prepare($consultaNombre);
        $preparadaNombre->bindParam(':dni', $dni, PDO::PARAM_STR);
        $preparadaNombre->execute();
        $nombre = $preparadaNombre->fetchColumn();

        if (!$nombre) {
            throw new Exception("No se encontró un cliente con el DNI proporcionado.");
        }

        $preparada = $db->prepare($consultaPedido);
        $preparada->bindParam(':dni', $dni, PDO::PARAM_STR);
        $preparada->bindParam(':limite', $limite, PDO::PARAM_INT);
        $preparada->bindParam(':offset', $offset, PDO::PARAM_INT);
        $preparada->execute();
        $pedidos = $preparada->fetchAll(PDO::FETCH_ASSOC);


        $consultaCount = "SELECT COUNT(DISTINCT a.ID) AS total 
                     FROM alquiler a
                     INNER JOIN cliente c ON a.cliente_ID = c.ID
                     WHERE c.DNI = :dni";

        $preparadaCount = $db->prepare($consultaCount);
        $preparadaCount->bindParam(':dni', $dni, PDO::PARAM_STR);
        $preparadaCount->execute();
        $totalRegistros = $preparadaCount->fetchColumn();
        $totalPaginas = ceil($totalRegistros / $limite);

        if (empty($pedidos)) {
            $error = "No se encontraron pedidos para el cliente con DNI: " . htmlspecialchars($dni);
        }
    } catch (PDOException $e) {
        $error = "Error al obtener los pedidos: " . $e->getMessage();
    }
} else {
    $error = "El DNI del cliente no fue proporcionado.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $alquilerId = $_POST['eliminar_id'];

    try {
        $consultaEliminar = "DELETE FROM alquiler WHERE ID = :id";
        $preparadaEliminar = $db->prepare($consultaEliminar);
        $preparadaEliminar->bindParam(':id', $alquilerId, PDO::PARAM_INT);
        $preparadaEliminar->execute();

        header("Location: ?dni=" . $dni);
        exit();
    } catch (PDOException $e) {
        $error = "Error al eliminar el alquiler: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos del Cliente</title>
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

        a{
            text-decoration: none;
            color: black;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex align-items-center justify-content-center pb-4 mt-4">
            <a href="buscar_por_dni.php" class="btn btn-outline-danger back-btn">Atrás</a>
        </div>

        <h1>Pedidos del Cliente</h1>

        <!-- Mostrar errores si existen -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php else: ?>
            <h2 class="text-center">Pedidos de <?php echo htmlspecialchars($nombre) ?> con dni:<?php echo htmlspecialchars($dni) ?></h2>

            <div class="table-container">
            <div class="table-container">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID Alquiler</th>
                            <th>Fecha Inicio</th>
                            <th>Dias Alquilados</th>
                            <th>Fecha Final</th>
                            <th>Punto de Recogida</th>
                            <th>Precio Final</th>
                            <th>Referencia Recogida</th>
                            <th>
                                <a href="?dni=<?php echo htmlspecialchars($dni); ?>&orden_estado=<?php echo isset($_GET['orden_estado']) && $_GET['orden_estado'] === 'desc' ? 'asc' : 'desc'; ?>">
                                    Estado
                                    <?php if (isset($_GET['orden_estado']) && $_GET['orden_estado'] === 'desc'): ?>
                                        🔽
                                    <?php else: ?>
                                        🔼
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido['alquiler_id']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['fecha_Inicio']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['dias_Alquiler']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['fecha_Final']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['punto_recogida']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['precio_Final']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['referencia_Recogida']); ?></td>
                                <td> <?php echo $pedido['estado'] == 1 ? 'En vigor' : 'Finalizado'; ?> </td>
                                <td>
                                    <form method="POST" onsubmit="return confirmarEliminacion(<?php echo $pedido['alquiler_id']; ?>);">
                                        <input type="hidden" name="eliminar_id" value="<?php echo $pedido['alquiler_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <strong>Videojuegos:</strong> 
                                    <?php echo htmlspecialchars($pedido['videojuegos']); ?>
                                </td>
                                <td colspan="6">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if (!empty($pedidos)): ?>

    <nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?dni=<?php echo htmlspecialchars($dni); ?>&orden_estado=<?php echo htmlspecialchars($ordenEstado); ?>&pagina=<?php echo $pagina - 1; ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?php echo $pagina == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?dni=<?php echo htmlspecialchars($dni); ?>&orden_estado=<?php echo htmlspecialchars($ordenEstado); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
            <a class="page-link" href="?dni=<?php echo htmlspecialchars($dni); ?>&orden_estado=<?php echo htmlspecialchars($ordenEstado); ?>&pagina=<?php echo $pagina + 1; ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
    </div>

    <script>
        function confirmarEliminacion(id) {
            return confirm('¿Está seguro que desea eliminar el alquiler con ID: ' + id + '?');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
