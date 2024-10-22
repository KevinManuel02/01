<?php
session_start();
require_once "../conexion.php";

$user_id = $_SESSION[$_SESSION['db'].'idUser'];

$query = mysqli_query($conection, "SELECT last_activity FROM m_usuari WHERE id = $user_id");
$last = mysqli_fetch_assoc($query);

if (isset($user_id) && $last !== null) {
    $last_activity = $last['last_activity'];

    $last_activity_time = strtotime($last_activity);

    $current_time = time();
    $inactive_time = $current_time - $last_activity_time;

    if ($inactive_time > 30*60) {
        $update_query = mysqli_query($conection, "UPDATE m_usuari SET is_logged_in = 0 WHERE id = $user_id");

        if ($update_query) {
            echo "Estado actualizado a desconectado para el usuario con ID $user_id.";
        } else {
            echo "Error al actualizar el estado a desconectado para el usuario con ID $user_id.";
        }
    } else {
        echo "El usuario está activo.";
    }
} else {
    echo "El usuario no está conectado.";
}

mysqli_close($conection);
