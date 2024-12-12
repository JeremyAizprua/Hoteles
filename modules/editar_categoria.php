<?php
// Incluir archivos de configuración y clases necesarias
require_once('../config/Database.php');
require_once('../classes/CategoriaHotel.php');

// Verificar si se ha pasado el ID de la categoría a editar
if (!isset($_GET['id'])) {
    echo "Error: ID de categoría no especificado.";
    exit();
}

$id = (int)$_GET['id']; // Convertir el ID a entero para evitar inyecciones SQL

try {
    // Crear conexión a la base de datos
    $db = new Database();
    $conn = $db->getAdminConnection(); // Obtener conexión para administrador
    $categoria = new CategoriaHotel($conn); // Crear objeto de la clase CategoriaHotel

    // Obtener los datos de la categoría con el ID proporcionado
    $datos_categoria = $categoria->obtenerPorId($id);

    // Verificar si se encontraron los datos de la categoría
    if (!$datos_categoria) {
        echo "Error: Categoría no encontrada.";
        exit();
    }

    // Procesar la actualización de la categoría si se envió el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Verificar que los campos necesarios no estén vacíos
        if (!empty($_POST['nombre_categoria']) && isset($_POST['estado'])) {
            $categoria->id = $id; // Asignar el ID de la categoría
            $categoria->nombre_categoria = trim($_POST['nombre_categoria']); // Asignar el nombre de la categoría
            $categoria->estado = trim($_POST['estado']); // Asignar el estado de la categoría

            // Intentar actualizar la categoría en la base de datos
            if ($categoria->actualizar()) {
                header("Location: categorias.php"); // Redirigir a la lista de categorías
                exit();
            } else {
                echo "Error al actualizar la categoría."; // Mensaje en caso de fallo
            }
        } else {
            echo "Error: Todos los campos son obligatorios."; // Mensaje si algún campo está vacío
        }
    }
} catch (Exception $e) {
    // Capturar y mostrar cualquier error ocurrido durante la ejecución del código
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría</title>
    <!-- Incluir Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
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
            <a href="#" class="brand-logo">Editar Categoría</a>
            <ul id="nav-mobile" class="right">
                <li><a href="categorias.php">Volver</a></li> <!-- Enlace para volver a la lista de categorías -->
            </ul>
        </div>
    </nav>
    <!-- Contenedor principal -->
    <div class="container">
        <h4>Editar Categoría</h4>
        <form method="POST" class="row">
            <!-- Campo para el nombre de la categoría -->
            <div class="input-field col s12">
                <input type="text" id="nombre_categoria" name="nombre_categoria" value="<?php echo $datos_categoria['nombre_categoria']; ?>" required>
                <label for="nombre_categoria">Nombre de Categoría</label>
            </div>
            <!-- Campo para seleccionar el estado de la categoría -->
            <div class="input-field col s12">
                <select name="estado">
                    <option value="activo" <?php echo $datos_categoria['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $datos_categoria['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
                <label>Estado</label>
            </div>
            <!-- Botón para guardar los cambios -->
            <div class="center">
                <button type="submit" class="btn blue">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <!-- Incluir Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        // Inicializar los select de Materialize
        document.addEventListener('DOMContentLoaded', function() {
            M.FormSelect.init(document.querySelectorAll('select'));
        });
    </script>
</body>
</html>
