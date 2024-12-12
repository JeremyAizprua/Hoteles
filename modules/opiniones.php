<?php
session_start();

// Inicializar mensaje como vacío
$message = '';

try {
    require_once('../config/Database.php');
    require_once('../classes/Opinion.php');
    require_once('../classes/Hotel.php');

    // Verificar si el usuario está logueado, si no redirigir al login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Crear conexiones a la base de datos
    $db = new Database();
    $conn = $db->getAdminConnection();
    $opinion = new Opinion($conn);
    $hotel = new Hotel($conn);

    // Manejar envíos del formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    // Agregar nueva opinión
                    try {
                        $hotel_id = $_POST['hotel_id'];
                        $comentario = $_POST['comentario'];
                        $usuario_id = $_SESSION['user_id']; // Se obtiene directamente de la sesión

                        // Llamar al método crear() de la clase Opinion
                        $opinion->crear($hotel_id, $usuario_id, $comentario);
                        $message = "Opinión agregada con éxito"; // Mensaje de éxito
                    } catch (Exception $e) {
                        $message = "Error al agregar la opinión: " . $e->getMessage(); // En caso de error, mostrar mensaje
                    }
                    break;

                case 'delete':
                    // Eliminar una opinión
                    if (!empty($_POST['id'])) {
                        try {
                            $opinion->id = (int) $_POST['id'];
                            if ($opinion->eliminar()) {
                                $message = "Opinión eliminada con éxito."; // Mensaje de éxito al eliminar
                            } else {
                                $message = "Error al eliminar la opinión."; // Mensaje de error si no se elimina
                            }
                        } catch (Exception $e) {
                            $message = "Error al eliminar la opinión: " . $e->getMessage(); // Capturar y mostrar error
                        }
                    } else {
                        $message = "Error: El ID es obligatorio para eliminar."; // Si no se recibe ID, mostrar mensaje de error
                    }
                    break;
            }
        }
    }

    // Obtener todas las opiniones
    $opiniones = $opinion->obtenerTodasOpiniones();
    $hoteles = $hotel->leerTodos();

} catch (Exception $e) {
    // Capturar cualquier error de conexión o del código y mostrarlo
    $message = "Error al cargar la página: " . $e->getMessage();
    exit(); // Detener la ejecución del script en caso de error
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opiniones - Hoteles Panamá</title>
    <!-- Enlace a Materialize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>
<body>

    <!-- Encabezado de la página -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo">Opiniones</a>
            <ul id="nav-mobile" class="right">
                <li><a href="../index.php">Menú Principal</a></li>
            </ul>
        </div>
    </nav>

    
    <div class="container">
        <?php if ($_SESSION['role'] !== 'editor'): ?>
        <!-- Formulario para agregar opinión -->
        <h2 class="header">Agregar Opinión</h2>
        <form method="POST">
            <div class="input-field">
                <select name="hotel_id" required>
                    <option value="" disabled selected>Seleccione un Hotel</option>
                    <?php foreach ($hoteles as $hotel): ?>
                        <option value="<?php echo $hotel['id']; ?>"><?php echo $hotel['titulo']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="hotel_id">Hotel</label>
            </div>
            <div class="input-field">
                <textarea name="comentario" id="comentario" class="materialize-textarea" required></textarea>
                <label for="comentario">Comentario</label>
            </div>
            <button type="submit" name="action" value="add" class="btn waves-effect waves-light blue darken-3">Agregar Opinión</button>
            <?php endif; ?>
        </form>

        <br>
        <!-- Muestra mensajes de éxito o error -->
            <?php if ($message): ?>
                <div class="card-panel <?php echo strpos($message, 'Error:') !== false ? 'red' : 'green'; ?> lighten-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
<br>
        <!-- Lista de opiniones existentes -->
        <h2 class="header">Opiniones Existentes</h2>
        <table class="highlight responsive-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hotel</th>
                    <th>Usuario</th>
                    <th>Comentario</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($opiniones as $o): ?>
                <tr>
                    <td><?php echo $o['id']; ?></td>
                    <td><?php echo $o['hotel_titulo']; ?></td> <!-- Muestra el título del hotel -->
                    <td><?php echo $o['nombre_usuario']; ?></td> <!-- Muestra el nombre del usuario -->
                    <td><?php echo $o['comentario']; ?></td>
                    <td><?php echo $o['fecha']; ?></td>
                    <td>
                        <!-- Formulario para eliminar opinión -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                            <button type="submit" class="btn red waves-effect waves-light">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>

    </div>

    <!-- Incluir JS de Materialize y jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <script>
        // Inicializar el dropdown del select en Materialize
        $(document).ready(function(){
            $('select').formSelect();
        });
    </script>

</body>
</html>
