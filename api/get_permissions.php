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

include '../conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userId'] ?? '';
$opc = $input['opc'] ?? '';
$module = $input['module'] ?? '';

if (!$userId) {
    echo json_encode(['error' => 'User ID not provided']);
    exit();
}

if (!$opc) {
    echo json_encode(['error' => 'Option not provided']);
    exit();
}

if (!$module) {
    echo json_encode(['error' => 'Module not provided']);
    exit();
}

// Using prepared statements to prevent SQL injection
$stmtUserPermissions = $conection->prepare("SELECT u.id_dopcion, u.lec_item FROM d_usuari u INNER JOIN d_dopcion d ON u.id_dopcion = d.id WHERE u.id_musuari = ? AND d.swt_opc = 1");
$stmtUserPermissions->bind_param('i', $userId);
$stmtUserPermissions->execute();
$resultUserPermissions = $stmtUserPermissions->get_result();

$userPermissions = [];
$lecPermissions = [];
while ($rowUserPermissions = $resultUserPermissions->fetch_assoc()) {
    $userPermissions[] = $rowUserPermissions['id_dopcion'];
    $lecPermissions[$rowUserPermissions['id_dopcion']] = $rowUserPermissions['lec_item'];
}

$stmtAllPermissions = $conection->prepare("SELECT d.id, d.des_item, m.id as idm, m.des_item as des_opc, d.num_cor FROM d_dopcion d LEFT JOIN d_opcion m ON m.id = d.id_dopcion WHERE d.id_dopcion = ? AND d.id_mopcion = ? AND d.swt_opc = 1 ORDER BY d.num_cor");
$stmtAllPermissions->bind_param('ii', $opc, $module);
$stmtAllPermissions->execute();
$resultAllPermissions = $stmtAllPermissions->get_result();

$permissions = [];
while ($rowAllPermissions = $resultAllPermissions->fetch_assoc()) {
    $permission = [
        'id' => $rowAllPermissions['id'],
        'des_item' => $rowAllPermissions['des_item'],
        'id_mopcion' => $rowAllPermissions['idm'],
        'des_opc' => $rowAllPermissions['des_opc'],
        'num_cor' => $rowAllPermissions['num_cor'],
        'selected' => in_array($rowAllPermissions['id'], $userPermissions) ? true : false,
        'lec_item' => $lecPermissions[$rowAllPermissions['id']] ?? 0
    ];
    $permissions[] = $permission;
}

$response = [
    'userPermissions' => $userPermissions,
    'allPermissions' => $permissions
];

echo json_encode($response);
