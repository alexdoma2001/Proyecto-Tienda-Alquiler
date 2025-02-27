<?php
session_start();
include '../conexion_be.php';

// Verificaciones
if (!isset($_SESSION['cliente_id']) || !isset($_SESSION['punto_recogida']) || !isset($_SESSION['dias_alquiler'])) {
    header("Location: ../pagina_principal/index.php");
    exit();
}


if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../pagina_principal/index.php");
}
$dias_alquiler = $_SESSION['dias_alquiler'];
if (!$dias_alquiler) {
    $_SESSION['error_message'] = "No se ha definido el número de días de alquiler.";
    header("Location: seleccionar_fechas.php");
    exit();
}

// Obtener información del punto de recogida seleccionado
$punto_recogida_id = $_SESSION['punto_recogida'];
$preparada = $db->prepare("SELECT * FROM punto_recogida WHERE id = :id");
$preparada->bindParam(':id', $punto_recogida_id, PDO::PARAM_INT);
$preparada->execute();
$punto_recogida = $preparada->fetch(PDO::FETCH_ASSOC);

// Procesar el carrito para obtener detalles de los videojuegos
$carrito_detalles = [];
foreach ($_SESSION['carrito'] as $item) {
    $videojuego_id = $item['videojuego_id'];
    $plataforma = $item['plataforma'];

    // Consulta para obtener el nombre del videojuego
    $preparada = $db->prepare("SELECT nombre FROM videojuegos WHERE id = :id");
    $preparada->bindParam(':id', $videojuego_id, PDO::PARAM_INT);
    $preparada->execute();
    $videojuego = $preparada->fetch(PDO::FETCH_ASSOC);

    if ($videojuego) {
        $carrito_detalles[] = [
            'nombre' => $videojuego['nombre'],
            'plataforma' => $plataforma,
            'precio' => $item['precio'],
            'unidades' => $item['unidades']
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Alquiler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-center pb-2">
        <a href="seleccionar_punto_recogida.php" class="btn btn-outline-danger mt-2">Atrás</a>
    </div>
    <h2 class="text-center">Confirmar Alquiler</h2>

        <div id="errorMessages" class="mt-3">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>
    <h4 class="mt-4">Resumen del alquiler</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Videojuego</th>
                <th>Plataforma</th>
                <th>Unidades</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php $total_general = 0; ?>
        <?php foreach ($carrito_detalles as $item): ?>
            <?php 
                $total_item = $item['precio'] * $item['unidades']; 
                $total_general += $total_item;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                <td><?php echo htmlspecialchars($item['plataforma']); ?></td>
                <td><?php echo htmlspecialchars($item['unidades']); ?></td>
                <td>€<?php echo number_format($item['precio'], 2); ?></td>
                <td>€<?php echo number_format($total_item, 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total General Por Día:</th>
                <th>€<?php echo number_format($total_general, 2); ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Total Por <?php echo $dias_alquiler; ?> Día(s):</th>
                <th>€<?php echo number_format($total_general * $dias_alquiler, 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="alert alert-warning text-center mt-4">
        <strong>¡Importante!</strong>Deberá presentar su DNI o NIE para vereficar su identidad en el punto de entrega. Para retirar su pedido, los datos del DNI O NIE deberán coincidir con los introducidos en la cuenta del solicitante. <br>  No se le cobrará el importe hasta que realice la devolución del producto en el punto de recogida seleccionado. Se deberá devolver todos los productos del pedido a la vez en las condiciones en las que se fueron entregadas.<br>Se le cobrará los dias que indicó en el alquiler. No se descontará dinero por una entrega anterior al plazo asignado. Si no cumple con los plazos de alquiler, se le cobrará una multa por los días en los que ha abusado del producto. <br> Muchas gracias por su comprensión y por confiar en nuestros servicios.
        <br><strong>Para realizar el alquiler, deberá aceptar que ha leido este mensaje y que acepta los terminos y condiciones</strong>
    </div>

    <div class="form-check mt-3">
        <input type="checkbox" id="termsCheckbox" class="form-check-input border border-dark">
        <label for="termsCheckbox" class="form-check-label">
            He leído los términos y condiciones del alquiler, y acepto que se me cobre por no cumplir con los plazos del alquiler.
        </label>
    </div>

    <h4 class="mt-4">Punto de Recogida Seleccionado</h4>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($punto_recogida['nombre']); ?></p>
    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($punto_recogida['direccion']); ?></p>

    <form method="POST" action="procesar_alquiler.php">
        <button type="submit" id="confirmButton" class="btn btn-primary" disabled>Confirmar Alquiler</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript para habilitar el botón solo si se marca el checkbox
    const termsCheckbox = document.getElementById('termsCheckbox');
    const confirmButton = document.getElementById('confirmButton');

    termsCheckbox.addEventListener('change', function () {
        confirmButton.disabled = !termsCheckbox.checked;
    });
</script>
</body>
</html>
