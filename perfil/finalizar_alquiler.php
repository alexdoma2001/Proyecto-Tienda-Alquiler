<?php
// Incluir conexión a la base de datos
include '../conexion_be.php';
include 'enviar_correo_finalizacion_alquiler.php';


// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../inicio_sesion/inicio_sesion.php");
    exit();
}

// Validar datos enviados por POST
if (!isset($_POST['pedido_id']) || empty($_POST['pedido_id'])|| !isset($_POST['referencia_recogida']) || empty($_POST['referencia_recogida'])){
    header("Location: detalles_pedido.php?pedido_id=" . (isset($_POST['pedido_id']) ? $_POST['pedido_id'] : '') . "&error=datos_invalidos");
    exit();
}

$pedido_id = (int) $_POST['pedido_id'];
$referencia_recogida = $_POST['referencia_recogida'];

try {
    $sql_referencia = "SELECT referencia_Recogida FROM alquiler WHERE ID = :pedido_id";
    $consulta_referencia = $db->prepare($sql_referencia);
    $consulta_referencia->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $consulta_referencia->execute();
    $resultado_referencia = $consulta_referencia->fetch(PDO::FETCH_ASSOC);

    if (!$resultado_referencia || $resultado_referencia['referencia_Recogida'] !== $referencia_recogida) {
        header("Location: detalles_pedido.php?pedido_id={$pedido_id}&error=referencia_incorrecta");
        exit();
    }



    // Obtener el pedido y calcular la multa si hay retraso
    $sql = "SELECT 
                a.ID AS alquiler_id,
                a.fecha_inicio,
                a.dias_alquiler,
                a.cliente_id,
                (SUM(vp.precio * avp.unidades) * a.dias_alquiler) AS precio_total
            FROM alquiler a
            INNER JOIN alquiler_videojuegos_plataforma avp ON a.ID = avp.alquiler_ID
            INNER JOIN videojuegos_plataforma vp ON avp.videojuego_plataforma_ID = vp.ID
            WHERE a.ID = :pedido_id
            GROUP BY a.ID";

    $consulta = $db->prepare($sql);
    $consulta->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $consulta->execute();
    $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        die("Pedido no encontrado.");
    }

    // Calcular multa por retraso
    $fecha_inicio = $pedido['fecha_inicio'];
    $dias_alquiler = $pedido['dias_alquiler'];
    $precio_total = $pedido['precio_total'];
    $cliente_id = $pedido['cliente_id'];

    $sql_cliente = "SELECT correo AS cliente_correo, nombre AS cliente_nombre FROM cliente WHERE ID = :cliente_id";
    $consulta_cliente = $db->prepare($sql_cliente);
    $consulta_cliente->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $consulta_cliente->execute();
    $cliente = $consulta_cliente->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die("Error: Cliente no encontrado.");
    }

    $cliente_email = $cliente['cliente_correo'];
    $cliente_nombre = $cliente['cliente_nombre'];

    if (empty($cliente_email) || empty($cliente_nombre)) {
        die("Error: Datos del cliente incompletos para enviar el correo.");
    }

    $fecha_limite_entrega = date('Y-m-d', strtotime($fecha_inicio . " + $dias_alquiler days"));
    $fecha_actual = date('Y-m-d');
    $dias_retraso = max(0, (strtotime($fecha_actual) - strtotime($fecha_limite_entrega)) / (60 * 60 * 24)); // Días de retraso

    $multa_total = 0;
    if ($dias_retraso > 0) {
        $multa_diaria = $precio_total * 0.25; // 25% del precio total original por día
        $multa_total = $dias_retraso * $multa_diaria;
    }

    // Calcular total a descontar (precio total + multa)
    $total_a_descontar = $precio_total + $multa_total;

    $sql_saldo = "SELECT monedero FROM cliente WHERE ID = :cliente_id";
    $consulta_saldo = $db->prepare($sql_saldo);
    $consulta_saldo->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $consulta_saldo->execute();
    $saldo_cliente = $consulta_saldo->fetchColumn();
    
    if ($saldo_cliente < $total_a_descontar) {
        header("Location: detalles_pedido.php?pedido_id={$pedido_id}&error=saldo_insuficiente");
        exit();
    }


    // Iniciar transacción
    $db->beginTransaction();

    // Descontar saldo del cliente
    $sql_monedero = "UPDATE cliente SET monedero = monedero - :total_a_descontar WHERE ID = :cliente_id AND monedero >= :total_a_descontar";
    $consulta_monedero = $db->prepare($sql_monedero);
    $consulta_monedero->bindParam(':total_a_descontar', $total_a_descontar, PDO::PARAM_STR);
    $consulta_monedero->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $consulta_monedero->execute();

    // Verificar que el saldo se haya actualizado correctamente
    if ($consulta_monedero->rowCount() === 0) {
        $db->rollBack(); // Revertir cualquier cambio si no se pudo descontar el saldo
        header("Location: detalles_pedido.php?pedido_id={$pedido_id}&error=saldo_insuficiente");
        exit();
    }

    // Actualizar el alquiler a finalizado
    $sql_finalizar = "UPDATE alquiler SET estado = 'finalizado', fecha_Final = :fecha_actual, precio_Final = :precio_Final WHERE ID = :pedido_id";
    $consulta_finalizar = $db->prepare($sql_finalizar);
    $consulta_finalizar->bindParam(':fecha_actual', $fecha_actual, PDO::PARAM_STR);
    $consulta_finalizar->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    // Aquí se suma la multa al precio total
    $precio_final_con_multa = $precio_total + $multa_total; 
    $consulta_finalizar->bindParam(':precio_Final', $precio_final_con_multa, PDO::PARAM_STR);
    $consulta_finalizar->execute();

    // Registrar la multa si existe
    if ($multa_total > 0) {
        $sql_multa = "INSERT INTO multa (cliente_ID, alquiler_ID, valor_Multa, dias_Debidos, fecha) 
                      VALUES (:cliente_id, :alquiler_id, :valor_multa, :dias_retraso, :fecha)";
        $consulta_multa = $db->prepare($sql_multa);
        $consulta_multa->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $consulta_multa->bindParam(':alquiler_id', $pedido_id, PDO::PARAM_INT);
        $consulta_multa->bindParam(':valor_multa', $multa_total, PDO::PARAM_STR);
        $consulta_multa->bindParam(':dias_retraso', $dias_retraso, PDO::PARAM_INT);
        $consulta_multa->bindParam(':fecha', $fecha_actual, PDO::PARAM_STR);
        $consulta_multa->execute();
    }

    // Actualizar el stock de los videojuegos alquilados
    $sql_unidades = "UPDATE videojuegos_plataforma vp
                  INNER JOIN alquiler_videojuegos_plataforma avp ON vp.ID = avp.videojuego_plataforma_ID
                  SET vp.unidades = vp.unidades + avp.unidades
                  WHERE avp.alquiler_ID = :pedido_id";
    $consulta_unidades = $db->prepare($sql_unidades);
    $consulta_unidades->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $consulta_unidades->execute();

    $sql_detalles = "SELECT 
            v.nombre AS videojuego_nombre, 
            p.nombre AS plataforma_nombre, 
            vp.precio AS precio, 
            avp.unidades AS unidades
                FROM alquiler_videojuegos_plataforma avp
                INNER JOIN videojuegos_plataforma vp ON avp.videojuego_plataforma_ID = vp.ID
                INNER JOIN videojuegos v ON vp.videojuego_ID = v.ID
                INNER JOIN plataforma p ON vp.plataforma_ID = p.ID
                WHERE avp.alquiler_ID = :pedido_id";

    $consulta_detalles = $db->prepare($sql_detalles);
    $consulta_detalles->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
    $consulta_detalles->execute();
    $detalles = $consulta_detalles->fetchAll(PDO::FETCH_ASSOC);

    $productos = [];
        foreach ($detalles as $detalle) {
            $productos[] = [
                'nombre' => $detalle['videojuego_nombre'], // Nombre del videojuego
                'plataforma' => $detalle['plataforma_nombre'], // Nombre de la plataforma
                'precio' => $detalle['precio'], // Precio unitario
                'unidades' => $detalle['unidades'], 
            ];
        }

    // Confirmar transacción
    $db->commit();

    enviarCorreoResumen(
        $cliente_email,
        $cliente_nombre,
        $referencia_recogida,
        "Punto de recogida", // Reemplaza con el punto de recogida real
        $dias_alquiler,
        $productos,
        $dias_retraso,
        $multa_total
    );

    // Redirigir con éxito
    header("Location: pedidos_Activos.php?mensaje=alquiler_finalizado");
    exit();
} catch (PDOException $e) {
    // En caso de error, hacer rollback y mostrar mensaje
    $db->rollBack();
    die("Error al finalizar el alquiler: " . $e->getMessage());
} catch (Exception $e) {
    // Rollback para otros errores
    $db->rollBack();
    die("Error: " . $e->getMessage());
}
