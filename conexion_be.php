<?php
    $cadena_conexion = "mysql:dbname=tfg2;host=127.0.0.1";
    $usuario = "root";
    $clave = "1234";
    $errmode = [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT];
    try{
        $db = new PDO($cadena_conexion, $usuario, $clave);
    }catch (PDOException $e) {
        echo "conexion fallida" .$e->getMessage();
    }
    
?>