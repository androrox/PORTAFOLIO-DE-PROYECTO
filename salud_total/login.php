<?php
session_start();

require_once 'db_conexion.php';

$error_mensaje = '';

// Si ya hay sesión activa, redirigir
if (isset($_SESSION['usuario_id'])) {
    header('Location: panel.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitización de Entradas
    // Recoger y limpiar el email. El parámetro 's' de bind_param ayuda contra inyección SQL.
    $email = trim($_POST['email'] ?? '');
    $clave_ingresada = $_POST['clave'] ?? ''; 

    // 2. Consulta Parametrizada
    // Usamos consultas preparadas para prevenir la Inyección SQL
    $sql = "SELECT id, nombre, clave, rol FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    
    // Si la preparación falla, se debe manejar el error
    if (!$stmt) {
        $error_mensaje = 'Error de sistema. Inténtelo más tarde.';
    } else {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // 3. Verificación Segura de Contraseña
            // **CLAVE DE SEGURIDAD:** Usamos password_verify para comparar la clave ingresada 
            // con el hash almacenado.
            if (password_verify($clave_ingresada, $usuario['clave'])) {
                
                // 4. Crear Sesión y Prevención de Fijación de Sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                // Regenerar el ID de sesión para prevenir ataques de fijación
                session_regenerate_id(true);

                header('Location: panel.php');
                exit();

            } else {
                // Mensaje genérico para prevenir enumeración de usuarios
                $error_mensaje = 'Email o contraseña incorrectos.';
            }
        } else {
            // Mensaje genérico si no se encuentra el usuario
            $error_mensaje = 'Email o contraseña incorrectos.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Inventario - Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --color-accent: #8a0022;
            --color-bg: #e9ecef;
        }
        body { 
            background-color: var(--color-bg); 
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            width: 100%; max-width: 400px;
            border-top: 8px solid var(--color-accent);
        }
        .card-title { color: var(--color-accent); font-weight: 700; }
        .btn-login {
            background-color: var(--color-accent);
            border-color: var(--color-accent);
            font-weight: bold;
        }
        .btn-login:hover { background-color: #6d001a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-auto">
                <div class="login-card text-center">
                    <i class="bi bi-crosshair display-3 text-danger"></i>
                    <h2 class="card-title mt-2">Acceso al Inventario</h2>
                    <p class="text-muted">Gestión de Farmacia Salud Total</p>

                    <?php if ($error_mensaje): ?>
                        <div class="alert alert-danger"><b><?php echo htmlspecialchars($error_mensaje); ?></b></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3 text-start">
                            <label class="fw-bold" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg" required 
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"> 
                        </div>

                        <div class="mb-4 text-start">
                            <label class="fw-bold" for="clave">Contraseña</label>
                            <input type="password" id="clave" name="clave" class="form-control form-control-lg" required>
                        </div>

                        <button type="submit" class="btn btn-login btn-lg w-100">Iniciar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php 
// Asegúrate de cerrar la conexión al final del script
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close(); 
}
?>