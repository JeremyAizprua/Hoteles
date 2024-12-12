<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Usuario.php');

// Verificar si el usuario está logueado y es administrador
try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }
} catch (Exception $e) {
    // Manejo de errores si ocurre alguna excepción
    echo "Error de sesión: " . $e->getMessage();
    exit();
}

$db = new Database();
$conn = $db->getLoginConnection();
$usuario = new Usuario($conn);

$usuarioAEditar = null;
$error = null; // Inicialización de la variable de error
$success = null; // Inicialización de la variable de éxito

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        switch ($_POST['action']) {
            case 'add':
                // Agregar un nuevo usuario
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $nombre_usuario = $_POST['nombre_usuario'];
                    $email = $_POST['email'];
                    $password = $_POST['password'];
                    $rol = $_POST['rol']; // Definir el campo de rol

                    // Validar los campos requeridos
                    if (empty($nombre_usuario) || empty($email) || empty($password)) {
                        $error = "Todos los campos son obligatorios.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = "El formato del email no es válido.";
                    } else {
                        // Verificar si el nombre de usuario o el correo electrónico ya existen
                        $result = $usuario->crear($nombre_usuario, $password, $email, $rol);
                        if ($result) {
                            $success = "Registro exitoso. Ahora puedes iniciar sesión.";
                        } else {
                            $error = "Error al registrar el usuario. El nombre de usuario o email ya puede estar en uso.";
                        }
                    }
                }
                break;
            case 'edit':
                // Editar un usuario existente
                $usuario->actualizar($_POST['id'], $_POST['nombre_usuario'], $_POST['email'], $_POST['rol'], $_POST['estado']);
                break;
            case 'delete':
                // Eliminar un usuario
                $usuario->eliminar($_POST['id']);
                break;
        }
    } catch (Exception $e) {
        // Manejo de excepciones si alguna acción falla
        $error = "Error al procesar la acción: " . $e->getMessage();
    }
}

// Obtener todos los usuarios
try {
    $usuarios = $usuario->leer();
} catch (Exception $e) {
    // Manejo de errores si ocurre una excepción al leer los usuarios
    $error = "Error al obtener los usuarios: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Hoteles Panamá</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        .actions form {
            display: inline;
        }
        .btn-small {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Cabecera -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo"><i class="material-icons">people</i> Gestión de Usuarios</a>
            <ul id="nav-mobile" class="right">
                <li><a href="../index.php"><i class="material-icons left">home</i> Menú Principal</a></li>
            </ul>
        </div>
    </nav>

    

    <div class="container">
        <!-- Formulario para agregar un usuario -->
        <h2><i class="material-icons">person_add</i> <?php echo $usuarioAEditar ? 'Editar Usuario' : 'Agregar Usuario'; ?></h2>
        <form method="POST" class="row">
            <input type="hidden" name="action" value="<?php echo $usuarioAEditar ? 'edit' : 'add'; ?>">
            <?php if ($usuarioAEditar): ?>
                <input type="hidden" name="id" value="<?php echo $usuarioAEditar['id']; ?>">
            <?php endif; ?>
            <div class="input-field col s12">
                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Nombre de Usuario" 
                    value="<?php echo $usuarioAEditar['nombre_usuario'] ?? ''; ?>" required>
                <label for="nombre_usuario">Nombre de Usuario</label>
            </div>
            <?php if (!$usuarioAEditar): ?>
                <div class="input-field col s12">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <label for="password">Contraseña</label>
                </div>
            <?php endif; ?>
            <div class="input-field col s12">
                <input type="email" id="email" name="email" placeholder="Email" 
                    value="<?php echo $usuarioAEditar['email'] ?? ''; ?>" required>
                <label for="email">Email</label>
            </div>
            <div class="input-field col s12">
                <select name="rol">
                    <option value="admin" <?php echo ($usuarioAEditar['rol'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="editor" <?php echo ($usuarioAEditar['rol'] ?? '') == 'editor' ? 'selected' : ''; ?>>Editor</option>
                    <option value="public" <?php echo ($usuarioAEditar['rol'] ?? '') == 'public' ? 'selected' : ''; ?>>Public</option>
                </select>
                <label>Rol</label>
            </div>
            <?php if ($usuarioAEditar): ?>
                <div class="input-field col s12">
                    <select name="estado">
                        <option value="activo" <?php echo ($usuarioAEditar['estado'] ?? '') == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo ($usuarioAEditar['estado'] ?? '') == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                    <label>Estado</label>
                </div>
            <?php endif; ?>
            <div class="col s12 center">
                <button type="submit" class="btn blue"><i class="material-icons left">save</i><?php echo $usuarioAEditar ? 'Guardar Cambios' : 'Agregar Usuario'; ?></button>
            </div>
        </form>
        <!-- Muestra mensajes de éxito o error -->
            <?php if ($error): ?>
                <div class="card-panel red lighten-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="card-panel green lighten-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
        <!-- Lista de Usuarios -->
        <h2><i class="material-icons">group</i> Usuarios Existentes</h2>
        <table class="highlight centered responsive-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['nombre_usuario']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['rol']; ?></td>
                    <td><?php echo $user['estado']; ?></td>
                    <td class="actions">
                        <a href="editar_usuario.php?id=<?php echo $user['id']; ?>" class="btn-small blue">
                            <i class="material-icons">edit</i>
                        </a>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn-small red">
                                <i class="material-icons">delete</i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        $(document).ready(function() {
            $('select').formSelect();
        });
    </script>
</body>
</html>
