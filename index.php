<?php
session_start();

// Verificar si el usuario está logueado
try {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../modules/login.php');
        exit();
    }

    // Verificar el rol del usuario
    $is_admin = ($_SESSION['role'] === 'admin');
    $is_editor = ($_SESSION['role'] === 'editor');

    // Incluir la conexión a la base de datos y el seguimiento de visitas
    require_once 'config/Database.php';
    require_once 'classes/Visita.php';

    // Establecer una conexión pública a la base de datos
    $database = new Database();
    $db = $database->getAdminConnection();

    // Instanciar la clase Visita
    $visita = new Visita($db);

    // Obtener el total de visitas por día, mes y año
    $total_visitas_dia = $visita->obtenerVisitasPorDia();
    $total_visitas_mes = $visita->obtenerVisitasPorMes();
    $total_visitas_ano = $visita->obtenerVisitasPorAno();
} catch (Exception $e) {
    // Si ocurre un error, mostrar mensaje y salir
    echo "Error al obtener datos: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Hoteles Panamá</title>

    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            margin-top: 30px;
            flex-grow: 1;
        }
        .chart-container {
            max-width: 800px;
            margin: 0 auto;
            margin-top: 30px;
        }
        footer {
            background-color: #263238;
            padding: 10px;
            color: white;
            text-align: center;
        }
        nav {
            background-color: #2c3e50;
        }
        .nav-wrapper .brand-logo {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <nav class="blue darken-3">
        <div class="nav-wrapper container">
            <a href="#" class="brand-logo">Hoteles Panamá</a>
            <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <?php if ($is_admin): ?>
                    <li><a href="modules/usuarios.php">Gestión de Usuarios</a></li>
                <?php endif; ?>
                <?php if ($is_admin || $is_editor): ?>
                    <li><a href="modules/categorias.php">Gestión de Categorías</a></li>
                    <li><a href="modules/hoteles.php">Gestión de Hoteles</a></li>
                <?php endif; ?>
                <li><a href="modules/opiniones.php">Gestión de Opiniones</a></li>
                <li><a href="public/index.php">Ver Sitio Público</a></li>
                <li><a href="modules/logout.php">Cerrar Sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container main-content">
        <h2 class="center-align">Bienvenido, <?php echo $_SESSION['username']; ?></h2>
        <p class="center-align">Selecciona una opción del menú para comenzar.</p>

        <!-- Gráfica combinada -->
        <div class="chart-container">
            <canvas id="chartVisitas"></canvas>
        <?php echo "Visitas por día: ".$total_visitas_dia ?><br><br>
        <?php echo "Visitas por mes: ".$total_visitas_mes ?><br><br>
        <?php echo "Visitas por año: ".$total_visitas_ano ?>
        </div> 
    </div>

    <!-- Footer -->
    <footer class="blue darken-3">
        <p>&copy; 2023 Hoteles Panamá. Todos los derechos reservados.</p>
    </footer>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <script>
        try {
            // Datos para la gráfica
            const visitasDia = <?php echo $total_visitas_dia; ?>;
            const visitasMes = <?php echo $total_visitas_mes; ?>;
            const visitasAno = <?php echo $total_visitas_ano; ?>;

            // Crear la gráfica combinada
            new Chart(document.getElementById("chartVisitas"), {
                type: 'bar',
                data: {
                    labels: ['Día', 'Mes', 'Año'], // Etiquetas de los parámetros
                    datasets: [
                        {
                            label: 'Visitas',
                            data: [visitasDia, visitasMes, visitasAno],
                            backgroundColor: [
                                'rgba(63, 81, 181, 0.6)',
                                'rgba(33, 150, 243, 0.6)',
                                'rgba(0, 188, 212, 0.6)'
                            ],
                            borderColor: [
                                'rgba(63, 81, 181, 1)',
                                'rgba(33, 150, 243, 1)',
                                'rgba(0, 188, 212, 1)'
                            ],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } catch (error) {
            console.error("Error al cargar la gráfica: " + error.message);
        }
    </script>
</body>
</html>
