<?php
session_start();
include '../conexion_be.php';

if (isset($_SESSION['cliente_id'])) {
    try {
        // Obtener el ID del cliente
        $cliente_id = $_SESSION['cliente_id'];

        // Verificar si el cliente tiene alquileres pendientes o en vigor
        $sql_alquileres = "SELECT COUNT(*) FROM alquiler WHERE cliente_id = :cliente_id AND estado IN ('1')";
        $consulta_alquileres = $db->prepare($sql_alquileres);
        $consulta_alquileres->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $consulta_alquileres->execute();
        $alquileres_pendientes = $consulta_alquileres->fetchColumn();

        if ($alquileres_pendientes > 0) {
            // Redirigir con mensaje de error si tiene alquileres activos
            header("Location: perfil.php?error=Debe finalizar sus alquileres antes de eliminar la cuenta");
            exit();
        }

        // Eliminar los datos del cliente de la base de datos
        $sql = "DELETE FROM cliente WHERE ID = :cliente_id";
        $preparada = $db->prepare($sql);
        $preparada->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $preparada->execute();

        // Eliminar la sesión del cliente
        session_destroy();

        // Redirigir al usuario con mensaje de éxito
        header("Location: ../pagina_principal/index.php?mensaje=Cuenta eliminada con éxito");
        exit();

    } catch (PDOException $e) {
        // Mostrar mensaje de error en caso de fallo en la base de datos
        echo "Error: " . $e->getMessage();
    }
} else {
    // Si no hay sesión activa, redirigir a la página de inicio
    header("Location: ../pagina_principal/index.php");
    exit();
}
?>