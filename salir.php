<?php
session_start();
if (!empty($_SESSION[$_SESSION['db'].'idUser'])) {
    $user_id = $_SESSION[$_SESSION['db'].'idUser'];

    // Conexión a la base de datos
    require_once "../conexion.php";

    // Actualiza el estado a desconectado
    $stmt = $conection->prepare("UPDATE m_usuari SET is_logged_in = 0 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Cierra la conexión
    $stmt->close();
    $conection->close();
}

// Destruye la sesión
session_destroy();

// Redirige al usuario a la página de inicio
header('location: ../');
exit();
