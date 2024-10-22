<?php
session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include '../conexion.php';

$selectedTdocId = null;
$selectedUbiId = null;

$selectedTpcId = null;
$selectedVenId = null;
$selectedCpgId = null;
$selectedMonId = null;

$email = null;
$id = $_POST['id']??false;
if ($id) {
    if (isset($_POST['swt'])) {
        if($_POST['swt']==1){
            $query_info = mysqli_query($conection, "SELECT id_dtab_tdoc, id_mubigeo, ruc_prv, des_prv, tel_prv, dir_prv FROM m_provee WHERE id = $id");
            
            if ($query_info && mysqli_num_rows($query_info) > 0) {
                $clientInfo = mysqli_fetch_assoc($query_info);
                $selectedTdocId = $clientInfo['id_dtab_tdoc'];
                $selectedUbiId = $clientInfo['id_mubigeo'];
            }
        }
        
    }else {
            $query_info = mysqli_query($conection, "SELECT id_dtab_tdoc, id_mubigeo, ruc_cli, des_cli, tel_cli, dir_cli,ID_DTAB_TCLI,ID_MVENDED,ID_DTAB_CPAG,ID_DTAB_MON,EMA_CLI FROM m_client WHERE id = $id");
            
            if ($query_info && mysqli_num_rows($query_info) > 0) {
                $clientInfo = mysqli_fetch_assoc($query_info);
                $selectedTdocId = $clientInfo['id_dtab_tdoc'];
                $selectedUbiId = $clientInfo['id_mubigeo'];
                
                $selectedTpcId = $clientInfo['ID_DTAB_TCLI'];
                $selectedVenId = $clientInfo['ID_MVENDED'];
                $selectedCpgId = $clientInfo['ID_DTAB_CPAG'];
                $selectedMonId = $clientInfo['ID_DTAB_MON'];

                $email = $clientInfo['EMA_CLI'];
            }
        }
}


//TIPO DE DOCUMENTO
$query_tdoc = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 58 ORDER BY num_item ASC");

if (!$query_tdoc) {
    echo json_encode(['error' => mysqli_error($conection)]);
    mysqli_close($conection);
    exit();
}

$optionsTdoc = '<div class="modal_item"><label for="tipo_documento">Tipo de documento:*</label>
        <select class="select_pedido" name="tipo_documento" id="tipo_documento">';
while ($row = mysqli_fetch_assoc($query_tdoc)) {
    $selected = ($row['id'] == $selectedTdocId) ? 'selected' : '';
    $optionsTdoc .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['des_item'] . '</option>';
}
$optionsTdoc .= '</select></div>';


//UBIGEO
$query_ubi = mysqli_query($conection, "SELECT id, ubigeo, departamento, provincia, distrito FROM m_ubigeo ORDER BY distrito ASC");

if (!$query_ubi) {
    echo json_encode(['error' => mysqli_error($conection)]);
    mysqli_close($conection);
    exit();
}

$optionsUbi = '<div class="modal_item"><label for="tipo_ubigeo">Ubigeo:*</label>
        <select name="tipo_ubigeo" id="tipo_ubigeo">';
while ($row = mysqli_fetch_assoc($query_ubi)) {
    $selected = ($row['id'] == $selectedUbiId) ? 'selected' : '';
    $optionsUbi .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['distrito'] . ' - ' . $row['provincia'] . ' - ' . $row['departamento'] . ' - ' . $row['ubigeo'] . '</option>';
}
$optionsUbi .= '</select></div>';


//TIPO DE CLIENTE
$query_tpc = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 14 ORDER BY num_item ASC");

if (!$query_tpc) {
    echo json_encode(['error' => mysqli_error($conection)]);
    mysqli_close($conection);
    exit();
}

$optionsTpc = '<div class="modal_item"><label for="select_tipo">Tipo de cliente:*</label>
        <select class="select_pedido" name="select_tipo" id="select_tipo">';
while ($row = mysqli_fetch_assoc($query_tpc)) {
    $selected = ($row['id'] == $selectedTpcId) ? 'selected' : '';
    $optionsTpc .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['des_item'] . '</option>';
}
$optionsTpc .= '</select></div>';

//VENDEDOR
$query_ven = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 93 ORDER BY num_item ASC");

if (!$query_ven) {
    echo json_encode(['error' => mysqli_error($conection)]);
    mysqli_close($conection);
    exit();
}

$optionsVen = '<div class="modal_item"><label for="select_ven">Vendedor:*</label>
        <select class="select_pedido" name="select_ven" id="select_ven">
            <option value="0">Seleccionar Vendedor</option>';
while ($row = mysqli_fetch_assoc($query_ven)) {
    $selected = ($row['id'] == $selectedVenId) ? 'selected' : '';
    $optionsVen .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['des_item'] . '</option>';
}
$optionsVen .= '</select></div>';

//CONDICION DE PAGO
$query_cpg = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 7 ORDER BY num_item ASC");

if (!$query_cpg) {
    echo json_encode(['error' => mysqli_error($conection)]);
    mysqli_close($conection);
    exit();
}

$optionsCpg = '<div class="modal_item"><label for="select_cpg">Condición de pago:*</label>
        <select class="select_pedido" name="select_cpg" id="select_cpg">';
while ($row = mysqli_fetch_assoc($query_cpg)) {
    $selected = ($row['id'] == $selectedCpgId) ? 'selected' : '';
    $optionsCpg .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['des_item'] . '</option>';
}
$optionsCpg .= '</select></div>';

//MONEDA DEFAULT
$query_mon = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 10 ORDER BY num_item ASC");

if (!$query_mon) {
    echo json_encode(['error' => mysqli_error($conection)]);
    mysqli_close($conection);
    exit();
}

$optionsMon = '<div class="modal_item"><label for="select_mon">Moneda por defecto:*</label>
        <select class="select_pedido" name="select_mon" id="select_mon">';
while ($row = mysqli_fetch_assoc($query_mon)) {
    $selected = ($row['id'] == $selectedMonId) ? 'selected' : '';
    $optionsMon .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['des_item'] . '</option>';
}
$optionsMon .= '</select></div>';


$response = ['optionsTdoc' => $optionsTdoc, 'optionsUbi' => $optionsUbi,'selected'=>$selectedTdocId,'optionsTpc'=>$optionsTpc,'optionsVen'=>$optionsVen,'optionsCpg'=>$optionsCpg,'optionsMon'=>$optionsMon,'email'=>$email??''];

if (isset($clientInfo)) {
    $response['clientInfo'] = $clientInfo;
}

echo json_encode($response);
mysqli_close($conection);
