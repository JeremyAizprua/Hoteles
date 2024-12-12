<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Usuario.php');
require_once('../classes/Funciones.php');

// Establecer la conexión a la base de datos
try {
    $db = new Database();
    $conn = $db->getLoginConnection();  // Obtener la conexión a la base de datos
    $usuario = new Usuario($conn);  // Crear objeto Usuario

} catch (Exception $e) {
    // Si ocurre un error al establecer la conexión o cargar las clases, mostrar mensaje de error
    echo "Error al conectar con la base de datos: " . $e->getMessage();
    exit();
}

$error = '';  // Variable para almacenar errores
$success = '';  // Variable para almacenar mensajes de éxito

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Sanitizar las entradas del formulario para prevenir inyecciones de código
        $nombre_usuario = sanitizarEntrada($_POST['nombre_usuario']);
        $email = sanitizarEntrada($_POST['email']);
        $password = sanitizarEntrada($_POST['password']);
        $confirm_password = sanitizarEntrada($_POST['confirm_password']);

        // Validar entradas
        $errores = [];

        // Validar nombre de usuario
        if ($errorNombre = validarCampo($nombre_usuario, '/^\w{1,50}$/', 'El nombre de usuario debe tener entre 1 y 50 caracteres alfanuméricos.')) {
            $errores[] = $errorNombre;
        }

        // Validar correo electrónico
        if (!validarCorreo($email)) {
            $errores[] = "El correo electrónico no es válido.";
        }

        // Validar las contraseñas
        if (!$password || !$confirm_password) {
            $errores[] = "Las contraseñas son obligatorias.";
        } elseif ($password !== $confirm_password) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        // Si no hay errores, proceder a registrar al usuario
        if (empty($errores)) {
            $result = $usuario->crear($nombre_usuario, $password, $email, 'public');
            if ($result) {
                $success = "Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $error = "El usuario o correo ya están en uso.";
            }
        } else {
            $error = implode('<br>', $errores);
        }
    } catch (Exception $e) {
        // Si ocurre algún error durante el proceso de registro, capturamos el error
        $error = "Ocurrió un error al procesar el registro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Hoteles Panamá</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
        font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #f0f0ef;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            font-size: 24px;
            color: #1e88e5;
            text-align: center;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .container button {
            background-color: #1e88e5;
            width: 100%;
            font-weight: bold;
        }
        .container button:hover {
            background-color: #1565c0;
        }
        p {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        p a {
            color: #1e88e5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container z-depth-2">
        <h2><i class="material-icons">person_add</i> Registro de Usuario</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-field">
                <i class="material-icons prefix">account_circle</i>
                <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                <label for="nombre_usuario">Nombre de Usuario</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">email</i>
                <input type="email" id="email" name="email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">lock</i>
                <input type="password" id="password" name="password" required>
                <label for="password">Contraseña</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">lock</i>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <label for="confirm_password">Confirmar Contraseña</label>
            </div>
            <button type="submit" class="btn waves-effect waves-light">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a></p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
