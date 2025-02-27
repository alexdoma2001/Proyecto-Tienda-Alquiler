<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

include '../../conexion_be.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de videojuego no especificado.");
}

// Obtener detalles del videojuego
$sql = "SELECT * FROM videojuegos WHERE ID = :id";
$consulta = $db->prepare($sql);
$consulta->bindParam(':id', $id, PDO::PARAM_INT);
$consulta->execute();
$videojuego = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$videojuego) {
    die("Videojuego no encontrado.");
}

// Obtener todas las plataformas
$plataformas = $db->query("SELECT * FROM plataforma")->fetchAll(PDO::FETCH_ASSOC);

// Obtener plataformas y precios asociadas al videojuego
$sql_plataformas_videojuego = "SELECT plataforma_id, precio, unidades FROM videojuegos_plataforma WHERE videojuego_id = :id";
$consulta = $db->prepare($sql_plataformas_videojuego);
$consulta->bindParam(':id', $id, PDO::PARAM_INT);
$consulta->execute();
$plataformas_videojuego = $consulta->fetchAll(PDO::FETCH_ASSOC);

$categorias = $db->query("SELECT * FROM categoria")->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías asociadas al videojuego
$sql_categorias_videojuego = "SELECT categoria_id FROM videojuegos_categoria WHERE videojuego_id = :id";
$consulta = $db->prepare($sql_categorias_videojuego);
$consulta->bindParam(':id', $id, PDO::PARAM_INT);
$consulta->execute();
$categorias_videojuego = $consulta->fetchAll(PDO::FETCH_COLUMN);

// Crear un array de plataformas seleccionadas, sus precios y unidades para el formulario
$precios_seleccionados = [];
$unidades_disponibles = [];
foreach ($plataformas_videojuego as $plataforma) {
    $precios_seleccionados[$plataforma['plataforma_id']] = $plataforma['precio'];
    $unidades_disponibles[$plataforma['plataforma_id']] = $plataforma['unidades'];
}

$nombre_imagen = '../../imagenes/' . $videojuego['nombre'] . '.jpg';

// Si la imagen no existe, usa una imagen por defecto
if (!file_exists($nombre_imagen)) {
    $nombre_imagen = '../../imagenes/logo_pagina.png';
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precios = $_POST['precios'] ?? [];
    $categorias_seleccionadas = $_POST['categorias'] ?? [];

    // Manejo de imagen
    $imagen_actual = '../../imagenes/' . $videojuego['nombre'] . '.jpg';
    $nueva_imagen = $imagen_actual;
    if (isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != '') {
        $nueva_imagen = '../../imagenes/' . $nombre . '.jpg';
        if (file_exists($imagen_actual)) {
            unlink($imagen_actual); // Borrar la imagen antigua
        }
        move_uploaded_file($_FILES['imagen']['tmp_name'], $nueva_imagen);
    } elseif ($nombre != $videojuego['nombre']) {
        // Renombrar la imagen si cambia el nombre del videojuego
        $nueva_imagen = '../../imagenes/' . $nombre . '.jpg';
        rename($imagen_actual, $nueva_imagen);
    }

    // Actualizar datos del videojuego
    $sql_update_videojuego = "UPDATE videojuegos SET nombre = :nombre, descripcion = :descripcion WHERE ID = :id";
    $consulta_update = $db->prepare($sql_update_videojuego);
    $consulta_update->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $consulta_update->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $consulta_update->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta_update->execute();

    // Actualizar plataformas y precios del videojuego
    $sql_delete_plataformas = "DELETE FROM videojuegos_plataforma WHERE videojuego_id = :id";
    $consulta_delete = $db->prepare($sql_delete_plataformas);
    $consulta_delete->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta_delete->execute();

    foreach ($plataformas as $plataforma) {
        $plataforma_id = $plataforma['ID'];
        if (isset($precios[$plataforma_id]) && is_numeric($precios[$plataforma_id])) {
            $precio = $precios[$plataforma_id];
            $sql_insert_plataforma = "INSERT INTO videojuegos_plataforma (videojuego_id, plataforma_id, precio) VALUES (:videojuego_id, :plataforma_id, :precio)";
            $consulta_insert = $db->prepare($sql_insert_plataforma);
            $consulta_insert->bindParam(':videojuego_id', $id, PDO::PARAM_INT);
            $consulta_insert->bindParam(':plataforma_id', $plataforma_id, PDO::PARAM_INT);
            $consulta_insert->bindParam(':precio', $precio, PDO::PARAM_STR);
            $consulta_insert->execute();
        }
    }

    //lo mismo en categoria
    $sql_delete_categorias = "DELETE FROM videojuegos_categoria WHERE videojuego_id = :id";
    $consulta_delete = $db->prepare($sql_delete_categorias);
    $consulta_delete->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta_delete->execute();

    foreach ($categorias_seleccionadas as $categoria_id) {
        $sql_insert_categoria = "INSERT INTO videojuegos_categoria (videojuego_id, categoria_id) VALUES (:videojuego_id, :categoria_id)";
        $consulta_insert = $db->prepare($sql_insert_categoria);
        $consulta_insert->bindParam(':videojuego_id', $id, PDO::PARAM_INT);
        $consulta_insert->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
        $consulta_insert->execute();
    }

    // Redirigir con mensaje de éxito
    header("Location: gestion_videojuegos.php?mensaje=Videojuego y precios actualizados correctamente");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Videojuego</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Editar Videojuego "<?php echo htmlspecialchars($videojuego['nombre']); ?>"</h2>
    <form method="POST" action="" enctype="multipart/form-data">

        <!-- Imagen del videojuego -->
        <div class="mb-3 text-center">
            <?php
                $nombre_imagen_jpg = '../../imagenes/' . $videojuego['nombre'] . '.jpg';
                $nombre_imagen_png = '../../imagenes/' . $videojuego['nombre'] . '.png';

                // Comprobar cuál de las dos imágenes existe
                if (file_exists($nombre_imagen_jpg)) {
                    $nombre_imagen = $nombre_imagen_jpg;  // Usar imagen JPG
                } elseif (file_exists($nombre_imagen_png)) {
                    $nombre_imagen = $nombre_imagen_png;  // Usar imagen PNG
                } else {
                    $nombre_imagen = '../../imagenes/logo_pagina.png';  // Imagen predeterminada
                }
            ?>
            <img src="<?php echo $nombre_imagen; ?>" alt="Imagen del videojuego" class="img-thumbnail" style="max-width: 200px;">
            <div class="mt-3">
                <label for="imagen" class="form-label">Cambiar Imagen</label>
                <input type="file" class="form-control" id="imagen" name="imagen">
            </div>
        </div>

        <!-- Nombre del videojuego -->
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" oninput="validarNombre()" value="<?php echo htmlspecialchars($videojuego['nombre']);  ?>" required>  
        </div>

        <!-- Descripción del videojuego -->
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($videojuego['descripcion']); ?></textarea>
        </div>

        <!-- Selección de plataformas y precios -->
        <h4>Plataformas y Precios</h4>
        <?php foreach ($plataformas as $plataforma): ?>
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input plataforma-check" 
                       id="plataforma_<?php echo $plataforma['ID']; ?>" 
                       name="plataformas[]" 
                       value="<?php echo $plataforma['ID']; ?>"
                       data-unidades="<?php echo $unidades_disponibles[$plataforma['ID']] ?? 0; ?>"
                       <?php echo isset($precios_seleccionados[$plataforma['ID']]) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="plataforma_<?php echo $plataforma['ID']; ?>">
                    <?php echo htmlspecialchars($plataforma['nombre']); ?>
                </label>
            </div>
            <div class="mb-3">
                <label for="precios_<?php echo $plataforma['ID']; ?>" class="form-label">Precio para <?php echo htmlspecialchars($plataforma['nombre']); ?></label>
                <input type="number" step="0.01" min="0" class="form-control" 
                       id="precios_<?php echo $plataforma['ID']; ?>" 
                       name="precios[<?php echo $plataforma['ID']; ?>]" 
                       value="<?php echo isset($precios_seleccionados[$plataforma['ID']]) ? htmlspecialchars($precios_seleccionados[$plataforma['ID']]) : ''; ?>" 
                       <?php echo isset($precios_seleccionados[$plataforma['ID']]) ? '' : 'disabled'; ?> required>
            </div>
        <?php endforeach; ?>


        <!-- Selección de categorias -->
        <h4>Categorías</h4>
        <?php foreach ($categorias as $categoria): ?>
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" 
                       id="categoria_<?php echo $categoria['ID']; ?>" 
                       name="categorias[]" 
                       value="<?php echo $categoria['ID']; ?>"
                       <?php echo in_array($categoria['ID'], $categorias_videojuego) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="categoria_<?php echo $categoria['ID']; ?>">
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </label>
            </div>
        <?php endforeach; ?>


        <!-- Botones -->
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="gestion_videojuegos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Alert de confirmacion que avisa de que se va a eliminar una plataforma con unidades de videojuegos
    document.querySelectorAll('.plataforma-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const unidades = this.getAttribute('data-unidades');
            const precioInput = document.getElementById('precios_' + this.value);
            
            if (!this.checked && unidades > 0) {
                const confirmacion = confirm(`Estás a punto de eliminar esta plataforma de la que se disponen ${unidades} unidades en stock. ¿Deseas continuar?`);
                
                if (!confirmacion) {
                    this.checked = true;
                } else {
                    precioInput.disabled = true;
                }
            } else {
                precioInput.disabled = !this.checked;
            }
        });
    });

    document.querySelectorAll('input[type="number"][min="0"]').forEach(function(input) {
    input.addEventListener('blur', function() { //Para que cuando deje de hacer el click en el input lo valide
        if (this.value < 0) {
            this.setCustomValidity("El precio debe ser 0 o mayor.");
            this.reportValidity(); 
        } else {
            this.setCustomValidity("");
        }
    });

    // Asegura que el mensaje desaparece en tiempo real al corregir el valor
    input.addEventListener('input', function() {
        if (this.value >= 0) {
            this.setCustomValidity("");
        }
    });
});

function validarNombre() {
    const nombreInput = document.getElementById('nombre');
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

</script>
</body>
</html>
