<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/CategoriaHotel.php');

// Verificar si el usuario está logueado y si es admin o editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header('Location: login.php');
    exit();  
}

$message = ""; // Variable para almacenar los mensajes de éxito o error

try {
    // Crear una nueva instancia de la clase Database y CategoriaHotel
    $db = new Database();
    $conn = $db->getAdminConnection();
    $categoria = new CategoriaHotel($conn);

    // Manejar las solicitudes POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                // Acción para agregar una nueva categoría
                case 'add':
                    if (!empty($_POST['nombre_categoria'])) {
                        $categoria->nombre_categoria = trim($_POST['nombre_categoria']);
                        $categoria->estado = 'activo'; // Asigna un estado predeterminado
                        if ($categoria->crear()) {
                            $message = "Categoría agregada con éxito.";
                        } else {
                            $message = "Error al agregar la categoría.";
                        }
                    } else {
                        $message = "Error: El nombre de la categoría es obligatorio.";
                    }
                    break;

                // Acción para editar una categoría existente
                case 'edit':
                    if (!empty($_POST['id']) && !empty($_POST['nombre_categoria']) && isset($_POST['estado'])) {
                        $categoria->id = (int) $_POST['id'];
                        $categoria->nombre_categoria = trim($_POST['nombre_categoria']);
                        $categoria->estado = trim($_POST['estado']);
                        if ($categoria->actualizar()) {
                            $message = "Categoría actualizada con éxito.";
                        } else {
                            $message = "Error al actualizar la categoría.";
                        }
                    } else {
                        $message = "Error: Todos los campos son obligatorios para editar.";
                    }
                    break;

                // Acción para eliminar una categoría
                case 'delete':
                    if (!empty($_POST['id'])) {
                        $categoria->id = (int) $_POST['id'];
                        if ($categoria->eliminar()) {
                            $message = "Categoría eliminada con éxito.";
                        } else {
                            $message = "Error al eliminar la categoría.";
                        }
                    } else {
                        $message = "Error: El ID es obligatorio para eliminar.";
                    }
                    break;

                // Acción no reconocida
                default:
                    $message = "Error: Acción no reconocida.";
                    break;
            }
        }
    }

    // Obtener todas las categorías existentes
    $categorias = $categoria->leer();
} catch (Exception $e) {
    // Capturar y manejar cualquier error inesperado
    error_log("Error inesperado: " . $e->getMessage());
    $message = "Ocurrió un error. Por favor, intenta de nuevo más tarde.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Hoteles Panamá</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Iconos Materialize -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    body {
    font-family: 'Montserrat', sans-serif;}
</style>
</head>
<body class="white">

    <!-- Header -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo">Categorias</a>
            <ul id="nav-mobile" class="right">
                <li><a href="../index.php">Menú Principal</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($_SESSION['role'] !== 'editor'): ?>
        <!-- Formulario para agregar una nueva categoría -->
        <h2 class="header">Agregar Categoría</h2>
        <form method="POST">
            <div class="input-field">
                <input id="nombre_categoria" name="nombre_categoria" type="text" required>
                <label for="nombre_categoria">Nombre de Categoría</label>
            </div>
            <button type="submit" name="action" value="add" class="btn waves-effect waves-light blue">
                Agregar Categoría
                <i class="material-icons right">add</i>
            </button> 
            <?php endif; ?>
        </form>

        <br>
        <!-- Muestra mensajes de éxito o error -->
        <?php if ($message): ?>
            <div class="card-panel <?php echo strpos($message, 'Error') !== false ? 'red' : 'green'; ?> lighten-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Listado de categorías existentes -->
        <h2 class="header">Categorías Existentes</h2>
        <table class="highlight responsive-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td><?php echo $cat['id']; ?></td>
                    <td><?php echo $cat['nombre_categoria']; ?></td>
                    <td><?php echo $cat['estado']; ?></td>
                    <td>
                        <!-- Botón de edición -->
                        <form method="GET" action="editar_categoria.php" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                            <button type="submit" class="btn-small waves-effect waves-light blue">
                                <i class="material-icons">edit</i>
                            </button>
                        </form>
                        
                        <!-- Botón de eliminación -->
                        <?php if ($_SESSION['role'] !== 'editor'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn-small waves-effect waves-light red">
                                    <i class="material-icons">delete</i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
