<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Opinion.php');
require_once('../classes/Hotel.php');
require_once('../classes/Like.php'); // Asegúrate de incluir la clase Like

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = ''; // Variable para almacenar mensajes

try {
    // Conexión a la base de datos
    $db = new Database();
    $conn = $db->getLoginConnection();
    $opinion = new Opinion($conn);
    $hotel = new Hotel($conn);
    $like = new Like($conn); // Instancia de la clase Like

    // Manejo de envíos del formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    try {
                        // Agregar una nueva opinión
                        $hotel_id = $_POST['hotel_id'];
                        $comentario = $_POST['comentario'];
                        $usuario_id = $_SESSION['user_id']; // Obtener ID del usuario desde la sesión
                        $opinion->crear($hotel_id, $usuario_id, $comentario); // Llamar a la función para crear la opinión
                        $message = "Opinión agregada con éxito.";
                    } catch (Exception $e) {
                        // Capturar errores al agregar la opinión
                        $message = "Error al agregar la opinión: " . $e->getMessage();
                    }
                    break;

                case 'delete':
                    try {
                        if (!empty($_POST['id'])) {
                            $opinion->id = (int) $_POST['id'];
                            if ($opinion->eliminar()) {
                                $message = "Opinión eliminada con éxito.";
                            } else {
                                $message = "Error al eliminar la opinión.";
                            }
                        } else {
                            $message = "Error: El ID es obligatorio para eliminar.";
                        }
                    } catch (Exception $e) {
                        // Capturar errores al eliminar la opinión
                        $message = "Error al eliminar la opinión: " . $e->getMessage();
                    }
                    break;
            }
        }
    }

    // Obtener las opiniones del usuario logueado
    $opiniones = $opinion->obtenerOpinionesPorUsuario($_SESSION['user_id']);
    // Obtener todos los hoteles para el formulario
    $hoteles = $hotel->leerTodos();

} catch (Exception $e) {
    // Capturar errores generales de conexión o de inicialización
    $message = "Error al cargar los datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opiniones - Hoteles Panamá</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Montserrat', sans-serif;
    }
</style>
</head>
<body>

    <!-- Barra de navegación -->
    <nav>
        <div class="nav-wrapper blue darken-4">
            <a href="#" class="brand-logo center">Mis Opiniones</a>
        </div>
    </nav>

    <div class="container">

        <br>
        <!-- Muestra mensajes de éxito o error -->
        <?php if (!empty($message)): ?>
            <div class="card-panel <?php echo strpos($message, 'Error') !== false ? 'red' : 'green'; ?> lighten-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Listado de opiniones del usuario logueado -->
        <h2 class="header">Mis Opiniones</h2>
        <table class="highlight responsive-table">
            <thead>
                <tr>
  
                </tr>
            </thead>
            <tbody>
                <?php if (count($opiniones) > 0): ?>
                    <?php foreach ($opiniones as $o): ?>
                        <tr>
                            <td><?php echo $o['hotel_titulo']; ?></td>
                            <td><?php echo $o['comentario']; ?></td>
                            <td><?php echo $o['fecha']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                    <button type="submit" class="btn red waves-effect waves-light">Eliminar</button>
                                </form>
                            </td>
                            <td>
                                <?php
                                    $hotel_id = $o['hotel_id'];
                                    $user_id = $_SESSION['user_id'];

                                    // Verificar si el usuario ya ha dado like al hotel
                                    if ($like->hasUserLiked($hotel_id, $user_id)) {
                                        echo "Has dado like a este hotel";
                                    } else {
                                        echo "No has dado like";
                                    }

                                    // Mostrar el total de likes
                                    $total_likes = $like->getTotalLikes($hotel_id);
                                    echo "<br>Total Likes: " . $total_likes;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No has agregado opiniones aún.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <a href="../public/index.php" class="btn waves-effect waves-light blue darken-4">Volver al Menú Principal</a>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <script>
        $(document).ready(function(){
            $('select').formSelect();
        });
    </script>

</body>
</html>
