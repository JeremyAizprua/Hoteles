<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Usuario.php');
require_once('../classes/Funciones.php');

try {
    // Verifica si el usuario está autenticado y tiene el rol 'admin'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }

    // Conexión a la base de datos
    $db = new Database();
    $conn = $db->getAdminConnection();
    $usuario = new Usuario($conn);

    // Verifica si el parámetro 'id' está presente en la URL
    if (!isset($_GET['id'])) {
        header('Location: usuarios.php');
        exit();
    }

    // Obtiene los datos del usuario a editar
    $usuarioAEditar = $usuario->obtenerPorId($_GET['id']);
    if (!$usuarioAEditar) {
        header('Location: usuarios.php');
        exit();
    }

    $errores = []; // Array para almacenar los errores

    // Lógica para actualizar el usuario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recibe los datos del formulario
        $nombre_usuario = sanitizarEntrada($_POST['nombre_usuario']);
        $email = sanitizarEntrada($_POST['email']);
        $rol = sanitizarEntrada($_POST['rol']);
        $estado = sanitizarEntrada($_POST['estado']);

        // Validar nombre de usuario
        if ($errorNombre = validarCampo(
            $nombre_usuario,
            '/^\w{1,50}$/',
            'El nombre de usuario debe tener entre 1 y 50 caracteres alfanuméricos.'
        )) {
            $errores[] = $errorNombre;
        }

        // Validar correo electrónico
        if (!validarCorreo($email)) {
            $errores[] = "El correo electrónico no es válido.";
        }

        // Si no hay errores, intenta actualizar el usuario
        if (empty($errores)) {
            if ($usuario->actualizar($_GET['id'], $nombre_usuario, $email, $rol, $estado)) {
                header('Location: usuarios.php?success=1');
                exit();
            } else {
                $errores[] = "Error al actualizar el usuario.";
            }
        }
    }
} catch (Exception $e) {
    $errores[] = "Ocurrió un error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo">Editar Usuario</a>
            <ul id="nav-mobile" class="right">
                <li><a href="usuarios.php">Volver</a></li>
            </ul>
        </div>
    </nav>

    <!-- Contenedor del formulario -->
    <div class="container">
        <h4>Editar Usuario</h4>
        <!-- Muestra los mensajes de error si existen -->
        <?php if (!empty($errores)): ?>
            <ul class="red-text">
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Formulario para editar usuario -->
        <form method="POST" class="row">
            <div class="input-field col s12">
                <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($usuarioAEditar['nombre_usuario']); ?>" required>
                <label for="nombre_usuario">Nombre de Usuario</label>
            </div>
            <div class="input-field col s12">
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuarioAEditar['email']); ?>" required>
                <label for="email">Email</label>
            </div>
            <div class="input-field col s12">
                <select id="rol" name="rol">
                    <option value="admin" <?php echo $usuarioAEditar['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="editor" <?php echo $usuarioAEditar['rol'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                    <option value="public" <?php echo $usuarioAEditar['rol'] === 'public' ? 'selected' : ''; ?>>Public</option>
                </select>
                <label for="rol">Rol</label>
            </div>
            <div class="input-field col s12">
                <select id="estado" name="estado">
                    <option value="activo" <?php echo $usuarioAEditar['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $usuarioAEditar['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <label for="estado">Estado</label>
            </div>
            <div class="center">
                <button type="submit" class="btn blue">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <!-- Script de Materialize para inicializar los selects -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.FormSelect.init(document.querySelectorAll('select'));
        });
    </script>
</body>
</html>
