<?php

$host = 'localhost';
$usuario_db = 'root';   // Usuario por defecto en XAMPP
$password_db = '';      // Contraseña por defecto en XAMPP (vacía)
$base_de_datos = 'salud_total_db';

// Crear la conexión
$conexion = new mysqli($host, $usuario_db, $password_db, $base_de_datos);

if ($conexion->connect_error) {
    die('Error de Conexión a la Base de Datos: ' . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>