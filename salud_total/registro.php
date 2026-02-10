<?php
session_start(); 

// 1. Control de Acceso: Redirigir si no hay sesión activa.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_conexion.php';

$mensaje_estado = ''; // Usado para confirmar éxito o mostrar errores

// Inicializar variables para mantener los valores del formulario en caso de error
$nombre = '';
$categoria = '';
$cantidad = 1;
$precio = '';
$proveedor_id = '';

// 2. Cargar lista de Proveedores
$proveedores = [];
// Usamos consultas simples para SELECTs sin entradas de usuario
$sql_prov = "SELECT id, nombre FROM Proveedores ORDER BY nombre ASC";
$resultado_prov = $conexion->query($sql_prov);

if ($resultado_prov && $resultado_prov->num_rows > 0) {
    while ($row = $resultado_prov->fetch_assoc()) {
        $proveedores[] = $row;
    }
}

// 3. Procesar el formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3a. Recolección y Limpieza/Casting de Entradas
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    
    // Casting robusto de números para asegurar tipos correctos
    $cantidad = filter_var($_POST['cantidad'] ?? 0, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'default' => 0)));
    $precio = filter_var($_POST['precio'] ?? 0.0, FILTER_VALIDATE_FLOAT, array('options' => array('min_range' => 0.01, 'default' => 0.0)));
    $proveedor_id = filter_var($_POST['proveedor_id'] ?? 0, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'default' => 0)));

    // 3b. Validación de Formularios
    if (empty($nombre) || empty($categoria) || $cantidad === 0 || $precio === 0.0 || $proveedor_id === 0) {
        $mensaje_estado = 'Error: Todos los campos son obligatorios y deben ser valores válidos.';
        
        // Asignar los valores del POST de vuelta a las variables para rellenar el formulario
        $nombre = htmlspecialchars($_POST['nombre'] ?? '');
        $categoria = htmlspecialchars($_POST['categoria'] ?? '');
        $cantidad = htmlspecialchars($_POST['cantidad'] ?? 1);
        $precio = htmlspecialchars($_POST['precio'] ?? '');
        $proveedor_id = htmlspecialchars($_POST['proveedor_id'] ?? '');

    } else {
        // 3c. Operación CRUD: Crear (INSERT) - Usando Prepared Statement
        $sql_insert = "INSERT INTO Medicamentos (nombre, categoria, cantidad, precio, proveedor_id) 
                         VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql_insert);

        $stmt->bind_param('ssidi', $nombre, $categoria, $cantidad, $precio, $proveedor_id);

        if ($stmt->execute()) {
            $mensaje_estado = 'Exitoso: Medicamento ' . htmlspecialchars($nombre) . ' registrado correctamente.';

            $nombre = $categoria = $precio = $proveedor_id = '';
            $cantidad = 1; 

        } else {

            error_log("Error de inserción de medicamento: " . $stmt->error); // Log del error
            $mensaje_estado = 'Error al registrar el medicamento. Por favor, contacte a soporte.';
        }
        $stmt->close();
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Medicamento - Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --color-primary: #007bff;
            --color-secondary: #6c757d; 
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            border-left: 5px solid var(--color-accent);
        }
        .btn-success {
            background-color: var(--color-accent);
            border-color: var(--color-accent);
            transition: background-color 0.3s;
        }
        .btn-success:hover {
            background-color: #1e7e34;
            border-color: #1e7e34;
        }
        .alert-success { 
            background-color: #c3e6cb; 
            color: #155724; 
            border-color: #b1dfbb;
        }
        .alert-danger { 
            background-color: #f5c6cb; 
            color: #721c24; 
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Farmacia | Inventario</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
                    <h2 class="mb-4 text-center text-primary"> Registrar Nuevo Medicamento</h2>

                    <?php
                    // Usamos htmlspecialchars para prevenir XSS en el mensaje de estado
                    if (!empty($mensaje_estado)) {
                        $clase = (strpos($mensaje_estado, 'Exitoso') !== false) ? 'alert-success' : 'alert-danger';
                        echo '<div class="alert ' . $clase . '" role="alert">' . htmlspecialchars($mensaje_estado) . '</div>';
                    }
                    ?>

                    <form action="registro.php" method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Medicamento:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                value="<?= htmlspecialchars($nombre) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría (ej. Antibiótico):</label>
                            <input type="text" class="form-control" id="categoria" name="categoria" required 
                                value="<?= htmlspecialchars($categoria) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cantidad" class="form-label">Cantidad Disponible:</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required 
                                    value="<?= htmlspecialchars($cantidad) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio Unitario:</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="precio" name="precio" required 
                                        value="<?= htmlspecialchars($precio) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="proveedor_id" class="form-label">Proveedor:</label>
                            <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                                <option value="">Seleccione un proveedor</option>
                                <?php 
                                foreach ($proveedores as $prov): 
                                    // Compara contra la variable $proveedor_id que ahora es un int o string vacío 
                                    $selected = ($prov['id'] == $proveedor_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $prov['id'] ?>" <?= $selected ?>><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Registrar Medicamento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>