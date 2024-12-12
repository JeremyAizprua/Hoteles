<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Hotel.php');
require_once('../classes/CategoriaHotel.php');

// Verificar si el usuario está logueado y tiene el rol de 'admin' o 'editor'
try {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
        header('Location: login.php');
        exit();
    }

    $db = new Database();
    $conn = $db->getAdminConnection();
    $hotel = new Hotel($conn);
    $categoria = new CategoriaHotel($conn);

    // Verificar si el ID del hotel está presente en la URL
    if (isset($_GET['id'])) {
        $hotel_id = $_GET['id'];
        // Obtener los datos del hotel
        $hotel_data = $hotel->leerPorId($hotel_id);
        if (!$hotel_data) {
            header('Location: hoteles.php'); // Redirigir si no se encuentra el hotel
            exit();
        }
    } else {
        header('Location: hoteles.php'); // Redirigir si no se proporciona el ID del hotel
        exit();
    }

    // Procesar la solicitud POST para actualizar los datos del hotel
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
        // Asignar valores a las propiedades del objeto hotel
        $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
        $ubicacion = isset($_POST['ubicacion']) ? $_POST['ubicacion'] : '';
        $provincia = isset($_POST['provincia']) ? $_POST['provincia'] : '';
        $costo = isset($_POST['costo']) ? $_POST['costo'] : '';
        $categoria_id = isset($_POST['categoria_id']) ? $_POST['categoria_id'] : '';
        $publicar = isset($_POST['publicar']) ? $_POST['publicar'] : '';
        $activo = isset($_POST['activo']) ? $_POST['activo'] : '';

        // Inicializar las variables para las imágenes
        $imagen_thumbnail = $hotel_data['imagen_thumbnail']; // Usar la imagen actual por defecto
        $imagen_grande = $hotel_data['imagen_grande']; // Usar la imagen actual por defecto

        // Manejo de subida de imágenes
        if (!empty($_FILES['imagen_thumbnail']['name'])) {
            $imagen_thumbnail = $_FILES['imagen_thumbnail']['name'];
            move_uploaded_file($_FILES['imagen_thumbnail']['tmp_name'], "../img/$imagen_thumbnail");
        }

        if (!empty($_FILES['imagen_grande']['name'])) {
            $imagen_grande = $_FILES['imagen_grande']['name'];
            move_uploaded_file($_FILES['imagen_grande']['tmp_name'], "../img/$imagen_grande");
        }

        // Llamada al método de actualización del hotel
        $hotel->id = $hotel_id; // Asignar el ID del hotel
        if ($hotel->actualizar($hotel_id, $titulo, $descripcion, $ubicacion, $provincia, $costo, $categoria_id, $publicar, $activo, $imagen_thumbnail, $imagen_grande)) {
            // Redirigir al mismo formulario con un mensaje de éxito
            header("Location: editar_hotel.php?id=$hotel_id&success=1");
            exit();
        }
    }

    // Obtener las categorías disponibles
    $categorias = $categoria->leer();
} catch (Exception $e) {
    // En caso de error, capturar la excepción y mostrar el mensaje
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Hotel - Hoteles Panamá</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
        body {
        font-family: 'Montserrat', sans-serif;
            background-color: #f0f0f0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 50px auto;
        }
        .form-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container img {
            display: block;
            margin: 10px auto;
            max-width: 100px;
        }
    </style>
</head>
<body>
<nav class="blue darken-3">
    <div class="nav-wrapper container">
        <a href="#" class="brand-logo">Editar Hotel <?php echo $hotel_data['titulo']; ?></a>
        <ul id="nav-mobile" class="right">
            <li><a href="hoteles.php">Volver</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="form-container z-depth-3">
        <h1>Editar Hotel</h1>
        <!-- Formulario de edición del hotel -->
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $hotel_data['id']; ?>">

            <div class="input-field">
                <input type="text" id="titulo" name="titulo" value="<?php echo $hotel_data['titulo']; ?>" required>
                <label for="titulo">Título</label>
            </div>

            <div class="input-field">
                <textarea id="descripcion" name="descripcion" class="materialize-textarea" required><?php echo $hotel_data['descripcion']; ?></textarea>
                <label for="descripcion">Descripción</label>
            </div>

            <div class="input-field">
                <input type="text" id="ubicacion" name="ubicacion" value="<?php echo $hotel_data['ubicacion']; ?>">
                <label for="ubicacion">Ubicación</label>
            </div>

            <div class="input-field">
                <input type="text" id="provincia" name="provincia" value="<?php echo $hotel_data['provincia']; ?>">
                <label for="provincia">Provincia</label>
            </div>

            <div class="input-field">
                <input type="number" id="costo" name="costo" step="0.01" value="<?php echo $hotel_data['costo']; ?>">
                <label for="costo">Costo</label>
            </div>

            <div class="input-field">
                <select name="categoria_id">
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $hotel_data['categoria_id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['nombre_categoria']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Categoría</label>
            </div>

            <div class="input-field">
                <select name="publicar">
                    <option value="S" <?php echo $hotel_data['publicar'] == 'S' ? 'selected' : ''; ?>>Publicar</option>
                    <option value="N" <?php echo $hotel_data['publicar'] == 'N' ? 'selected' : ''; ?>>No Publicar</option>
                </select>
                <label>Publicar</label>
            </div>
            <div class="input-field">
                <select name="activo">
                    <option value="activo" <?php echo $hotel_data['activo'] == 'N' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $hotel_data['activo'] == 'N' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <label>Estado</label>
            </div>           
            <div class="file-field input-field">
                <div class="btn">
                    <span>Imagen Thumbnail</span>
                    <input type="file" name="imagen_thumbnail" accept="image/*">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text">
                </div>
                <img src="../img/<?php echo $hotel_data['imagen_thumbnail']; ?>" alt="Thumbnail">
            </div>

            <div class="file-field input-field">
                <div class="btn">
                    <span>Imagen Grande</span>
                    <input type="file" name="imagen_grande" accept="image/*">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text">
                </div>
                <img src="../img/<?php echo $hotel_data['imagen_grande']; ?>" alt="Imagen Grande">
            </div>

            <div class="center">
                <button type="submit" class="btn blue">Actualizar Hotel</button>
            </div>
        </form>

        <?php if (isset($success)): ?>
            <p class="green-text"><?php echo $success; ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Materialize JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var selects = document.querySelectorAll('select');
        M.FormSelect.init(selects);
    });
</script>
</body>
</html>
