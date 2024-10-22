<?php
session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
include "../conexion.php";

$response = [];
$linea = $_POST['linea'] ?? '-NF';
$medida = $_POST['medida'] ?? '-NF';
$marca = $_POST['marca'] ?? '-NF';
$lista = $_POST['lista'] ?? '-NF';

// Fetch options for Linea
$query_line = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas=17 AND swt_item = 1 ORDER BY num_item ASC");
$optionLinea = '';
while ($row = mysqli_fetch_assoc($query_line)) {
    $des_item = $row['des_item'] ?? ''; // Handle null values
    $selected = $row['id'] == $linea ? 'selected' : '';
    $optionLinea .= "<option value='{$row['id']}' {$selected}>{$des_item}</option>";
}
$response['optionLinea'] = $optionLinea;

// Fetch options for Unidad de Medida
$query_med = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas=6 AND swt_item = 1 ORDER BY num_item ASC");
$optionMedida = '';
while ($row = mysqli_fetch_assoc($query_med)) {
    $des_item = $row['des_item'] ?? ''; // Handle null values
    $selected = $row['id'] == $medida ? 'selected' : '';
    $optionMedida .= "<option value='{$row['id']}' {$selected}>{$des_item}</option>";
}
$response['optionMedida'] = $optionMedida;

// Fetch options for Marca
$query_marca = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas=99 AND swt_item = 1 ORDER BY num_item ASC");
$optionMarca = '';
while ($row = mysqli_fetch_assoc($query_marca)) {
    $des_item = $row['des_item'] ?? ''; // Handle null values
    $selected = $row['id'] == $marca ? 'selected' : '';
    $optionMarca .= "<option value='{$row['id']}' {$selected}>{$des_item}</option>";
}
$response['optionMarca'] = $optionMarca;

// Fetch options for Listas
$query_lista = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas=53 AND swt_item = 1 ORDER BY num_item ASC");
$optionLista = '';
while ($row = mysqli_fetch_assoc($query_lista)) {
    $des_item = $row['des_item'] ?? ''; // Handle null values
    $selected = $row['id'] == $lista ? 'selected' : '';
    $optionLista .= "<option value='{$row['id']}' {$selected}>{$des_item}</option>";
}
$response['optionLista'] = $optionLista;

echo json_encode($response);
mysqli_close($conection);
