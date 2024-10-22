<?php
session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
include "../conexion.php";
include "calcTotal.php";
// print_r($_POST);    exit;

date_default_timezone_set('America/Lima');
$val = $_POST['val']??0;
$swtIgv=$_POST['swtIgv']??0;
$iduser = $_SESSION[$_SESSION['db'].'idUser'];
$current_date = date('Y-m-d H:i:s');
$idMped = isset($_POST['idMped']) && $_POST['idMped'] !== '' ? $_POST['idMped'] : 0;

// Function to get IGV from configuration
function getIgv($conection) {
    $query_igv = mysqli_query($conection, "SELECT igv FROM configuracion");
    if ($query_igv && mysqli_num_rows($query_igv) > 0) {
        $info_igv = mysqli_fetch_assoc($query_igv);
        return $info_igv['igv'];
    }
    return 0;
}



// Function to generate response
function generateResponse($query, $igv,$val,$swtIgv,$idMped) {
    $detalleTabla = '';
    $sub_total = 0;
    $sub_afecto=0;
    $total = 0;
    $imp_isc=0;
    $imp_iceberg=0;
    $descuentoTotal=0;
    $descuentoAfecto=0;
    $descuentoGlobal=0;
    $correlativo=0;
    $obs = '';
    while ($data = mysqli_fetch_assoc($query)) {
        // die('bucle while');
        //srr  num  fec  cli  mon  cpg  ven  list  ref  swt
        //ID_DTAB_SRR = ?, NUM_PED = ?,FEC_PED = ?, ID_MCLIENT = ?, ID_DTAB_MON = ?, ID_DTAB_CPAG = ?, ID_MVENDED = ?, ID_DTAB_LIST = ?, NUM_OCOM = ?, SWT_COT
        $dataG = ['fec'=>$data['FEC_PED']??$data['FEC_OCOM']??null,'id'=>$data['id_mpedido']??$data['ID_MORDCOM']??0,'crr'=>$data['NUM_PED']??$data['NUM_OCOM']??0,'cli'=>$data['ID_MCLIENT']??$data['ID_MPROVEE']??null, 'mon'=>$data['ID_DTAB_MON'], 'cpg'=>$data['ID_DTAB_CPAG'], 'ven'=>$data['ID_MVENDED'], 'list'=>$data['ID_DTAB_LIST']??'', 'ref'=>$data['NUM_OCOM'], 'swt'=>$data['SWT_COT']??'', 'ruc'=>$data['ruc_cli']??'', 'swtIgv'=>$data['SWT_IGV']??0, 'SWT_PED'=>$data['SWT_PED']??1];

        if($data['por_tigv']??$data['POR_TIGV'] != -1){
            $igv = $data['por_tigv']??$data['POR_TIGV']??18;
        }
        $obs = $data['OBS_PED']??$data['OBS_OCOM']??'';
        $imp_isc=$data['IMP_ISC']??0;
        $imp_iceberg=$data['IMP_ICEBERG']??0;
        //Pendiente agregar a sub_total o sub_afecto segun su estado swt_Afecto
        $swtAfecto=$data['SWT_AFECTO']??0;
        $pre_disabled = ($data['SWT_BONI']??0)?'disabled':'';
        $correlativo=$data['correlativo']??$data['ID'];
        $descuentoGlobal=$data['desc_glob']??$data['POR_TDCT']??0;
        if($swtAfecto){
            $descuentoTotal = round($descuentoTotal + ($data['cantidad']??$data['CAN_PPRD']) * ($data['precio_venta']??$data['PRE_PPRD']??0)*(($data['desc_item']??$data['DCT_PPRD']??0)/100), 2);
        }else{
            $descuentoAfecto = round($descuentoAfecto + ($data['cantidad']??$data['CAN_PPRD']) * ($data['precio_venta']??$data['PRE_PPRD']??0)*(($data['desc_item']??$data['DCT_PPRD']??0)/100), 2);
        }
        
        $precioTotal = round(($data['cantidad']??$data['CAN_PPRD']) * ($data['precio_venta']??$data['PRE_PPRD']??0)*(1-($data['desc_item']??$data['DCT_PPRD']??0)/100), 2);
        //Logica if para afecto o inafecto
        if($swtAfecto){
            $sub_total = round($sub_total + $precioTotal, 4);
        }else{
            $sub_afecto = round($sub_afecto + $precioTotal, 4);
        }
        $total = round($total + $precioTotal, 4);

        $detalleTabla .= '<tr>
            <form id="updateForm' . $correlativo . '">
                <td>
                    <span class="cdg_prod">' . ($data['cdg_prod']??$data['ID_MPRODUC']??'-') . '</span>
                    <span class="eqv_prod displaynone">' . ($data['cdg_eqv']??$data['ID_MPRODUC']??'-') . '</span>
                </td>
                <td>
                    <input type="text" name="descripcion" value="' . ($data['descripcion']??$data['OBS_PROD']??'-') . '" class="form-descripcion" id="descripcion-' . $correlativo . '">
                </td>
                <td>
                    ' . ($data['marca']??'-') . '
                </td>
                <td class="venta-text1">
                    <input type="text" name="cantidad" value="' . round($data['cantidad']??$data['CAN_PPRD'], 2) . '" class="form-cantidad numericInput" id="cantidad-' . $correlativo . '"></td>
                <td class="venta-text1">
                    <input type="text" name="precio_venta" data-decimales="4" style="width: 100px" value="' . ($data['precio_venta']??$data['PRE_PPRD']??0) . '" class="form-precio numericInput" id="precio-' . $correlativo . '" '.$pre_disabled.'>';
                    
        if($val==0){
            if($data['SWT_BONI']??0){
                $detalleTabla .= 'BONI.('.$data['PRE_BONI'].')';
            }
        }
        $detalleTabla .='    
                    <input type="hidden" class="val_sol" value="'.($data['VAL_SOL']??0).'">
                    <input type="hidden" class="val_dol" value="'.($data['VAL_DOL']??0).'">
                </td>
                <td class="venta-text">
                    <input type="text" max="100" name="desc_venta" value="' . ($data['desc_item']??$data['DCT_PPRD']??0) . '" class="form-desc_item numericInput" id="descuento-' . $correlativo . '">
                </td>
                
                <td class="venta-text form-precio-total">' . number_format($precioTotal, 2) . '</td>
                <td>
                <i class="fa-solid fa-trash-can disabled" onclick="del_product_detalle( '.$correlativo.' ,' .$val .')" title="Borrar detalle" ></i>';
        if($val==0){
            if($data['SWT_BONI']??0){
                $detalleTabla .= "<i class='fa-solid fa-b bonificacion' onclick='bonusChange(this,$correlativo)' title='bonificación'></i>";
            }else{
                $detalleTabla .= "<i class='fa-solid fa-b bonificacion disabled' onclick='bonusChange(this,$correlativo)' title='bonificación'></i>";
            }
        }
        $detalleTabla .= "
                </td>
            </form>
        </tr>";
        //Actualizar valores en detalle_temp
    }
    // calcTotal($sub_total, $igv, $descuentoGlobal, $imp_isc, $imp_iceberg);
    $result = calcTotal($sub_total, $igv, $descuentoGlobal, $imp_isc, $imp_iceberg, $swtIgv, $sub_afecto);

    // Access the calculated values
    $sub_total = $result['sub_total'];
    $impuesto = $result['impuesto'];
    $descuentoGlobalT = $result['descuentoGlobalT'];
    $total = $result['total'];
    $dataG['alertIgv'] = $result['alertIgv'];

    $detalleTotales ='
                <!-- Left side for button and textarea -->
                <div class="left_side">
                    <span>Observaciones:</span>
                    <div id="observations" >
                        <textarea id="text_obs" class="text_obs">'.$obs.'</textarea>
                    </div>
                </div>

                <!-- Right side for totals and other info -->
                <div class="right_side">
    <div class="group_totales">
    <div class="venta-text text_strong">Sub Afecto <span class="simbolo_mon">S/.</span></div>
    <div class="venta-text text_strong">'. number_format($sub_total, 2) .'</div>
    </div>

    <div class="group_totales">
        <div class="venta-text text_strong">Sub Inafecto <span class="simbolo_mon">S/.</span></div>
        <div class="venta-text text_strong">'. number_format($sub_afecto, 2) .'</div>
    </div>

    <div class="group_totales">
        <div class="venta-text">
            <input type="number" min="0" max="100" name="desc_glob" value="'. number_format($descuentoGlobal, 2) .'" class="form-desc_glob" id="form-desc_glob">% DESC GLOBAL <span class="simbolo_mon">S/.</span>
        </div>
        <div class="venta-text" id="final_desc_globt">'. number_format($descuentoGlobalT, 2) .'</div>
    </div>

    <div class="group_totales">
        <div class="venta-text">
            <input type="number" max="100" name="por_tigv" value="'. number_format($igv, 2) .'" class="form-por_tigv" id="por_tigv-'. $correlativo .'" disabled>% IGV <span class="simbolo_mon text_strong">S/.</span>
        </div>
        <div class="venta-text text_strong" id="final_imp_tigv">'. number_format($impuesto, 2) .'</div>
    </div>

    <div class="group_totales">
        <div class="venta-text">ISC:</div>
        <div class="venta-text">
            <input type="number" name="imp_isc" value="'. number_format($imp_isc, 2) .'" class="form-imp_isc" id="imp_isc-'. $imp_isc .'" disabled>
        </div>
    </div>

    <div class="group_totales">
        <div class="venta-text">ICEBERG:</div>
        <div class="venta-text">
            <input type="number" name="imp_iceberg" value="'. number_format($imp_iceberg, 2) .'" class="form-imp_iceberg" id="imp_iceberg-'. $imp_iceberg .'" disabled>
        </div>
    </div>

    <div class="group_totales">
        <div class="venta-text text_strong">TOTAL <span class="simbolo_mon">S/.</span></div>
        <div class="venta-text text_strong" id="imp_total">'. number_format($total, 2) .'</div>
    </div>

</div>
';
include "../conexion.php";
$token = md5($_SESSION[$_SESSION['db'].'idUser']);
if ($val != 3) {
    // Actualizar detalle_temp
    $query_update = "UPDATE detalle_temp SET 
        desc_glob = $descuentoGlobal, 
        desc_globt = $descuentoGlobalT,
        imp_tigv = $impuesto,
        imp_isc = $imp_isc,
        imp_iceberg = $imp_iceberg,
        IMP_STOT = $sub_total,
        VAL_F1 = $sub_afecto,
        imp_ttot = $total
        WHERE token_user = '$token' AND id_mpedido = '$idMped';";
} else {
    // Actualizar detalle_temp_ocom
    $query_update = "UPDATE detalle_temp_ocom SET
        IMP_STOT = $sub_total, 
        POR_TDCT = $descuentoGlobal, 
        IMP_TDCT = $descuentoGlobalT,
        IMP_TIGV = $impuesto,
        IMP_ISC = $imp_isc,
        IMP_ICEBERG = $imp_iceberg,
        IMP_TTOT = $total
        WHERE TOKEN_USER = '$token';";
}

// Ejecutar la consulta y manejar posibles errores
if (!mysqli_query($conection, $query_update)) {
    echo "Error: " . mysqli_error($conection);
}
        

    return ['detalle' => $detalleTabla, 'totales' => $detalleTotales, 'data' => $dataG];
}
function generateResponseIn($query,$val) {
    $detalleTabla = '';
    $obs = '';
    while ($data = mysqli_fetch_assoc($query)) {
        $max = ($data['CAN_MAX']??0)?ROUND($data['CAN_MAX'],2):0;
        $tot = ($data['CAN_PPRD']??0)?ROUND($data['CAN_PPRD'],2):0;
        $dataG = ['are'=>$data['ID_DTAB_CDGAREA'],'ori'=>$data['ID_DTAB_ORIAREA']??0,'tmov'=>$data['ID_DTAB_TMOV'],'fec'=>$data['FEC_GUIA'], 'ref'=>$data['REF1'], 'cct'=>$data['ID_DTAB_CCT']??0,'srr'=>$data['num_item']??''];
        $obs = $data['OBS_GUIA'];
        $id=$data['ID'];
        $detalleTabla .= '<tr class="table_row" data-id="'.$id.'">
                <td><span>' . $data['ID_MPRODUC'] . '</span></td>
                <td colspan="2"><span>' . $data['OBS_ITEM'] . '</span></td>
                <td class="venta-text1"><input type="text" name="cantidad" value="' . round($data['CAN_PROD'], 2) . '" data-form="cantidad" class="form-cantidad numericInput" id="cantidad-' . $id . '">';
        if($max){
            $detalleTabla .= 'de <span class="can_max">'.$max.'</span><input class="can_tot" type="hidden" value="'.$tot.'">';
        }
        if($val==2){
            $detalleTabla .= " de ". round($data['STK_ACT']??0,2);
        }
        $detalleTabla .= '</td><td class="venta-text"><input type="text" name="unitario" value="' . round($data['PRE_PROD']?? 0, 4)  . '" data-form="precio"  data-decimales="4" class="form-precio numericInput"></td>
                <td class="venta-text"><input type="text" name="total" value="' . round($data['PRE_GUIA']?? 0, 2)  . '" data-form="total" class="form-total numericInput"></td>
                <td>
                    <a class="link_delete material-symbols-outlined" title="Borrar detalle" href="#" onclick="event.preventDefault(); del_product_detalle(' . $id . ',' . $val . ');">Delete</a>
                </td>
        </tr>';
    }
    $detalleTotales ='
                <!-- Left side for button and textarea -->
                <div class="left_side">
                    <span>Observaciones:</span>
                    <div id="observations" >
                        <textarea id="text_obs" class="text_obs">'.$obs.'</textarea>
                    </div>
                </div>

                <!-- Right side for totals and other info -->
                <div class="right_side">
    
                </div>
                ';
                
    return ['detalle' => $detalleTabla, 'totales' => $detalleTotales,'data'=> $dataG??0];
}

// Process request
if ($_POST) {
    $action = $_POST['action'];
    $token = md5($_SESSION[$_SESSION['db'].'idUser']);
    $igv = getIgv($conection);
    switch ($action) {
        case 'addProductoDetalle':
            if (!empty($_POST['codproducto'])) {
                $codproducto = $_POST['codproducto'];
                $list = empty($_POST['list'])?0:$_POST['list'];
                $cli = empty($_POST['cli'])?0:$_POST['cli'];
                $mon = $_POST['mon']??0;//Pendiente revisar variables enviadas en add_detalle_temp
                // echo 'igv:'.$igv.'id'.$codproducto.'li'.$list.'cli'.$cli.'mon'.$mon;
                // echo "CALL add_detalle_temp($codproducto, '$token',$val,$iduser,'$current_date',$list,$cli,$mon,$idMped)";
                $query = mysqli_query($conection, "CALL add_detalle_temp($codproducto, '$token',$val,$iduser,'$current_date',$list,$cli,$mon,$idMped)");
                if ($query && mysqli_num_rows($query) > 0) {
                    // $response = ($val==0|| $val==3) ? generateResponse($query, $igv,$val,$swtIgv,$idMped):generateResponseIn($query,$val)   ;
                    $response = ['status'=>'success'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    echo 'error';
                }
            } else {
                echo 'error';
            }
            break;

        case 'searchForDetails':
            
            if(!$val){
                $srr = $_POST['srr']??0;
                $crr = $_POST['crr']??0;
                if($srr && $crr){
                    // echo "SELECT d.*, c.ruc_cli FROM detalle_temp d LEFT JOIN m_client c ON d.id_mclient = c.id WHERE d.token_user = '$token' AND d.id_dtab_srr = $srr AND d.num_ped = $crr";
                    // exit;
                    $query = mysqli_query($conection, "SELECT d.*, c.ruc_cli, p.cdg_prod, p.cdg_eqv, marca.des_item as marca 
                    FROM detalle_temp d 
                    LEFT JOIN m_client c ON d.id_mclient = c.id 
                    LEFT JOIN m_produc p ON d.codproducto = p.id 
                    LEFT JOIN d_tablas marca ON marca.id = p.id_dtab_marca 
                    WHERE d.token_user = '$token' AND d.id_dtab_srr = $srr AND d.num_ped = $crr");
                }else{
                    $idMpedQ = 'AND d.id_mpedido = '.($_POST['idMped']??0 != 0 ? $_POST['idMped'] : '0');
                    // echo $idMped;
                    $query = mysqli_query($conection, "SELECT d.*, c.ruc_cli, p.cdg_prod, p.cdg_eqv, marca.des_item as marca 
                    FROM detalle_temp d 
                    LEFT JOIN m_client c ON d.id_mclient = c.id 
                    LEFT JOIN m_produc p ON d.codproducto = p.id 
                    LEFT JOIN d_tablas marca ON marca.id = p.id_dtab_marca 
                    WHERE d.token_user = '$token' $idMpedQ");
                }
                
            }else if($val==1 || $val == 4){
                $query = mysqli_query($conection, "SELECT d.*,t.num_item,o.CAN_PPRD FROM detalle_temp_in d LEFT JOIN d_tablas t ON d.ID_DTAB_CDGAREA = t.ID LEFT JOIN d_ordcom o ON o.id = d.id_dordcom WHERE d.token_user = '$token'");
            }else if($val==2){
                $query = mysqli_query($conection, "SELECT d.*,t.num_item FROM detalle_temp_out d LEFT JOIN d_tablas t ON d.ID_DTAB_CDGAREA = t.ID WHERE d.token_user = '$token'");
            }else if($val==3){
                $query = mysqli_query($conection, 
                "SELECT d.*, p.ruc_prv as ruc_cli, prod.cdg_prod, prod.cdg_eqv, marca.des_item as marca
                        FROM detalle_temp_ocom d 
                        LEFT JOIN m_provee p ON d.id_mprovee = p.id 
                        LEFT JOIN m_produc prod ON d.id_mproduc = prod.id 
                        LEFT JOIN d_tablas marca ON marca.id = prod.id_dtab_marca 
                        WHERE d.token_user = '$token'");
            }
            
            if ($query && mysqli_num_rows($query) > 0) {
                $response = ($val==0 || $val==3) ? generateResponse($query, $igv,$val,$swtIgv,$idMped):generateResponseIn($query,$val);
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['detalle' => '', 'totales' => ''], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'updateTemp':
            try {
                // Obtener valores de POST
                $id = $_POST['id'] ?? 0;
                $des = $_POST['des'] ?? '';
                $can = $_POST['can'] ?? 0;
                $pre = $_POST['pre'] ?? 0;
                $dsc = $_POST['dsc'] ?? 0;
                $desc_glob = $_POST['desc_glob'];
                $por_tigv = $_POST['por_tigv'];
                $imp_isc = 0;
                $imp_iceberg = 0;
                // Descomentar para usar los impuestos
                // $imp_isc = $_POST['imp_isc'];
                // $imp_iceberg = $_POST['imp_iceberg'];
                $imp_total = $_POST['imp_total'];
                $obs = $_POST['obs'];
        
                $cpg = $_POST['cpg'];
                $ven = $_POST['ven'];
        
                if ($desc_glob < 0 || $desc_glob > 100) {
                    throw new Exception('El descuento global no puede ser negativo o mayor a 100.');
                }
                if ($dsc < 0 || $dsc > 100) {
                    throw new Exception('El descuento no puede ser negativo o mayor a 100.');
                }
        
                $stmt = $conection->prepare("CALL update_detalle_temp(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    throw new mysqli_sql_exception('Error en la preparación de la consulta: ' . htmlspecialchars($conection->error));
                }
        
                $stmt->bind_param(//i - int, s - string, d - double
                    'isddsddddddsiiiii',
                    $id, $des, $can, $pre, $token, $dsc,
                    $desc_glob, $por_tigv, $imp_isc, $imp_iceberg,
                    $imp_total, $obs, $val, $cpg, $ven, $swtIgv, $idMped
                );
        
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        // $response = generateResponse($result, $igv, $val, $swtIgv, $idMped);
                        $response = ['status'=>'success'];
                        echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    } else {
                        throw new Exception('No se encontraron resultados tras la ejecución del procedimiento.');
                    }
                } else {
                    throw new Exception('Error al ejecutar la consulta.');
                }
        
                // Cerrar la consulta preparada
                $stmt->close();
        
            } catch (Exception $e) {
                // Capturar cualquier error y devolverlo al cliente
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        
            break;

        case 'delProductoDetalle':
            if (!empty($_POST['id_detalle'])) {
                $id_detalle = $_POST['id_detalle'];
                $query = mysqli_query($conection, "CALL del_detalle_temp($id_detalle, '$token',$val,$idMped)");
                if ($query && mysqli_num_rows($query) > 0) {
                    // $response = ($val==1||$val==2) ? generateResponseIn($query,$val):generateResponse($query, $igv,$val,$swtIgv,$idMped);
                    $response = ['status'=>'success'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    echo 'error';
                }
            } else {
                echo 'error';
            }
            break;

        default:
            echo 'error';
            break;
    }

    mysqli_close($conection);
    exit;
}
