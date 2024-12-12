<?php
session_start();

try {
    // Destruir todas las variables de sesión
    $_SESSION = array();

    // Eliminar la sesión del servidor
    if (session_destroy()) {
        // Si la sesión se destruye correctamente, redirigir al usuario
        header("Location: ../public/index.php");
        exit();
    } else {
        // Si hay un problema al destruir la sesión, lanzar una excepción
        throw new Exception("Hubo un problema al destruir la sesión.");
    }
} catch (Exception $e) {
    // Capturar cualquier error y mostrarlo
    echo "Error: " . $e->getMessage();
    exit(); // Salir del script en caso de error
}
?>
