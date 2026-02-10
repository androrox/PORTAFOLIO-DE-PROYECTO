<?php
// Módulo: Eliminación de registros (medicamentos y proveedores)

session_start();
require_once "db_conexion.php";

// Control de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}


// Eliminar medicamento
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM medicamentos WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Eliminación Exitosa: Medicamento con ID $id eliminado correctamente.";
        }
        $stmt->close();
    }
    header("Location: editar.php");
    exit;
}
?>