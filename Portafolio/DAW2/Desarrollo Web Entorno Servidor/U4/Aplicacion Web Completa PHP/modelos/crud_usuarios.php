<?php
// Importamos la conexión de la base de datos.
include_once "conexion_bd.php";

// Al haber importado el script, tenemos acceso a sus métodos y funciones, los usamos.
try {
    // Obtener la instancia de la clase ConexionBaseDeDatos
    $baseDeDatos = ConexionBaseDeDatos::obtenerInstancia();
    // Obtener la conexión de la base de datos desde la instancia.
    $conexion = $baseDeDatos->obtenerConexion();
} catch (Exception $e) {
    // Manejo de errores más específico
    die("Error: No se ha podido conectar a la base de datos, " . $e->getMessage());
}

class UsuarioCRUD {
    // Método para verificar las credenciales del usuario
    public static function verificarCredenciales($usuario, $contrasena) {
        // Obtenemos la conexión a la base de datos
        $conexion = ConexionBaseDeDatos::obtenerInstancia()->obtenerConexion();

        // Preparamos la consulta SQL en la conexión a la base de datos.
        $sql = $conexion -> prepare("SELECT * FROM usuarios WHERE nombre_usuario = :usuario LIMIT 1");

        // Con bindParam remplazamos los valores de ":usuario" con los recibidos en el argumento del método.
        $sql -> bindParam(':usuario', $usuario, PDO::PARAM_STR);

        // Ejecutamos la consulta
        $sql -> execute();

        // "Jalamos" del "sql" todos los usuarios que hayan coincidido con la consulta.
        $resultado = $sql -> fetch(PDO::FETCH_ASSOC);

        // Verificamos si se encontró un usuario y se compara la contraseña hasheada.
        if ($resultado && password_verify($contrasena, $resultado['contrasena'])) {
            return $resultado; // Devuelve los datos del usuario si la contraseña coincide
        } else {
            return false; // Retorna false si las credenciales no son válidas
        }
    }

    // Método para registrar un nuevo usuario (CRUD)
    public static function registrarUsuario($usuario, $contrasena) {
        // Obtenemos la conexión a la base de datos
        $db = ConexionBaseDeDatos::obtenerInstancia()->obtenerConexion();

        // Verificamos si el nombre de usuario ya existe
        $sql = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :usuario");
        $sql->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $sql->execute();
        
        $usuarioExistente = $sql->fetchColumn();

        if ($usuarioExistente > 0) {
            return "El nombre de usuario ya está en uso."; // El usuario ya existe
        }

        // Encriptamos la contraseña
        $contrasenaHash = password_hash($contrasena, PASSWORD_BCRYPT);

        // Preparamos la consulta de inserción
        $sql = $db->prepare("INSERT INTO usuarios (nombre_usuario, contrasena) VALUES (:usuario, :contrasena)");
        $sql->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $sql->bindParam(':contrasena', $contrasenaHash, PDO::PARAM_STR);

        // Ejecutamos la consulta
        if ($sql->execute()) {
            return true; // Registro exitoso
        } else {
            return "Error al registrar el usuario."; // Error en el registro
        }
    }

    // Método para actualizar la contraseña de un usuario
    public static function actualizarContrasena($usuario, $nuevaContrasena) {
        // Obtenemos la conexión a la base de datos
        $db = ConexionBaseDeDatos::obtenerInstancia()->obtenerConexion();

        // Encriptamos la nueva contraseña
        $contrasenaHash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);

        // Preparamos la consulta de actualización
        $sql = $db->prepare("UPDATE usuarios SET contrasena = :contrasena WHERE nombre_usuario = :usuario");
        $sql->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $sql->bindParam(':contrasena', $contrasenaHash, PDO::PARAM_STR);

        // Ejecutamos la consulta
        if ($sql->execute()) {
            return true; // Actualización exitosa
        } else {
            return "Error al actualizar la contraseña."; // Error en la actualización
        }
    }

    // Método para eliminar un usuario
    public static function eliminarUsuario($usuario) {
        // Obtenemos la conexión a la base de datos
        $db = ConexionBaseDeDatos::obtenerInstancia()->obtenerConexion();

        // Preparamos la consulta de eliminación
        $sql = $db->prepare("DELETE FROM usuarios WHERE nombre_usuario = :usuario");
        $sql->bindParam(':usuario', $usuario, PDO::PARAM_STR);

        // Ejecutamos la consulta
        if ($sql->execute()) {
            return true; // Eliminación exitosa
        } else {
            return "Error al eliminar el usuario."; // Error en la eliminación
        }
    }
}