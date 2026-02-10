<?php
session_start();

// 1. Requisito: Solo accesible si existe una sesión activa 
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_conexion.php';

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Administrador'; // Usar valor predeterminado si no está seteado
$rol_usuario = $_SESSION['usuario_rol'] ?? 'N/A';
$filtro_categoria = $_GET['categoria'] ?? ''; // Permite filtrar por GET 

// Construir la consulta SQL para leer (SELECT) los medicamentos 
$sql = "SELECT m.id, m.nombre, m.categoria, m.cantidad, m.precio, p.nombre AS nombre_proveedor
        FROM Medicamentos m 
        JOIN Proveedores p ON m.proveedor_id = p.id";

$params = [];
$types = '';

// Agregar filtro si se proporciona una categoría en la URL (GET)
if (!empty($filtro_categoria)) {
    $sql .= " WHERE m.categoria = ?";
    $params[] = $filtro_categoria;
    $types = 's';
}

$sql .= " ORDER BY m.nombre ASC";

$stmt = $conexion->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
$medicamentos = $resultado->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Inventario - Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
        .navbar-brand, .nav-link, .user-info {
            color: white !important;
            font-weight: bold;
        }
        .user-info {
            font-size: 0.9em;
            margin-right: 1.5rem;
            opacity: 0.8;
            padding: .5rem 1rem;
            border-radius: .25rem;
        }
        .table-container {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .table thead th {
            background-color: #e9ecef;
            color: #495057;
            border-bottom: 2px solid var(--color-primary);
        }
        .btn-edit { background-color: var(--color-primary); border-color: var(--color-primary); }
        .btn-delete { background-color: #dc3545; border-color: #dc3545; }
        .btn-register { background-color: var(--color-accent); border-color: var(--color-accent); }

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
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Panel de Inventario | Farmacia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="user-info">
                            Usuario: <?= htmlspecialchars($nombre_usuario) ?> (<?= htmlspecialchars($rol_usuario) ?>)
                        </span>
                    </li>
                    <li class="nav-item me-2">
                        <a href="registro.php" class="btn btn-register text-white">
                            Registrar Medicamento
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-light">
                            Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <h2 class="mt-4 mb-4 text-primary">Listado de Existencias</h2>

        <?php
        $mensaje_estado = $_GET['estado'] ?? '';
        if (!empty($mensaje_estado)) {

            $mensaje = htmlspecialchars(urldecode($mensaje_estado)); 
            $clase = (strpos($mensaje, 'Exitoso') !== false) ? 'alert-success' : 'alert-danger';
            echo '<div class="alert ' . $clase . ' alert-dismissible fade show" role="alert">' . $mensaje . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
        ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <form action="panel.php" method="GET" class="d-flex align-items-center bg-white p-3 rounded shadow-sm">
                    <label for="categoria" class="form-label me-2 mb-0 fw-bold">Filtrar por Categoría:</label>
                    <input type="text" class="form-control me-2" id="categoria" name="categoria" 
                           value="<?= htmlspecialchars($filtro_categoria) ?>" placeholder="Ej: Analgésico, Jarabe">
                    
                    <button type="submit" class="btn btn-primary me-2">
                        Buscar
                    </button>
                    
                    <?php if (!empty($filtro_categoria)): ?>
                        <a href="panel.php" class="btn btn-secondary">Limpiar Filtro</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th class="text-center">Cantidad</th>
                            <th>Precio</th>
                            <th>Proveedor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($medicamentos) > 0): ?>
                            <?php foreach ($medicamentos as $med): ?>
                                <tr>
                                    <td><?= htmlspecialchars($med['nombre']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($med['categoria']) ?></span></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $med['cantidad'] < 10 ? 'danger' : 'success' ?>">
                                            <?= htmlspecialchars($med['cantidad']) ?>
                                        </span>
                                    </td>
                                    <td>$<?= number_format($med['precio'], 2) ?></td>
                                    <td><?= htmlspecialchars($med['nombre_proveedor']) ?></td>
                                    <td class="text-center">
                                        <a href="editar.php?id=<?= $med['id'] ?>" class="btn btn-sm btn-edit text-white">
                                            Editar
                                        </a> 
                                        <a href="eliminar.php?id=<?= $med['id'] ?>" class="btn btn-sm btn-delete text-white" 
                                           onclick="return confirm('¿Está seguro de eliminar el medicamento <?= htmlspecialchars($med['nombre']) ?>?');">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">
                                    No hay medicamentos registrados o no se encontraron resultados para la categoría **"<?= htmlspecialchars($filtro_categoria) ?>"**.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Enlace a proveedores -->
        <div class="mb-4">
            <a href="proveedores.php" class="btn btn-secondary">
                Administrar Proveedores
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>