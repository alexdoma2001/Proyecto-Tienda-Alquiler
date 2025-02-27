<?php
session_start();
include '../conexion_be.php';

if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar un producto al carrito
if (isset($_POST['añadir_carrito'])) {
    $videojuego_id = intval($_POST['videojuego_id']);
    $plataforma_id = intval($_POST['plataforma_id']);
    $precio = floatval($_POST['precio']);
    $unidades = 1; // Se inicializa con 1 al agregar un nuevo juego

    // Buscar si el videojuego ya está en el carrito
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['videojuego_id'] == $videojuego_id && $item['plataforma_id'] == $plataforma_id) {
            $item['unidades'] += $unidades; 
            $encontrado = true;
            break;
        }
    }

    // Si no se encontró, añadir
    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'videojuego_id' => $videojuego_id,
            'plataforma_id' => $plataforma_id,
            'precio' => $precio,
            'unidades' => $unidades,
        ];
    }
}

// Aumentar la cantidad de un producto en el carrito
if (isset($_POST['aumentar_cantidad'])) {
    $key = intval($_POST['key']);
    $_SESSION['carrito'][$key]['unidades']++;
}

// Reducir la cantidad de un producto en el carrito
if (isset($_POST['reducir_cantidad'])) {
    $key = intval($_POST['key']);
    if ($_SESSION['carrito'][$key]['unidades'] > 1) {
        $_SESSION['carrito'][$key]['unidades']--;
    } else {
        unset($_SESSION['carrito'][$key]); //Elimina del carrito el proucto
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reinicia el array para que los productos vayan en orden
    }
}

// Eliminar un artículo del carrito
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = intval($_GET['eliminar_id']);
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($key == $eliminar_id) {
            unset($_SESSION['carrito'][$key]);
            break;
        }
    }
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
    max-width: 100%;
    }
    .profile-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 100px;
            margin-right: 20px;
        }
/* Media query para pantallas pequeñas (como móviles) */
@media (max-width: 768px) {
    .d-flex {
        flex-direction: column; /* Apila los elementos */
    }

    .profile-box {
        width: 100%; /* Ocupa todo el ancho disponible */
        height: auto; /* Ajusta la altura automáticamente */
        margin-bottom: 20px; /* Agrega espacio entre secciones */
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 10px;
        align-self: center;
    }

    table {
        font-size: 0.9rem; /* Reduce el tamaño del texto */
    }

    .btn {
        margin: 5px 0; /* Espaciado entre botones */
    }

    td, th {
        text-align: center; /* Centra el contenido en pantallas pequeñas */
    }
    .container{
        align-items: center;
    }
}
    </style>
</head>
<body>
<div class="container mt-5 d-flex">

    <!-- Cuadro de perfil a la izquierda -->
    <div class="profile-box text-center mb-4" style="width: 250px; height: 300px;">
        <h5 class="mt-2 mb-5 text-black">Mi perfil</h5>
        <p><strong>Nombre:</strong> 
            <?php 
            echo htmlspecialchars($_SESSION['nombre'] ?? 'Invitado'); 
            ?>
        </p>
        <p><strong>Tu saldo:</strong> 
            
                <?php echo ($_SESSION['monedero']);?>
            
        </p>
        <a href="../perfil/agregar_saldo.php" class="btn btn-primary btn-sm">Añadir saldo</a>
    </div>

    <!-- Carrito de compras a la derecha -->
    <div style="flex-grow: 1;">
        <div class="d-flex align-items-center justify-content-center pb-4">
            <a href="../pagina_principal/index.php" class="btn btn-outline-danger mt-4">Atrás</a>
        </div>

        <h2 class="text-center">Carrito de Compras</h2>

        <?php if (empty($_SESSION['carrito'])): ?>
            <p class="text-center">Tu carrito está vacío.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Videojuego</th>
                        <th>Plataforma</th>
                        <th>Precio Ud/dia</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_general = 0;
                    foreach ($_SESSION['carrito'] as $key => $item):
                        $total = $item['precio'] * $item['unidades'];
                        $total_general += $total;

                        // Obtener el nombre del videojuego
                        $stmt = $db->prepare("SELECT nombre FROM videojuegos WHERE ID = :videojuego_id");
                        $stmt->bindParam(':videojuego_id', $item['videojuego_id'], PDO::PARAM_INT);
                        $stmt->execute();
                        $videojuego = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($videojuego['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($item['plataforma']); ?></td>
                        <td><?php echo number_format($item['precio'], 2); ?> €</td>
                        <td>
                            <!-- Botones + y - para cambiar la cantidad -->
                            <form action="carrito.php" method="POST" style="display:inline;">
                                <input type="hidden" name="key" value="<?php echo $key; ?>">
                                <button type="submit" name="reducir_cantidad" class="btn btn-sm btn-secondary">-</button>
                            </form>
                            <?php echo $item['unidades']; ?>
                            <form action="carrito.php" method="POST" style="display:inline;">
                                <input type="hidden" name="key" value="<?php echo $key; ?>">
                                <button type="submit" name="aumentar_cantidad" class="btn btn-sm btn-secondary">+</button>
                            </form>
                        </td>
                        <td><?php echo number_format($total, 2); ?> €</td>
                        <td>
                            <a href="carrito.php?eliminar_id=<?php echo $key; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total General Por Dia:</strong></td>
                        <td><?php echo number_format($total_general, 2); ?> €</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="text-center">
                <a href="seleccionar_fechas.php" class="btn btn-success">Proceder con el alquiler</a>
                <a href="../pagina_principal/index.php" class="btn btn-primary">Seguir Comprando</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
