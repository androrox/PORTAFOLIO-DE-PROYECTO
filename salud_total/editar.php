<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_conexion.php';

$mensaje_estado = '';
$medicamento = null;
$proveedores = [];
$medicamento_id = (int)($_GET['id'] ?? 0); // Obtenemos el ID del medicamento desde la URL

// 2. Bloque para cargar el medicamento y los proveedores
if ($medicamento_id > 0) {
    // 2a. Cargar el medicamento a editar
    $sql_med = "SELECT id, nombre, categoria, cantidad, precio, proveedor_id 
                 FROM Medicamentos WHERE id = ?";
    $stmt_med = $conexion->prepare($sql_med);
    $stmt_med->bind_param('i', $medicamento_id);
    $stmt_med->execute();
    $resultado_med = $stmt_med->get_result();
    
    if ($resultado_med->num_rows === 1) {
        $medicamento = $resultado_med->fetch_assoc();
    } else {
        // Si no se encuentra el medicamento, redirigir con mensaje de error
        header('Location: panel.php?estado=' . urlencode('Error: Medicamento no encontrado.'));
        exit();
    }
    $stmt_med->close();

    // 2b. Cargar lista de Proveedores (para el SELECT del formulario)
    $sql_prov = "SELECT id, nombre FROM Proveedores ORDER BY nombre ASC";
    $resultado_prov = $conexion->query($sql_prov);

    if ($resultado_prov && $resultado_prov->num_rows > 0) {
        while ($row = $resultado_prov->fetch_assoc()) {
            $proveedores[] = $row;
        }
    }
} else {
    // Si no hay ID en la URL
    header('Location: panel.php?estado=' . urlencode('Error: ID de medicamento no proporcionado.'));
    exit();
}

// 3. Procesar el formulario POST para ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3a. Recolección y Sanitización de datos
    $id = (int)($_POST['id'] ?? 0); // Aseguramos que el ID se mantenga
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $precio = (float)($_POST['precio'] ?? 0.0);
    $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);

    // 3b. Validación (Asegurar que el ID y los campos sean válidos)
    if ($id !== $medicamento_id || empty($nombre) || empty($categoria) || $cantidad < 0 || $precio <= 0 || $proveedor_id <= 0) {
        // Nota: Permití $cantidad = 0, pues puede estar agotado, pero $precio debe ser > 0.
        $mensaje_estado = 'Error: Datos incompletos o ID inválido para la actualización.';
    } else {
        // 3c. Operación CRUD: Actualizar (UPDATE)
        $sql_update = "UPDATE Medicamentos SET 
                         nombre = ?, categoria = ?, cantidad = ?, precio = ?, proveedor_id = ?
                         WHERE id = ?";
        
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param('ssidii', $nombre, $categoria, $cantidad, $precio, $proveedor_id, $id);

        if ($stmt_update->execute()) {
            // Éxito: Se muestra el mensaje y se redirige al panel para ver los cambios
            $mensaje = 'Actualización Exitosa: Medicamento **' . htmlspecialchars($nombre) . '** modificado correctamente.';
            header('Location: panel.php?estado=' . urlencode($mensaje));
            exit();
            
        } else {
            $mensaje_estado = ' Error al actualizar el medicamento: ' . $stmt_update->error;
        }
        $stmt_update->close();
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Medicamento - Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --color-primary: #007bff; 
            --color-accent: #28a745; 
            --color-header: #8a0022; 
            --color-bg: #f8f9fa; 
        }
        body {
            background-color: var(--color-bg);
            padding-top: 56px;
        }
        .navbar {
            background-color: var(--color-header) !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
            font-weight: bold;
        }
        .form-container {
            margin-top: 20px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 5px solid var(--color-primary); 
        }
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .alert-success { 
            background-color: #d4edda; 
            color: #155724; 
            border-color: #c3e6cb;
        }
        .alert-danger { 
            background-color: #f8d7da; 
            color: #721c24; 
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Editar Medicamento</a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="panel.php">Inventario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-light ms-2" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="form-container">
                    <h2 class="mb-4 text-center text-primary"> Modificar Medicamento #<?= htmlspecialchars($medicamento_id) ?></h2>

                    <?php 
                    // Mostrar mensajes de estado (éxito o error)
                    if (!empty($mensaje_estado)) {
                        $clase = (strpos($mensaje_estado, 'Exito') !== false) ? 'alert-success' : 'alert-danger';
                        echo '<div class="alert ' . $clase . '" role="alert">' . $mensaje_estado . '</div>';
                    }
                    ?>

                    <?php if ($medicamento): ?>
                        <form action="editar.php?id=<?= $medicamento_id ?>" method="POST">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($medicamento['id']) ?>"> 

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Medicamento:</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                    value="<?= htmlspecialchars($medicamento['nombre']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría:</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" 
                                    value="<?= htmlspecialchars($medicamento['categoria']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cantidad" class="form-label">Cantidad Disponible:</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" 
                                        value="<?= htmlspecialchars($medicamento['cantidad']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="precio" class="form-label">Precio Unitario:</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0.01" 
                                            value="<?= htmlspecialchars($medicamento['precio']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="proveedor_id" class="form-label">Proveedor:</label>
                                <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                                    <option value="">Seleccione un proveedor</option>
                                    <?php foreach ($proveedores as $prov): ?>
                                        <option value="<?= $prov['id'] ?>" 
                                            <?= ($prov['id'] == $medicamento['proveedor_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($prov['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">No se pudo encontrar el medicamento especificado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>