<?php
session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $apiUrl = "https://api.apis.net.pe/v1/tipo-cambio-sunat?fecha=$fecha";

    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($ch);

    // Verificar si hubo algún error
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Devolver la respuesta al frontend
        header('Content-Type: application/json');
        echo $response;
    }

    // Cerrar la sesión cURL
    curl_close($ch);
} else {
    echo json_encode(['error' => 'Fecha no proporcionada']);
}
