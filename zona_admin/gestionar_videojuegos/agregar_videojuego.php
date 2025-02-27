<?php
// Iniciar sesión para manejar la conexión a la base de datos
session_start();
require_once '../../conexion_be.php'; // Incluye tu archivo de conexión a la base de datos

// Obtener categorías y plataformas desde la base de datos para mostrarlas en el formulario
$categorias = [];
$plataformas = [];

// Consulta para obtener categorías
$consulta = $db->query("SELECT id, nombre FROM categoria");
$categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener plataformas
$consulta = $db->query("SELECT id, nombre FROM plataforma");
$plataformas = $consulta->fetchAll(PDO::FETCH_ASSOC);

$mensaje_exito = "";
$mensaje_error = "";

// Verificar si se han enviado los datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos enviados desde el formulario
    $titulo = $_POST['titulo'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $categoriasSeleccionadas = $_POST['categorias'] ?? [];
    $plataformasSeleccionadas = $_POST['plataformas'] ?? [];
    $precios = $_POST['precios'] ?? [];

    // Validación básica de los campos
    if (!$titulo || empty($categoriasSeleccionadas) || empty($plataformasSeleccionadas)) {
        $mensaje_error = "Todos los campos son obligatorios.";
    }
        // Manejo de la imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $imagenNombreOriginal = $_FILES['imagen']['name'];
            $imagenTemp = $_FILES['imagen']['tmp_name'];
    
            // Obtener la extensión de la imagen
            $extension = strtolower(pathinfo($imagenNombreOriginal, PATHINFO_EXTENSION));
    
            // Generar un nuevo nombre de archivo con el título y la extensión adecuada
            $imagenRuta = "../../imagenes/" . $titulo . "." . $extension;
    
            // Mover el archivo a la carpeta 'imagenes' con el nuevo nombre
            if (!move_uploaded_file($imagenTemp, $imagenRuta)) {
                $mensaje_error = "Todos los campos son obligatorios.";
            }
        }

    // Iniciar una transacción
    $db->beginTransaction();

    try {
        // Insertar el videojuego en la tabla `videojuego`
        $consulta = $db->prepare("INSERT INTO videojuegos (nombre, descripcion) VALUES (?, ?)");
        $consulta->execute([$titulo, $descripcion]);

        // Obtener el ID del videojuego recién insertado
        $videojuego_id = $db->lastInsertId();

        // Asignar categorías seleccionadas al videojuego
        foreach ($categoriasSeleccionadas as $categoria_id) {
            $consulta = $db->prepare("INSERT INTO videojuegos_categoria (videojuego_id, categoria_id) VALUES (?, ?)");
            $consulta->execute([$videojuego_id, $categoria_id]);
        }

        // Asignar plataformas seleccionadas al videojuego con precios específicos
        foreach ($plataformasSeleccionadas as $videojuego => $plataforma_id) {
            $precio = $precios[$videojuego];
            $consulta = $db->prepare("INSERT INTO videojuegos_plataforma (videojuego_id, plataforma_id, precio) VALUES (?, ?, ?)");
            $consulta->execute([$videojuego_id, $plataforma_id, $precio]);
        }

        // Confirmar transacción
        $db->commit();
        $mensaje_exito = "Videojuego agregado exitosamente.";
    } catch (Exception $e) {
        // Deshacer transacción en caso de error
        $db->rollBack();
        $mensaje_error = "No se ha podido introducir el juego, revise bien los datos " . $e->getMessage();
    }
}
?>





<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Videojuego</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container my-5">
    <div class="d-flex align-items-center justify-content-center pb-4">
        <a href="gestion_videojuegos.php" class="btn btn-outline-danger mt-4">Atrás</a>
    </div>
    <h1 class="text-center">Agregar Videojuego</h1>
    <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success text-center">
            <?php echo $mensaje_exito; ?>
        </div>
    <?php endif; ?>
    <form id="formularioVideojuego" action="agregar_videojuego.php" method="POST" enctype="multipart/form-data" class="p-4 shadow rounded">
        
    <div class="form-group">
        <label for="titulo">Título del Videojuego:</label>
            <input type="text" name="titulo" id="titulo" class="form-control" required oninput="validarTitulo()">

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label for="imagen">Seleccionar Imagen (PNG o JPEG):</label>
            <input type="file" name="imagen" id="imagen" class="form-control-file" accept="image/png, image/jpeg">
        </div>

        <div id="mensajeError" class="alert alert-danger text-center" style="display:none;"></div>
        <fieldset class="form-group">
            <legend>Categorías</legend>
            <?php foreach ($categorias as $categoria): ?>
                <div class="form-check">
                    <input type="checkbox" name="categorias[]" value="<?php echo $categoria['id']; ?>" class="form-check-input categoria-checkbox">
                    <label class="form-check-label"><?php echo htmlspecialchars($categoria['nombre']); ?></label>
                </div>
            <?php endforeach; ?>
        </fieldset>

        <fieldset class="form-group">
            <legend>Plataformas</legend>
            <?php foreach ($plataformas as $plataforma): ?>
                <div class="form-check">
                    <input type="checkbox" name="plataformas[]" value="<?php echo $plataforma['id']; ?>" class="form-check-input plataforma-checkbox" onchange="precioCheckboxes(this)">
                    <label class="form-check-label"><?php echo htmlspecialchars($plataforma['nombre']); ?></label>
                    <input type="number" name="precios[]" class="form-control mt-2" placeholder="Precio" min="0" style="display:none; ">
                </div>
            <?php endforeach; ?>
        </fieldset>

        <button type="submit" class="btn btn-primary btn-block">Agregar Videojuego</button>
    </form>

    <script>
        function validarTitulo() {
            const nombreInput = document.getElementById('titulo');
            const caracteresNoPermitidos = /[\\\/:*?"^<>|]/g;

            if (caracteresNoPermitidos.test(nombreInput.value)) {
                nombreInput.setCustomValidity("No se permiten los caracteres \\ / : * ? \" ^ < > | en el nombre."); //Guarda el mensaje en Validity
                nombreInput.value = nombreInput.value.replace(caracteresNoPermitidos, '');
            } else {
                nombreInput.setCustomValidity("");
            }
            //Es para mostrar el mensaje validity que hemos guardado anteriormente
            nombreInput.reportValidity();
        }

        function precioCheckboxes(checkbox) {
            const precioInput = checkbox.nextElementSibling.nextElementSibling;
            if (checkbox.checked) {
                precioInput.style.display = 'block';
                precioInput.setAttribute('required', 'true'); // Requerir el precio
            } else {
                precioInput.style.display = 'none';
                precioInput.value = ''; // Limpiar el valor del precio si se desmarca
                precioInput.removeAttribute('required'); // Quitar el requisito de valor
            }
        }

         // Validar si al menos un checkbox de cada grupo (categorías y plataformas) está seleccionado
         document.getElementById('formularioVideojuego').addEventListener('submit', function(event) {
            const categoriasSeleccionadas = document.querySelectorAll('.categoria-checkbox:checked').length;
            const plataformasSeleccionadas = document.querySelectorAll('.plataforma-checkbox:checked').length;
            const mensajeError = document.getElementById('mensajeError');

            // Mostrar un mensaje de error si no hay selección en categorías o plataformas
            if (categoriasSeleccionadas === 0 || plataformasSeleccionadas === 0) {
                event.preventDefault();
                mensajeError.style.display = 'block';
                mensajeError.textContent = 'Debes seleccionar al menos una categoría y una plataforma.';
            } else {
                mensajeError.style.display = 'none';
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
