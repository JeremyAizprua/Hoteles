<?php
session_start();

// Conexión a la base de datos y clases necesarias
require_once('../config/Database.php');
require_once('../classes/Hotel.php');
require_once('../classes/Opinion.php');
require_once('../classes/Like.php'); // Incluir la clase Like

// Establecer conexión con la base de datos y crear instancias de las clases
$db = new Database();
$conn = $db->getLoginConnection();
$hotel = new Hotel($conn);
$opinion = new Opinion($conn);
$like = new Like($conn); // Crear instancia de la clase Like

try {
    // Verificar si se ha recibido el ID del hotel desde la URL
    if (isset($_GET['id'])) {
        $hotel_id = $_GET['id'];
        
        // Obtener los detalles del hotel
        $hotel_data = $hotel->leerDetallesHotel($hotel_id);
        if (!$hotel_data) {
            // Si no se encuentra el hotel, redirigir a la lista de hoteles
            header('Location: hoteles.php');
            exit();
        }

        // Obtener las opiniones del hotel
        $opiniones = $opinion->leerPorHotel($hotel_id);

        // Obtener el total de likes para el hotel
        $total_likes = $like->getTotalLikes($hotel_id);

        // Verificar si el usuario ya ha dado like al hotel (solo si está logueado)
        if (isset($_SESSION['user_id'])) {
            $user_liked = $like->hasUserLiked($hotel_id, $_SESSION['user_id']);
        } else {
            $user_liked = false; // Si no está logueado, consideramos que no ha dado like
        }

        // Manejar el envío de un nuevo comentario (solo si está logueado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario']) && isset($_SESSION['user_id'])) {
            $usuario_id = $_SESSION['user_id'];
            $comentario = trim($_POST['comentario']);

            if (!empty($comentario)) {
                // Crear el nuevo comentario en la base de datos
                $opinion->crear($hotel_id, $usuario_id, $comentario);
                header('Location: ' . $_SERVER['REQUEST_URI']); // Recargar la página para mostrar el nuevo comentario
                exit();
            }
        }

        // Manejar la acción de dar like al hotel (solo si está logueado)
        if (isset($_POST['like']) && isset($_SESSION['user_id'])) {
            $usuario_id = $_SESSION['user_id'];
            if (!$user_liked) {
                // Si el usuario no ha dado like, agregar el like
                $like->addLike($hotel_id, $usuario_id);
                header('Location: ' . $_SERVER['REQUEST_URI']); // Recargar para actualizar los likes
                exit();
            }
        }
    } else {
        // Si no se ha recibido el ID del hotel, redirigir a la lista de hoteles
        header('Location: hoteles.php');
        exit();
    }
} catch (Exception $e) {
    // En caso de error, redirigir a la página de hoteles con un mensaje de error
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Hotel - Hoteles Panamá</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">


<style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f9f9f9;
        }
        
        .hotel-title {
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: bold;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-image img {
            border-radius: 10px 10px 0 0;
        }
        .card-content p {
            font-size: 1rem;
            color: #555;
            text-align: justify; /* Justificar el texto */
        }
        .comments-section, .add-comment-form {
            margin-top: 40px;
        }
        .like-button button {
            margin-top: 20px;
            background-color: #42A4BA;
            color: white;
            font-weight: bold;
            border-radius: 5px;
        }
        .like-button p {
            color: #42A4BA;
        }
        footer {
            background-color: #2c3e50;
            padding: 10px;
            color: white;
            text-align: center;
        }
        footer p {
            margin: 0;
        }
    </style>
</head>
<body>

<header>
    <nav class="blue darken-4">
        <div class="nav-wrapper container">
            <a href="../public/index.php" class="brand-logo center">Hoteles Panamá</a>
            <ul id="nav-mobile" class="right">
                <li><a href="../public/index.php"><i class="material-icons left">home</i> Menú Principal</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../modules/logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="../modules/login.php">Iniciar Sesión</a></li>
                <li><a href="../modules/registro.php">Registrarse</a></li>
            <?php endif; ?>
            </ul>
            
        </div>
    </nav>
</header>

<main class="container">
    <h2 class="center-align hotel-title"><?php echo htmlspecialchars($hotel_data['titulo']); ?></h2>

    <div class="row">
        <div class="col s12 m6">
            <div class="card">
                <div class="card-image">
                    <img src="../img/<?php echo htmlspecialchars($hotel_data['imagen_thumbnail']); ?>" alt="Imagen del hotel" class="responsive-img">
                </div>
                <div class="card-content">
                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($hotel_data['descripcion']); ?></p><br>
                    <p>
                        <strong><i class="fas fa-map-marker-alt icon"></i>Ubicación:</strong> 
                        <?php echo htmlspecialchars($hotel_data['ubicacion'] . ", Provincia: " . $hotel_data['provincia']); ?>
                    </p><br>
                    <p>
                        <strong><i class="fas fa-dollar-sign icon"></i>Costo:</strong> 
                        $<?php echo number_format($hotel_data['costo'], 2); ?>
                    </p><br>
                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($hotel_data['nombre_categoria']); ?></p> <br>
                    <p>
                        <strong>Estado:</strong> 
                        <span style="color: <?php echo $hotel_data['activo'] == 'activo' ? 'green' : 'red'; ?>;">
                            <strong><?php echo htmlspecialchars($hotel_data['activo']); ?></strong>
                        </span>
                    </p><br>
                    <!-- Botón de Like -->
                    <form method="POST" class="like-button">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($user_liked): ?>
                                <p><strong>¡Ya has dado like a este hotel!</strong></p>
                            <?php else: ?>
                                <button type="submit" name="like" class="btn-floating btn-large blue darken-4">
                                    <i class="material-icons">thumb_up</i>
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <p><strong>Inicia sesión para dar like a este hotel.</strong></p>
                        <?php endif; ?>
                    </form>
                    <p><strong>Likes:</strong> <?php echo $total_likes; ?></p>
                </div>
            </div>
        </div>

        <div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <h5>Galería de Imágenes</h5>
                    <img src="../img/<?php echo htmlspecialchars($hotel_data['imagen_grande']); ?>" alt="Imagen thumbnail" class="responsive-img">
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Opiniones -->
    <div class="comments-section">
        <h4>Opiniones</h4>
        <?php if (!empty($opiniones)): ?>
            <?php foreach ($opiniones as $opinion): ?>
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><i class="material-icons">person</i> <?php echo htmlspecialchars($opinion['nombre_usuario']); ?></span>
                        <p><?php echo htmlspecialchars($opinion['comentario']); ?></p>
                        <small class="grey-text">Publicado el <?php echo htmlspecialchars($opinion['fecha']); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="grey-text">No hay opiniones para este hotel aún.</p>
        <?php endif; ?>
    </div>

    <!-- Formulario para añadir un comentario (solo si está logueado) -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="add-comment-form">
            <h5>Añadir un Comentario</h5>
            <form method="POST">
                <div class="input-field">
                    <textarea id="comentario" name="comentario" class="materialize-textarea" required></textarea>
                    <label for="comentario">Tu Comentario</label>
                </div>
                <button type="submit" class="btn blue darken-4">Enviar Comentario</button> 
            </form>
        </div><br> <br>
    <?php else: ?>
        <p><strong>Inicia sesión para añadir un comentario.</strong></p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2024 Hoteles Panamá. Todos los derechos reservados.</p>
</footer>

<!-- Scripts de Materialize -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
