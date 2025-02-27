<?php
// Iniciar sesión
session_start();

// Destruir todas las variables de sesión (excepto el carrito)
unset($_SESSION['cliente_id']); // Elimina la ID del cliente u otra información del usuario

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página principal o a una página específica
header("Location: index.php");
exit();
?>
