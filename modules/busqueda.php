<?php
// Inicia la sesión del usuario
session_start();

// Se incluyen los archivos necesarios para la conexión a la base de datos y la clase Hotel
require_once('../config/Database.php');
require_once('../classes/Hotel.php');

// Crea una instancia de la conexión a la base de datos
$db = new Database();
$conn = $db->getAdminConnection();

// Crea una instancia de la clase Hotel
$hotel = new Hotel($conn);

// Inicializa variables para los resultados de búsqueda y mensajes
$resultados = [];
$message = '';

// Manejo de búsqueda
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['buscar'])) {
    // Obtiene el término de búsqueda desde el formulario
    $termino = $_GET['termino'];
    $resultados = $hotel->buscar($termino); // Busca hoteles basados en el término ingresado
}

try {
    // Manejo de búsqueda con protección contra errores
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['buscar'])) {
        $termino = $_GET['termino'];
        $resultados = $hotel->buscar($termino);
    }

    // Manejo de eliminación de hoteles
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
        // Verifica que se haya enviado el ID del hotel y que el usuario no sea "editor"
        if (isset($_POST['id']) && $_SESSION['role'] !== 'editor') {
            $id = $_POST['id'];
            if ($hotel->eliminar($id)) {
                // Muestra mensaje de éxito y actualiza los resultados de búsqueda
                $message = "Hotel eliminado con éxito.";
                if (isset($_GET['termino'])) {
                    $resultados = $hotel->buscar($_GET['termino']);
                }
            } else {
                // Muestra mensaje de error si no se pudo eliminar
                $message = "Error al eliminar el hotel.";
            }
        }
    }
} catch (Exception $e) {
    // Captura errores inesperados y los registra en el log de errores
    error_log("Error inesperado: " . $e->getMessage());
    $message = "Ocurrió un error. Por favor, intenta de nuevo más tarde.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos básicos -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Hoteles - Hoteles Panamá</title>

    <!-- Estilos de Materialize -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Íconos de Google -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    body {
    font-family: 'Montserrat', sans-serif;}
</style>
</head>
<body>

    <!-- Barra de navegación -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo center">Búsqueda de Hoteles</a>
            <div>
                <!-- Botón para volver a la lista de hoteles -->
                <a href="../modules/hoteles.php" class="btn waves-effect waves-light blue">Volver</a>
            </div>
        </div>
    </nav>
<br><br>
    <div class="container">

        <!-- Muestra mensajes de éxito o error -->
        <?php if ($message): ?>
            <div class="card-panel <?php echo strpos($message, 'Error') !== false ? 'red' : 'green'; ?> lighten-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <form method="GET" class="row">
            <div class="input-field col s12 m8 l10">
                <input type="text" name="termino" id="termino" placeholder="Buscar por nombre o provincia" required value="<?php echo isset($_GET['termino']) ? htmlspecialchars($_GET['termino']) : ''; ?>">
                <label for="termino">Buscar</label>
            </div>
            <div class="input-field col s12 m4 l2">
                <button type="submit" name="buscar" class="btn waves-effect waves-light blue darken-4">Buscar</button>
            </div>
        </form>

        <!-- Resultados de búsqueda -->
        <?php if (!empty($resultados)): ?>
        <h2>Resultados de la búsqueda</h2>
        <h4>Hoteles Existentes</h4>
        <table class="striped highlight responsive-table">
            <thead>
                <tr>
                    <!-- Encabezados de la tabla -->
                    <th>Título</th>
                    <th>Ubicación</th>
                    <th>Provincia</th>
                    <th>Costo</th>
                    <th>Categoría</th>
                    <th>Creado por</th>
                    <th>Publicar</th>
                    <th>Imágenes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Itera sobre los resultados de búsqueda y muestra cada hotel -->
                <?php foreach ($resultados as $h): ?>
                <tr>
                    <td><?php echo $h['titulo']; ?></td>
                    <td><?php echo $h['ubicacion']; ?></td>
                    <td><?php echo $h['provincia']; ?></td>
                    <td><?php echo $h['costo']; ?></td>
                    <td><?php echo $h['nombre_categoria']; ?></td>
                    <td><?php echo $h['creador']; ?></td>
                    <td><?php echo $h['publicar']; ?></td>
                    <td>
                        <!-- Muestra las imágenes si existen -->
                        <?php if ($h['imagen_thumbnail']): ?>
                            <img src="../img/<?php echo $h['imagen_thumbnail']; ?>" alt="Thumbnail" class="responsive-img" width="100">
                        <?php endif; ?>
                        <?php if ($h['imagen_grande']): ?>
                            <img src="../img/<?php echo $h['imagen_grande']; ?>" alt="Grande" class="responsive-img" width="100">
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Botón para editar hotel -->
                        <form method="GET" action="editar_hotel.php" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                            <button type="submit" class="btn-small blue">
                                <i class="material-icons">edit</i>
                            </button>
                        </form>

                        <!-- Botón para eliminar hotel -->
                        <?php if ($_SESSION['role'] !== 'editor'): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este hotel?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                            <button type="submit" class="btn-small red">
                                <i class="material-icons">delete</i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['buscar'])): ?>
        <p>No se encontraron resultados.</p>
    <?php endif; ?>

    </div>

    <!-- Scripts de Materialize y jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <script>
        // Inicializa los componentes de Materialize (opcional)
        $(document).ready(function(){
            // Inicializaciones adicionales si son necesarias
        });
    </script>
</body>
</html>
