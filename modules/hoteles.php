<?php 
session_start();
require_once('../config/Database.php');
require_once('../classes/Hotel.php');
require_once('../classes/CategoriaHotel.php');

// Inicializar la variable de mensaje
$message = '';

// Comprobar si el usuario está logueado y tiene rol de administrador o editor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header('Location: login.php');
    exit();
}

try {
    // Establecer la conexión con la base de datos
    $db = new Database();
    $conn = $db->getAdminConnection();
    $hotel = new Hotel($conn);
    $categoria = new CategoriaHotel($conn);

    // Obtener las categorías activas de hoteles
    $categorias = $categoria->leerActivas(); 

    // Manejar las solicitudes POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    // Manejo de la carga de imágenes
                    $imagen_thumbnail = '';
                    $imagen_grande = '';

                    // Verificar y mover la imagen de la miniatura
                    if (isset($_FILES['imagen_thumbnail']) && $_FILES['imagen_thumbnail']['error'] == 0) {
                        $target_dir = "../img/";
                        $imagen_thumbnail = time() . "_" . basename($_FILES["imagen_thumbnail"]["name"]);
                        $target_file = $target_dir . $imagen_thumbnail;
                        
                        // Validar el tipo de imagen antes de moverla
                        if (in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
                            move_uploaded_file($_FILES["imagen_thumbnail"]["tmp_name"], $target_file);
                        }
                    }

                    // Verificar y mover la imagen grande
                    if (isset($_FILES['imagen_grande']) && $_FILES['imagen_grande']['error'] == 0) {
                        $target_dir = "../img/";
                        $imagen_grande = time() . "_" . basename($_FILES["imagen_grande"]["name"]);
                        $target_file = $target_dir . $imagen_grande;
                        
                        // Validar el tipo de imagen antes de moverla
                        if (in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
                            move_uploaded_file($_FILES["imagen_grande"]["tmp_name"], $target_file);
                        }
                    }

                    // Crear la entrada del hotel
                    if ($hotel->crear($_POST['titulo'], $_POST['descripcion'], $_POST['ubicacion'], $_POST['provincia'], $_POST['costo'], $_POST['categoria_id'], $_SESSION['user_id'], $_POST['publicar'], $_POST['activo'], $imagen_thumbnail, $imagen_grande)) {
                        $message = "Hotel añadido con éxito!";
                    } else {
                        $message = "Error al añadir el hotel.";
                    }
                    break;
                case 'delete':
                    // Eliminar el hotel
                    $hotel->eliminar($_POST['id']);
                    $message = "Hotel eliminado con éxito!";
                    break;
            }
        }
    }

    // Obtener todos los hoteles
    $hoteles = $hotel->leer();
    $categorias = $categoria->leerActivas();

} catch (Exception $e) {
    // En caso de error, se muestra el mensaje de la excepción
    $message = "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Hoteles - Hoteles Panamá</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Google Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    body {
    font-family: 'Montserrat', sans-serif;}
</style>
</head>
<body>
    <!-- Header -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo">Hoteles Panamá</a>
            <ul id="nav-mobile" class="right">
                <li><a href="../index.php">Menú Principal</a></li>
                <li><a href="../modules/busqueda.php">Buscar Hoteles</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
                <!-- Muestra mensajes de éxito o error -->
                <?php if ($message): ?>
                <div class="card-panel <?php echo strpos($message, 'Error:') !== false ? 'red' : 'green'; ?> lighten-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        <!-- Formulario para Agregar Hotel -->
        <?php if ($_SESSION['role'] !== 'editor'): ?>
        <h4>Agregar Hotel</h4>
        <form method="POST" enctype="multipart/form-data" class="card-panel">
            <input type="hidden" name="action" value="add">
            
            <div class="input-field">
                <i class="material-icons prefix">title</i>
                <input type="text" name="titulo" id="titulo" required>
                <label for="titulo">Título</label>
            </div>

            <div class="input-field">
                <i class="material-icons prefix">description</i>
                <textarea name="descripcion" id="descripcion" class="materialize-textarea" required></textarea>
                <label for="descripcion">Descripción</label>
            </div>

            <div class="input-field">
                <i class="material-icons prefix">place</i>
                <input type="text" name="ubicacion" id="ubicacion">
                <label for="ubicacion">Ubicación</label>
            </div>

            <div class="input-field">
                <i class="material-icons prefix">location_city</i>
                <input type="text" name="provincia" id="provincia">
                <label for="provincia">Provincia</label>
            </div>

            <div class="input-field">
                <i class="material-icons prefix">attach_money</i>
                <input type="number" name="costo" id="costo" step="0.01">
                <label for="costo">Costo</label>
            </div>

            <div class="input-field">
                <i class="material-icons prefix">category</i>
                <select name="categoria_id">
                    <option value="" disabled selected>Seleccionar Categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nombre_categoria']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Categoría</label>
            </div>

            <div class="input-field">
                <i class="material-icons prefix">visibility</i>
                <select name="publicar">
                    <option value="S">Publicar</option>
                    <option value="N">No Publicar</option>
                </select>
                <label>Estado de Publicación</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">check_circle</i>
                <select name="activo">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <label>Estado de Actividad</label>
            </div>           
            <div class="file-field input-field">
                <div class="btn">
                    <span><i class="material-icons">image</i> Miniatura</span>
                    <input type="file" name="imagen_thumbnail" accept="image/*">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text" placeholder="Selecciona una imagen">
                </div>
            </div>

            <div class="file-field input-field">
                <div class="btn">
                    <span><i class="material-icons">image</i> Imagen Grande</span>
                    <input type="file" name="imagen_grande" accept="image/*">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text" placeholder="Selecciona una imagen">
                </div>
            </div>

            <button type="submit" class="btn waves-effect waves-light blue darken-3">
                <i class="material-icons left">add</i>Agregar Hotel
            </button>
        </form>
        <?php endif; ?>

        <!-- Listado de Hoteles -->
        <h4>Hoteles Existentes</h4>
        <table class="striped highlight responsive-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Ubicación</th>
                    <th>Provincia</th>
                    <th>Costo</th>
                    <th>Categoría</th>
                    <th>Creado por</th>
                    <th>Publicar</th>
                    <th>Estado</th>
                    <th>Imágenes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hoteles as $h): ?>
                <tr>
                    <td><?php echo $h['id']; ?></td>
                    <td><?php echo $h['titulo']; ?></td>
                    <td><?php echo $h['ubicacion']; ?></td>
                    <td><?php echo $h['provincia']; ?></td>
                    <td><?php echo $h['costo']; ?></td>
                    <td><?php echo $h['nombre_categoria']; ?></td>
                    <td><?php echo $h['creador']; ?></td>
                    <td style="color: <?php echo $h['publicar'] == 'S' ? 'green' : 'red'; ?>;">
                        <strong><?php echo htmlspecialchars($h['publicar']); ?></strong>
                    </td>
                    <td style="color: <?php echo $h['activo'] == 'activo' ? 'green' : 'red'; ?>;">
                        <strong><?php echo htmlspecialchars($h['activo']); ?></strong>
                    </td>
                    <td>
                       
                        <?php if ($h['imagen_thumbnail']): ?>
                            <img src="../img/<?php echo $h['imagen_thumbnail']; ?>" alt="Miniatura" width="50">
                        <?php endif; ?>
                        <?php if ($h['imagen_grande']): ?>
                            <img src="../img/<?php echo $h['imagen_grande']; ?>" alt="Imagen Grande" width="50">
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
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit(); // Inicializa componentes Materialize
        });
    </script>
</body>
</html>
