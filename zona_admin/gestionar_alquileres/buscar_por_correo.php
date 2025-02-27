<?php
// Iniciar la sesión
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

// Conectar a la base de datos
include '../../conexion_be.php';

$mensaje = "";
$resultado = null;

// Procesar la búsqueda si se envió el formulario
if (isset($_GET['correo'])) {
    $correo = trim($_GET['correo']);

    if (!empty($correo)) {
        try {
            // Buscar cliente por correo
            $consulta = "SELECT ID, DNI, nombre, correo FROM cliente WHERE correo = :correo";
            $preparada = $db->prepare($consulta);
            $preparada->bindParam(':correo', $correo, PDO::PARAM_STR);
            $preparada->execute();
            $resultado = $preparada->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                $mensaje = "No se encontró ningún cliente con el correo proporcionado.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al buscar el cliente: " . $e->getMessage();
        }
    } else {
        $mensaje = "Por favor, ingresa un correo válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Cliente por Correo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        body {
            background-color: #f5f5f5;
        }

        .back-btn {
            margin-bottom: 20px;
        }

        .card-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            max-width: 400px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            text-align: center;
        }

        .card-title {
            font-weight: bold;
        }

        .alert {
            text-align: center;
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
            <a href="gestionar_alquileres.php" class="btn btn-outline-danger back-btn">Atrás</a>
        </div>

        <h1>Buscar Cliente por Correo</h1>

        <!-- Formulario para buscar cliente por correo -->
        <form action="buscar_por_correo.php" method="get" class="mb-4">
            <div class="input-group">
                <input type="email" name="correo" class="form-control" placeholder="Ingrese el correo" value="<?php echo htmlspecialchars($_GET['correo'] ?? ''); ?>" required>
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </form>

        <!-- Mostrar mensajes si existen -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <!-- Mostrar detalles del cliente si se encuentra -->
        <?php if ($resultado): ?>
            <div class="card-container">
                <div class="card text-black">
                    <a href="pedidos_cliente.php?dni=<?php echo urlencode($resultado['DNI']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($resultado['correo']); ?></h5>
                            <p class="card-text">
                                <strong>Nombre:</strong> <?php echo htmlspecialchars($resultado['nombre']); ?><br>
                                <strong>DNI:</strong> <?php echo htmlspecialchars($resultado['DNI']); ?><br>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
