<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Usuario.php');
require_once('../classes/Funciones.php');
require_once('../classes/Visita.php');
$db = new Database();
$conn = $db->getLoginConnection();
$usuario = new Usuario($conn);
$visita = new Visita($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Sanitizar entradas
        $username = sanitizarEntrada($_POST['username']);
        $password = sanitizarEntrada($_POST['password']);

        // Validar entradas
        $errores = [];
        if (!$username) $errores[] = "El nombre de usuario es obligatorio.";
        if (!$password) $errores[] = "La contraseña es obligatoria.";

        if (empty($errores)) {
            try {
                // Intentar hacer login con los datos proporcionados
                if ($usuario->login($username, $password)) {
                    // Si el login es exitoso, se inicia la sesión
                    $_SESSION['user_id'] = $usuario->id;
                    $_SESSION['username'] = $usuario->nombre_usuario;
                    $_SESSION['role'] = $usuario->rol;
                    $visita->registrarVisita();
                    // Redirigir según el rol del usuario
                    header('Location: ' . ($usuario->rol == 'admin' ? '../index.php' : '../public/index.php'));
                    exit();
                } else {
                    // Si el login falla, mostrar mensaje de error
                    $error = "Usuario o contraseña incorrectos.";
                }
            } catch (Exception $e) {
                // Capturar cualquier error durante el proceso de login
                $error = "Error al intentar iniciar sesión: " . $e->getMessage();
            }
        } else {
            // Si hay errores de validación de entradas, mostrarlos
            $error = implode('<br>', $errores);
        }
    } catch (Exception $e) {
        // Capturar cualquier otro error general
        $error = "Ocurrió un error al procesar la solicitud: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hoteles Panamá</title>
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
        .login-container {
            background-color: #f0f0ef;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            font-size: 24px;
            color: #1e88e5;
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-container input {
            margin-bottom: 20px;
        }
        .login-container button {
            background-color: #1e88e5;
            width: 100%;
            font-weight: bold;
        }
        .login-container button:hover {
            background-color: #1565c0;
        }
        .login-container p {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .login-container p a {
            color: #1e88e5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container z-depth-2">
        <h2><i class="material-icons">lock</i> Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="input-field">
                <i class="material-icons prefix">person</i>
                <input type="text" id="username" name="username" placeholder="Username" required>
                <label for="username">Usuario</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">lock</i>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <label for="password">Contraseña</label>
            </div>
            <button type="submit" class="btn waves-effect waves-light">Iniciar sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
