<?php
session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include "../conexion.php";

$userId = $_POST['userId'] ?? '';
$module =  $_POST['module'] ?? '';
$opc =  $_POST['opc'] ?? '';
$permissions =  $_POST['permissions'] ?? [];

if (!$userId) {
    echo json_encode(['error' => 'User ID not provided']);
    exit();
}

// Evita la inyección SQL utilizando consultas preparadas
$query = $conection->prepare("DELETE u.* FROM d_usuari u INNER JOIN d_dopcion o ON u.id_dopcion = o.id WHERE u.id_musuari = ? AND o.id_mopcion = ? AND o.id_dopcion = ?");
$query->bind_param("iii", $userId, $module, $opc);
$query->execute();
$query->close();

// Inserta nuevos permisos
foreach ($permissions as $permission) {
    // Asegúrate de que la inserción sea correcta según la estructura de la tabla
    $query = $conection->prepare("INSERT INTO d_usuari (ID_MUSUARI, ID_DOPCION, FAV_ITEM, LEC_ITEM) VALUES (?, ?, NULL, 0)");
    $query->bind_param("ii", $userId, $permission);
    $query->execute();
    $query->close();
}

echo json_encode(['message' => 'Permissions updated successfully']);
