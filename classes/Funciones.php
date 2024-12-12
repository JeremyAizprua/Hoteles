<?php
// Función para sanitizar la entrada del usuario
// Esta función elimina espacios en blanco al inicio y final, 
// quita barras invertidas y convierte caracteres especiales en entidades HTML
function sanitizarEntrada($data) {
    try {
        return htmlspecialchars(trim(stripslashes($data)));
    } catch (Exception $e) {
        error_log("Error en sanitizarEntrada: " . $e->getMessage());
        throw new Exception("Error al sanitizar la entrada.");
    }
}

// Función para validar correos electrónicos
// Utiliza el filtro FILTER_VALIDATE_EMAIL para verificar si un correo es válido
function validarCorreo($correo) {
    try {
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log("Error en validarCorreo: " . $e->getMessage());
        throw new Exception("Error al validar el correo.");
    }
}

// Función genérica para validar un campo con una expresión regular
// Parámetros:
// - $campo: El valor del campo a validar
// - $regex: La expresión regular para la validación
// - $mensajeError: Mensaje de error que se devuelve si la validación falla
// Retorna null si la validación es exitosa o el mensaje de error si falla
function validarCampo($campo, $regex, $mensajeError) {
    try {
        if (preg_match($regex, $campo)) {
            return null;
        } else {
            return $mensajeError;
        }
    } catch (Exception $e) {
        error_log("Error en validarCampo: " . $e->getMessage());
        throw new Exception("Error al validar el campo.");
    }
}
?>
