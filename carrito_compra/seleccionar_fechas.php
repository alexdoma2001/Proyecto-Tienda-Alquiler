<?php
session_start();

$diferencia_dias = null;
$error = "";

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar las fechas enviadas desde el formulario
    if (isset($_POST['dias_alquiler'], $_POST['fecha_final']) && is_numeric($_POST['dias_alquiler'])) {
        $diferencia_dias = (int) $_POST['dias_alquiler'];
        $fechaActual = date("Y-m-d"); // Fecha actual (fecha de inicio)
        $fechaFinal = $_POST['fecha_final'];

        // Validar que la fecha final sea posterior a la fecha actual
        if (strtotime($fechaFinal) > strtotime($fechaActual)) {
            // Guardar en la sesión
            $_SESSION['dias_alquiler'] = $diferencia_dias;
            $_SESSION['fecha_inicio'] = $fechaActual;
            $_SESSION['fecha_final'] = $fechaFinal;
            header("Location: seleccionar_punto_recogida.php");
            exit();
        } else {
            $error = "La fecha final debe ser posterior a la fecha actual.";
        }
    } else {
        $error = "No se pudo calcular el número de días de alquiler o faltan datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Fechas de Alquiler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-center pb-2">
        <a href="carrito.php" class="btn btn-outline-danger mt-2">Atrás</a>
    </div>
    <h2 class="text-center">Seleccionar Fechas de Alquiler</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="seleccionar_fechas.php">
        <div class="mb-3">
            <label for="fecha_final" class="form-label">Selecciona la Fecha Final</label>
            <input type="date" id="fecha_final" name="fecha_final" class="form-control" required>
        </div>

        <!-- Etiqueta para mostrar el cálculo dinámico -->
        <div class="mb-3">
            <label id="dias_alquiler_label" class="form-label text-muted">Selecciona una fecha para calcular el total de días.</label>
        </div>

        <!-- Campo oculto para almacenar los días calculados -->
        <input type="hidden" id="dias_alquiler" name="dias_alquiler">

        <div class="text-center">
            <button type="submit" class="btn btn-primary">Continuar</button>
        </div>
    </form>
</div>

<!-- JavaScript para cálculo dinámico -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fechaFinalInput = document.getElementById('fecha_final');
        const diasAlquilerLabel = document.getElementById('dias_alquiler_label');
        const diasAlquilerInput = document.getElementById('dias_alquiler');

        fechaFinalInput.addEventListener('change', () => {
            const fechaActual = new Date();
            const fechaFinal = new Date(fechaFinalInput.value);

            if (fechaFinal > fechaActual) {
                const diferenciaMilisegundos = fechaFinal - fechaActual;
                const dias = Math.ceil(diferenciaMilisegundos / (1000 * 60 * 60 * 24)); // Convertir milisegundos a días

                
                diasAlquilerLabel.textContent = `Total de días de alquiler: ${dias}`;

                
                diasAlquilerInput.value = dias;
            } else {
                diasAlquilerLabel.textContent = "La fecha final debe ser posterior a la fecha actual.";
                diasAlquilerInput.value = ""; 
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
