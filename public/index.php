<?php
session_start();
require_once('../config/Database.php');
require_once('../classes/Hotel.php');

try {
    // Crear instancia de la base de datos y la clase Hotel
    $db = new Database();
    $conn = $db->getPublicConnection();
    $hotel = new Hotel($conn);

    // Configuración de paginación
    $limit = 9;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Filtros de búsqueda (nombre y ubicación)
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';

    // Obtener el número total de hoteles según los filtros
    $total_hoteles = $hotel->contarHoteles($search, $location);
    $total_pages = ceil($total_hoteles / $limit);

    // Obtener los hoteles de acuerdo con la paginación y los filtros
    $hoteles = $hotel->buscarHoteles($search, $location, $limit, $offset);
} catch (Exception $e) {
    // Manejo de errores, se captura cualquier excepción y se muestra un mensaje
    $error_message = "Error al obtener la información de los hoteles: " . $e->getMessage();
    echo "<p>$error_message</p>";
    exit; // Detener la ejecución si hay un error
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoteles Panamá - Página Principal</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f7f7f7;
    color: #333;
    }

    nav {
        background-color: #2c3e50;
    }

    .card {
        height: 600px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #ddd;
        background-color: #fff;
        border-radius: 8px;
    }

    .card-image img {
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-image:hover img {
        transform: scale(1.1);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .card-content p i {
        vertical-align: middle;
        margin-right: 5px;
        color: #2c3e50;
    }

    .hotel-title {
        font-size: 1.3rem;
        margin: 10px 0;
        font-weight: bold;
        color: #2c3e50;
        text-align: center;
    }

    .card-content {
        font-size: 0.95rem;
        text-align: justify; /* Justificar el texto */
    }


    .card-action a {
        color: #007acc;
        font-weight: bold;
    }

    .pagination .active {
        background-color: #2c3e50;
    }

    footer {
        background-color: #2c3e50;
        padding: 20px 0;
    }

    footer p {
        font-size: 0.9rem;
        margin: 0;
    }

    form input {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 10px;
    }

    form button {
        border-radius: 4px;
    }

    .pagination li.active {
        background-color: #007acc;
        color: #fff;
        border-radius: 4px;
    }

    .pagination li a {
        color: #007acc;
    }

    .pagination li.disabled a {
        color: #ccc;
    }
</style>

</head>
<body>
    <!-- Navbar -->
    <nav class="blue darken-4">
    <div class="nav-wrapper container">
        <a href="#" class="brand-logo">Hoteles Panamá</a>
        <ul class="right hide-on-med-and-down">
            <li><a href="#hoteles">Todos los Hoteles</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../modules/opinionesUsuario.php">Mis Opiniones y likes</a></li>
                <li><a href="../modules/logout.php">Cerrar Sesión</a></li>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
                    <!-- Botón para redirigir al panel de administrador o editor -->
                    <li><a href="../index.php" >Ir al Panel</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="../modules/login.php">Iniciar Sesión</a></li>
                <li><a href="../modules/registro.php">Registrarse</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

    <main class="container">
        <h2 class="center-align">Buscar Hoteles</h2>

        <!-- Formulario de Búsqueda -->
        <form method="GET" class="row">
            <div class="input-field col s12 m5">
                <i class="material-icons prefix">search</i>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>">
                <label for="search">Buscar por nombre</label>
            </div>
            <div class="input-field col s12 m5">
                <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($location); ?>">
                <label for="location">Buscar por ubicación o provincia</label>
            </div>
            <div class="input-field col s12 m2">
                <button type="submit" class="btn blue darken-4">Buscar</button>
            </div>
        </form>

        <div class="row"> 
            <?php if (count($hoteles) > 0): ?>
                <?php foreach ($hoteles as $hotel): ?>
                    <?php if ($hotel['publicar'] === 'S'): // Filtrar solo los hoteles que están publicados ?>
                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="../img/<?php echo $hotel['imagen_thumbnail']; ?>" alt="<?php echo $hotel['titulo']; ?>">
                                </div>
                                <div class="card-content">
                                    <h5 class="hotel-title"><?php echo $hotel['titulo']; ?></h5>
                                    <p><?php echo substr($hotel['descripcion'], 0, 300) . '...'; ?></p><br>
                                    <p>
                                        <i class="material-icons">place</i>
                                        <?php echo htmlspecialchars($hotel['ubicacion'] . ", " . $hotel['provincia']); ?>
                                    </p>
                                </div>
                                <div class="card-action">
                                    <a href="detalle_hotel.php?id=<?php echo $hotel['id']; ?>">
                                        <i class="material-icons">info_outline</i> Ver más
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="center-align">No se encontraron resultados.</p>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <ul class="pagination center-align">
            <li class="<?php echo $page <= 1 ? 'disabled' : 'waves-effect'; ?>">
                <a href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo $search; ?>&location=<?php echo $location; ?>"><i class="material-icons">chevron_left</i></a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="<?php echo $page == $i ? 'active blue' : 'waves-effect'; ?>">
                    <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&location=<?php echo $location; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="<?php echo $page >= $total_pages ? 'disabled' : 'waves-effect'; ?>">
                <a href="?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo $search; ?>&location=<?php echo $location; ?>"><i class="material-icons">chevron_right</i></a>
            </li>
        </ul>
    </main>

    <!-- Footer -->
    <footer class="blue darken-4">
        <div class="container">
            <p class="white-text center-align">&copy; 2024 Hoteles Panamá</p>
        </div>
    </footer>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
