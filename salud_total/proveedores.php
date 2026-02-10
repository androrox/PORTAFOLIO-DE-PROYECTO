<?php
session_start();
require_once "db_conexion.php";

// Control de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Administrador';
$rol_usuario = $_SESSION['usuario_rol'] ?? 'N/A';

$mensaje = "";

// Eliminar proveedor
if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM proveedores WHERE id = ?");
        $stmt->bind_param("i", $id);
        $mensaje = $stmt->execute() ? "Proveedor eliminado correctamente." : "Error al eliminar proveedor.";
        $stmt->close();
    }
}

// Procesar formulario
// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    $hayError = false; // Para evitar el INSERT si algo falla

    // Validación de nombre
    if ($nombre === '') {
        $mensaje = "El nombre es obligatorio.";
        $hayError = true;
    } elseif (!preg_match("/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/", $nombre)) {
        $mensaje = "El nombre solo puede contener letras y espacios.";
        $hayError = true;
    }

    // Validación de teléfono (obligatorio y 10 dígitos)
    if ($telefono === '') {
        $mensaje = "El teléfono es obligatorio.";
        $hayError = true;
    } elseif (!preg_match("/^[0-9]{10}$/", $telefono)) {
        $mensaje = "El teléfono debe contener exactamente 10 números.";
        $hayError = true;
    }

    // Validación de dirección
    if ($direccion === '') {
        $mensaje = "La dirección es obligatoria.";
        $hayError = true;
    } elseif (strlen($direccion) < 5) {
        $mensaje = "La dirección debe tener al menos 5 caracteres.";
        $hayError = true;
    }

    // Si NO hay errores → insertar
    if (!$hayError) {
        $stmt = $conexion->prepare("INSERT INTO proveedores (nombre, telefono, direccion, creado_en) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $nombre, $telefono, $direccion);

        if ($stmt->execute()) {
            $mensaje = "Proveedor agregado correctamente.";
        } else {
            $mensaje = "Error al agregar proveedor.";
        }

        $stmt->close();
    }
}


// Obtener proveedores
$proveedores = [];
$result = $conexion->query("SELECT id, nombre, telefono, direccion, creado_en FROM proveedores ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $proveedores[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Farmacia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .card-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-add { background: var(--color-accent); color: white; }
        .btn-del { background: #dc3545; color: white; }
        .btn-back { background: var(--color-secondary); color: white; }

        table thead th {
            background-color: #e9ecef;
            border-bottom: 2px solid var(--color-primary);
        }

        .alert {
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="panel.php">Panel de Inventario | Farmacia</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="user-info">
                            Usuario: <?= htmlspecialchars($nombre_usuario) ?> (<?= htmlspecialchars($rol_usuario) ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO -->
    <div class="container mt-4">

        <h2 class="text-primary mb-4">Administrar Proveedores</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- AGREGAR PROVEEDOR -->
        <div class="card-box mb-4">
            <h4 class="mb-3">Agregar Nuevo Proveedor</h4>

            <form method="POST" class="row g-3">
                <div class="col-md-4">
                <label class="form-label">Nombre</label>
        <input 
            type="text" 
            name="nombre" 
            class="form-control" 
            placeholder="Ingrese el nombre del proveedor" 
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input 
            type="text" 
            name="telefono" 
            class="form-control" 
            placeholder="Ej: 333-123-4567"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Dirección</label>
        <input 
            type="text" 
            name="direccion" 
            class="form-control" 
            placeholder="Calle, número, colonia"
        >
    </div>

    <div class="col-12">
        <button class="btn btn-add mt-2">Agregar Proveedor</button>
    </div>
</form>

        </div>

        <!-- LISTADO -->
        <div class="card-box">
            <h4 class="mb-3">Listado de Proveedores</h4>

            <?php if (empty($proveedores)): ?>
                <p class="text-muted">No hay proveedores registrados.</p>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($proveedores as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><?= htmlspecialchars($p['telefono']) ?></td>
                                    <td><?= htmlspecialchars($p['direccion']) ?></td>
                                    <td><?= $p['creado_en'] ?></td>

                                    <td>
                                        <a href="proveedores.php?eliminar=1&id=<?= $p['id'] ?>"
                                           class="btn btn-sm btn-del"
                                           onclick="return confirm('¿Eliminar proveedor <?= addslashes($p['nombre']) ?>?');">
                                           Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            <?php endif; ?>

            <a href="panel.php" class="btn btn-back mt-3">Volver al Panel</a>

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
