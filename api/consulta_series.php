<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include('../conexion.php');

session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
$idUser = $_SESSION[$_SESSION['db'].'idUser'] ?? '';
$tdoc = isset($_POST['tdoc']) ? "AND td.id_dtab_tdc = ".$_POST['tdoc'] : '';
if (!$user) {
    echo json_encode(['error' => 'No user ID provided']);
    exit();
}

$query_serie = mysqli_query($conection, "SELECT d.id, d.des_item FROM m_usrseries m
                                         INNER JOIN d_tablas d ON m.id_dtab_srr = d.id 
                                         WHERE id_mtablas = 1 AND m.id_musuari = $idUser 
                                         ORDER BY m.swt_default DESC, m.id_dtab_srr DESC");

if (!$query_serie) {
    echo json_encode(['error' => mysqli_error($conection)]);
    exit();
}
$series = [];
while ($row = mysqli_fetch_assoc($query_serie)) {
    $series[] = $row;
    // Si tienes un número de serie por defecto, guárdalo aquí
}
$query_default = mysqli_query($conection, "SELECT COALESCE(MAX(td.num_ero), 0) + 1 AS def FROM t_doccli td INNER JOIN d_tablas dt ON td.id_dtab_srr = dt.id INNER JOIN m_usrseries m ON m.id_dtab_srr = dt.id WHERE m.id_musuari = $idUser $tdoc AND swt_default = 1;");
$valor_qd = mysqli_fetch_assoc( $query_default );
$seriePorDefecto = $valor_qd['def']; // Valor por defecto para el número de serie

echo json_encode(['series' => $series, 'seriePorDefecto' => $seriePorDefecto]);

mysqli_close($conection);
