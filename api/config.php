<?php

session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
include "../conexion.php";
// print_r($_POST);    exit;

$user_id = $_SESSION[$_SESSION['db'].'idUser'];
date_default_timezone_set('America/Lima');
$current_date = date('Y-m-d H:i:s');
$current_time = date('H:i:s');
$token = md5($_SESSION[$_SESSION['db'].'idUser']);



function checkModuleStatus($conection, $month, $year,$id) {
    $query = "SELECT swt_opc FROM m_cierre WHERE mes_cie = '$month' AND ano_cie = '$year' AND id_mopcion = $id";
    $result = mysqli_query($conection, $query);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['swt_opc'];
        } else {
            return 'no_record';
        }
    }
    return false;
}
function sanitize_data($data) {
    return [
        'fec_gui' => isset($data['fec_gui']) ? date('Y-m-d', strtotime($data['fec_gui'])) : '',
        'despachoT' => isset($data['despachoT']) ? intval($data['despachoT']) : 0,
        'tpoTransporte' => isset($data['tpoTransporte']) ? intval($data['tpoTransporte']) : 0,
        'transportista' => $data['transportista']?? '',
        'chofer' => $data['chofer']?? '',
        'placa' => $data['placa']?? '',
        'venMueBon' => isset($data['venMueBon']) ? intval($data['venMueBon']) : 0,
        'movimiento' => $data['movimiento']?? '',
        'motivo' => $data['motivo']?? '',
        'almacen' => $data['almacen']?? '',
        'text_obs' => $data['text_obs']?? '',
        'peso' => isset($data['peso']) ? floatval($data['peso']) : 0.0,
        'biene' => $data['biene']?? ''
    ];
}
if (!empty($_POST)) {


    // Actualizar o crear serie
    if ($_POST['action'] == 'fillModules') {
        if (empty($_POST['ids'])) {
            echo json_encode(['error' => 'No IDs provided']);
            exit;
        } else {
            $ids = implode(',', array_map('intval', $_POST['ids'])); // Convert the array of IDs into a comma-separated string
            $modules = []; // Initialize an array to store the data
    
            $query_module = mysqli_query($conection, "SELECT p.id, p.num_item, p.des_item, p.icon_item,
                                        MIN(dd.frm_item) AS frm_item,m.id AS idM,
                                        COUNT(dd.id) AS contador
                                        FROM d_opcion p
                                        INNER JOIN m_opcion m ON m.id = p.id_mopcion
                                        INNER JOIN d_dopcion dd ON dd.id_dopci = p.id
                                        INNER JOIN d_usuari u ON u.id_dopcion = dd.id
                                        WHERE p.id_mopcion IN ($ids) AND p.swt_item = 1 AND dd.swt_opc = 1 AND u.id_musuari = $user_id
                                        GROUP BY p.id, p.num_item, p.des_item, p.icon_item
                                        ORDER BY m.id
                                        ");
    
            while ($row = mysqli_fetch_assoc($query_module)) {
                $modules[] = $row; // Add each row as an associative array to the main array
            }
            
            // Convert the array to JSON format and return it
            echo json_encode($modules);
    
            mysqli_close($conection);
        }
    }


    // Consultar Ddopcion
    if ($_POST['action'] == 'viewDdopcion') {
        if (empty($_POST['idmodule'])||empty($_POST['idopcion'])) {
            echo 'error';
        } else {
            $idmodule = $_POST['idmodule'];
            $idopcion = $_POST['idopcion'];
            $query_procesar = []; // Inicializar un array para almacenar los datos

            $query_dopc = mysqli_query($conection, "SELECT DISTINCT dd.id,dd.num_cor, dd.des_item, dd.frm_item,dd.icon_item FROM d_dopcion dd INNER JOIN m_opcion m ON m.id = dd.id_mopcion INNER JOIN d_opcion p  ON dd.id_mopcion = m.id AND dd.id_dopcion = p.num_item INNER JOIN d_usuari u ON u.id_dopcion = dd.id WHERE m.id = $idmodule AND dd.id_dopcion = $idopcion AND p.swt_item = 1 AND u.id_musuari = $user_id;");
            
            while ($row = mysqli_fetch_assoc($query_dopc)) {
                $query_procesar[] = $row; // Agregar cada fila como un elemento del array
            }

            // Convertir el array en formato JSON y devolverlo
            echo json_encode($query_procesar);

            mysqli_close($conection);
        }
    }
    //Agregar favorito
    if ($_POST['action'] == 'addNewFav') {
        if (empty($_POST['idDopc'])) { 
            echo json_encode(array('error' => 'ID D_dopcion is missing.'));
        } else {
            $idopc = $_POST['idDopc'];
            $query_update = mysqli_query($conection, "UPDATE d_usuari SET fav_item = 1 WHERE id_musuari = $user_id AND id_dopcion = $idopc");
            echo 'success';

            mysqli_close($conection);
        }
    }

    //Rellenar favoritos
    if ($_POST['action'] == 'fillFavorites') {
        $modules = array(); 

        $query_module = mysqli_query($conection, "SELECT p.id, p.des_item, p.frm_item, p.icon_item FROM d_dopcion p INNER JOIN d_usuari u ON u.id_dopcion = p.id WHERE p.id_mopcion IN (SELECT m.id FROM m_opcion m WHERE swt_opc = 1) AND p.id_dopcion IN (SELECT num_item FROM d_opcion WHERE id_mopcion = p.id_mopcion AND num_item = p.id_dopcion AND swt_item = 1) AND p.swt_opc = 1 AND u.id_musuari = $user_id AND u.fav_item = 1 ORDER BY p.des_item;");

        while ($row = mysqli_fetch_assoc($query_module)) {
            $modules[] = $row;
        }

        echo json_encode($modules);

        mysqli_close($conection);
    }
    
    //Eliminar favoritos
    if ($_POST['action'] == 'deleteFav') {
        if (empty($_POST['idFav'])) { 
            echo json_encode(array('error' => 'ID D_dopcion is missing.'));
        } else {
            $idopc = $_POST['idFav'];
            // Preparar la consulta UPDATE
        $query_update = mysqli_prepare($conection, "UPDATE d_usuari SET fav_item = 0 WHERE id_musuari = ? AND id_dopcion = ?");
        mysqli_stmt_bind_param($query_update, "ii", $user_id, $idopc);

        // Ejecutar la consulta UPDATE
        mysqli_stmt_execute($query_update);

        // Verificar si se ejecutó correctamente
        if (mysqli_stmt_affected_rows($query_update) > 0) {
            // Consulta SELECT para obtener des_item
            $query_select = mysqli_prepare($conection, "SELECT des_item FROM d_dopcion WHERE id = ?");
            mysqli_stmt_bind_param($query_select, "i", $idopc);
            mysqli_stmt_execute($query_select);
            mysqli_stmt_bind_result($query_select, $des_item);
            mysqli_stmt_fetch($query_select);

            // Cerrar conexiones y liberar recursos
            mysqli_stmt_close($query_select);
            mysqli_stmt_close($query_update);

            // Devolver resultado como JSON codificado
            echo json_encode(array('status' => 'success', 'des_item' => $des_item));
        } else {
            echo json_encode(array('error' => 'Failed to update.'));
            mysqli_stmt_close($query_update);
        }
        mysqli_close($conection);
        }
    }

    //Eliminar deshabilitar usuario
    if ($_POST['action'] == 'lrdriideleteDisableUser') {
        if (empty($_POST['id'])) { 
            echo json_encode(['error' => 'ID is missing.']);
        } else {
            $id = $_POST['id'];
            $query_delete = mysqli_query($conection, "UPDATE m_usuari SET swt_usr = 0 WHERE id = $id ");
            echo 'success';

            mysqli_close($conection);
        }
    }

    //Activar o desactivar modo solo lectura
    if ($_POST['action'] == 'lectura') {
        if (empty($_POST['id'])||empty($_POST['user'])) { 
            echo json_encode(['error' => 'Values missing.']);
        } else {
            $id = $_POST['id'];
            $user = $_POST['user'];
            $swt = $_POST['swt'];
            $query_update = mysqli_query($conection, "UPDATE d_usuari SET lec_item = $swt WHERE id_musuari = $user AND id_dopcion = $id");
            echo json_encode(['success' => 'update.']);

            mysqli_close($conection);
        }
    }

   // Activar o desactivar modo solo lectura
    if ($_POST['action'] == 'consultar_abr_linea') {
        if (empty($_POST['line'])) {
            echo json_encode(['error' => 'Value is missing.']);
        } else {
            $line = $_POST['line'];

            // Preparar la consulta preparada
            $query = "SELECT d.abr_item, (SELECT COUNT(id) + 1 FROM m_produc WHERE id_dtab_linp = d.id) AS num FROM d_tablas d WHERE d.id = ?";
            $stmt = mysqli_prepare($conection, $query);

            if ($stmt === false) {
                // Manejo de error si la preparación de la consulta falla
                echo json_encode(['error' => 'Prepared statement failed.']);
            } else {
                // Vincular parámetros y ejecutar la consulta
                mysqli_stmt_bind_param($stmt, "i", $line);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $abr_item, $num);
                
                // Obtener resultados
                if (mysqli_stmt_fetch($stmt)) {
                    // Cerrar el statement
                    mysqli_stmt_close($stmt);
                    
                    // Devolver resultado como JSON codificado
                    echo json_encode(['status' => 'success', 'abr_item' => $abr_item, 'num' => $num]);
                } else {
                    // Manejar caso de consulta sin resultados
                    echo json_encode(['error' => 'No results found.']);
                }
            }
        }
    }

    if ($_POST['action'] == 'buscarPedidoAjax') {
        $pedido = $_POST['pedido'] ?? '';
        $cliente = $_POST['cliente'] ?? '';
        $des = $_POST['des']?? '';
        $ocompra = $_POST['ocompra']?? '';
        $swt = isset($_POST['swt']) && $_POST['swt'] != 0 ? $_POST['swt'] : '';
        $fec_desde = $_POST['fec_desde']?? '';
        $fec_hasta = $_POST['fec_hasta']?? '';

        $where = ' WHERE 1=1 ';
        $params = [];
        $types = '';

        if (!empty($pedido)) {
            $where .= " AND (p.id LIKE ? OR p.num_ero LIKE ?)";
            $params[] = "%$pedido%";
            $params[] = "%$pedido%";
            $types .= 'ss';
        }
        if (!empty($cliente)) {
            $where .= " AND (c.des_cli LIKE ? AND c.ruc_cli LIKE ?) ";
            $params[] = "%$des%";
            $params[] = "%$cliente%";
            $types .= 'ss';
        }
        if (!empty($ocompra)) {
            $where .= " AND p.num_ocom LIKE ? ";
            $params[] = "%$ocompra%";
            $types .= 's';
        }
        if (!empty($swt)) {
            $where .= " AND p.id_dtab_srr = ? ";
            $params[] = $swt;
            $types .= 'i';
        }else{
            $where .= " AND p.id_dtab_srr IN (SELECT id_dtab_srr FROM m_usrseries mu WHERE id_musuari = ?) ";
            $params[] = $user_id;
            $types .= 'i';
        }
        if (!empty($fec_desde) && !empty($fec_hasta)) {
            if ($fec_desde > $fec_hasta) {
                echo json_encode(['error' => 'La fecha desde no puede ser mayor que la fecha hasta.']);
                exit();
            } elseif ($fec_desde == $fec_hasta) {
                $where .= " AND p.fec_ped LIKE ? ";
                $params[] = "$fec_desde%";
                $types .= 's';
            } else {
                $f_de = "$fec_desde 00:00:00";
                $f_a = "$fec_hasta 23:59:59";
                $where .= " AND p.fec_ped BETWEEN ? AND ? ";
                $params[] = $f_de;
                $params[] = $f_a;
                $types .= 'ss';
            }
        }

        $query = "SELECT  p.id, p.num_ocom, c.ruc_cli, c.des_cli, p.imp_ttot, p.id_dtab_mon, 
                    COALESCE(dp.count_id, 0) AS count_id,
                    COALESCE(dg.count_gui, 0) AS count_gui,
                    COALESCE(dd.count_doc, 0) AS count_doc,
                p.fec_ped, p.id_musuari,p.num_ero
                FROM m_pedido p 
                LEFT JOIN m_client c ON p.id_mclient = c.id 
                LEFT JOIN (
                            SELECT id_mpedido,SUM(can_pprd) AS count_id
                            FROM d_pedido
                            GROUP BY id_mpedido
                        ) dp ON dp.id_mpedido = p.id
                        LEFT JOIN (
                            SELECT sdp.id_mpedido, SUM(g.can_dgui) AS count_gui
                            FROM d_guia g
                            INNER JOIN d_pedido sdp ON sdp.id =  g.id_dpedido
                            LEFT JOIN m_guia mg ON g.id_mguia = mg.id
                            WHERE mg.swt_est != 0
                            GROUP BY sdp.id_mpedido
                        ) dg ON dg.id_mpedido = p.id
                        LEFT JOIN (
                            SELECT sdp.id_mpedido, SUM(doc.can_dfac) AS count_doc
                            FROM d_doccli doc
                            INNER JOIN d_pedido sdp ON sdp.id =  doc.id_dpedido
                            LEFT JOIN m_doccli md ON doc.id_mdoccli = md.id
                            WHERE md.swt_est != 0
                            GROUP BY sdp.id_mpedido
                        ) dd ON dd.id_mpedido = p.id
                $where AND p.swt_ped = 1
                ORDER BY p.id DESC";

        $stmt = $conection->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        $numrows = $result->num_rows; 

        while ($row = $result->fetch_assoc()) {
            
            $row['numrows'] = $numrows;
            $data[] = $row;
        }
        $response = [
            'numrows' => $numrows,
            'data' => $data
        ];
        echo json_encode($response);

        $stmt->close();
        $conection->close();
    }


    
    // Busqueda pedido ajax despacho facturacion tdoc gui
    if ($_POST['action'] == 'loadTdocGui') {
        $sel = $_POST['sel'];

        $query = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 21");
    
        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }

        $options = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $selected = ($row['id'] == $sel) ?'selected':'';
            $options[] = '<option value="' . $row['id'] . '" '.$selected.'>' . $row['des_item'] . '</option>';
        }
    
        echo json_encode(['options' => implode('', $options)]);
    
        mysqli_close($conection);
        
    }

    // Busqueda pedido ajax despacho facturacion tdoc doc
    if ($_POST['action'] == 'loadTdocDoc') {
        $sel = $_POST['sel'];
        $query = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 12");
    
        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $options = []; 
        while ($row = mysqli_fetch_assoc($query)) {
            $selected = $row['id']==$sel?'selected':'';
            $options[] = '<option value="' . $row['id'] . '" '.$selected.'>' . $row['des_item'] . '</option>';
        }
    
        echo json_encode(['options' => implode('', $options)]);
    
        mysqli_close($conection);
        
    }

    
    if ($_POST['action'] == 'updateSerie' || $_POST['action'] == 'updateSerieGuia') {
        if ($_POST['num']<0 || empty($_POST['doc']) || empty($_POST['srr'])) {
            echo json_encode(['status' => 'error', 'message' => 'Solo se aceptan series positivas.']);
        } else {
            $num = $_POST['num'];
            $doc = $_POST['doc'];
            $srr = $_POST['srr'];
            
            if ($_POST['action'] == 'updateSerie') {
                $check_query = $conection->prepare("SELECT * FROM t_doccli WHERE id_dtab_tdc = ? AND id_dtab_srr= ?");
                $check_query->bind_param("ii", $doc, $srr);
            } else {
                $check_query = $conection->prepare("SELECT * FROM t_guia WHERE id_dtab_tpg = ? AND id_dtab_srr= ?");
                $check_query->bind_param("ii", $doc, $srr);
            }
            
            $check_query->execute();
            $result = $check_query->get_result();
            $row_count = $result->num_rows;
            $check_query->close();
            
            if ($row_count > 0) {
                if ($_POST['action'] == 'updateSerie') {
                    $update_query = $conection->prepare("UPDATE t_doccli SET num_ero = ?, id_musuari = ?, fec_usu = ? WHERE id_dtab_tdc = ? AND id_dtab_srr= ?");
                } else {
                    $update_query = $conection->prepare("UPDATE t_guia SET num_ero = ?, id_musuari = ?, fec_usu = ? WHERE id_dtab_tpg = ? AND id_dtab_srr= ?");
                }
                $update_query->bind_param("iissi", $num, $user_id, $current_date, $doc, $srr);
                if ($update_query->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Serie actualizada']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la serie']);
                }
                $update_query->close();
            } else {
                if ($_POST['action'] == 'updateSerie') {
                    $insert_query = $conection->prepare("INSERT INTO t_doccli (id_dtab_tdc, id_dtab_srr, num_ero, id_musuari, fec_usu) VALUES (?, ?, ?, ?, ?)");
                } else {
                    $insert_query = $conection->prepare("INSERT INTO t_guia (id_dtab_tpg, id_dtab_srr, num_ero, id_musuari, fec_usu) VALUES (?, ?, ?, ?, ?)");
                }
                $insert_query->bind_param("iiiss", $doc, $srr, $num, $user_id, $current_date);
                if ($insert_query->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Serie creada']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al crear la serie']);
                }
                $insert_query->close();
            }
            
            $conection->close();
        }
    }
    
    if ($_POST['action'] == 'getClient') {
        // SQL query to fetch the data
        $query = mysqli_query($conection, "SELECT DISTINCT c.ruc_cli, CONCAT(c.ruc_cli, '-', c.des_cli) AS txt FROM m_client c INNER JOIN m_pedido m ON m.id_mclient = c.id WHERE m.swt_cot = 0 AND c.swt_cli = 1");

        // Check for query errors
        if (!$query) {
            echo json_encode(array('error' => 'Error en la consulta: ' . mysqli_error($conection)));
            exit();
        }

        $options =[];

        // Fetch results and build options array
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = [
                'id' => $row['ruc_cli'],
                'txt' => $row['txt']
            ];
        }

        // Close the database connection
        mysqli_close($conection);

        // Return the options in JSON format
        echo json_encode(['results' => $options]);
        exit(); // End script execution after sending JSON response
    }

    if ($_POST['action'] == 'addDetalleTempGuia') {
        $pedidos = json_decode($_POST['pedidos'], true);
        $tpg = $_POST['tpg'];
        $srr = $_POST['srr'];
        $pedidos_json = json_encode($pedidos);
        
        mysqli_autocommit($conection, false);
        
        $query = mysqli_prepare($conection, "CALL add_detalle_temp_guia(?,?,?,?,?,?)");
        mysqli_stmt_bind_param($query, "ssiisi", $pedidos_json, $token,$tpg,$srr,$current_date,$user_id);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            mysqli_rollback($conection); // Deshacer la transacción en caso de error
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        mysqli_commit($conection);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'addDetalleTempFact') {
        $guias = json_decode($_POST['guias'], true);
        $tdoc = $_POST['tdoc'];
        $srr = $_POST['srr'];
        $guias_json = json_encode($guias);
        
        
        mysqli_autocommit($conection, false);
        
        $query = mysqli_prepare($conection, "CALL add_detalle_temp_fact(?, ?,?,?,?)");
        mysqli_stmt_bind_param($query, "ssiis", $guias_json, $token,$tdoc,$srr,$current_date);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            mysqli_rollback($conection); // Deshacer la transacción en caso de error
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        mysqli_commit($conection);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    
    if ($_POST['action'] == 'cambiosTablaGuia') {
        $id = ($_POST['id'] != '') ? $_POST['id'] : 0;
        $codigo = $_POST['codigo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $valorDespachar = $_POST['valorDespachar'] ?? 0;
        $query = "CALL actualizar_o_seleccionar(?, ?, ?, ?, ?)";
        $stmt = $conection->prepare($query);
    
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Error en la preparación: ' . mysqli_error($conection)]);
            mysqli_close($conection);
            exit;
        }
    
        $stmt->bind_param("issds", $id, $codigo, $descripcion, $valorDespachar, $token);
    
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $message = $row['message'];
    
            if ($message === 'success') {
                echo json_encode(['success' => true, 'message' => 'Operación completada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la ejecución: ' . $stmt->error]);
        }
    
        $stmt->close();
        mysqli_close($conection);
    }

    
    if ($_POST['action'] == 'procesarGuia') {
        $id = $_POST['tpOpc'] ?? 2;
        $fec = $_POST['fec_gui'];
        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));
        $moduleStatus = checkModuleStatus($conection, $month, $year, $id);
        
        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }
    
        $query = "CALL procesarGuia(?, ?, ?)";
        $stmt = $conection->prepare($query);
    
        $stmt->bind_param("ssi", $token, $current_date, $user_id); // Asegúrate de que los tipos sean correctos
    
        $result = $stmt->execute();
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
            $stmt->close();
            mysqli_close($conection);
            exit;
        }
    
        $result = $stmt->get_result();
    
        if ($result) {
            $row = $result->fetch_assoc();
            $message = $row['message'];
    
            if ($message === 'success') {
                echo json_encode(['success' => true, 'message' => 'Guía procesada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la obtención de resultados: ' . mysqli_stmt_error($stmt)]);
        }
    
        $stmt->close();
        mysqli_close($conection);
    }
    
    
    if ($_POST['action'] == 'updateDetalleTempGui') {
        $datos = sanitize_data($_POST['datos']);
        $query = "UPDATE detalle_temp_guia 
                  SET FEC_GUI = ?, DESP_TOT = ?, TRANSP = ?, ID_DTAB_TRA = ?,
                      ID_DTAB_CHOF = ?, PLACA = ?, VEN_MUE_BON = ?, ID_DTAB_MOV = ?, ID_DTAB_MOTIV = ?, ID_DTAB_ARE = ?, 
                      PESO = ?, OBS_GUI = ?, CAN_BIENE = ? 
                  WHERE TOKEN_USER = ?";
        
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "siiiisiiiidsis",
            $datos['fec_gui'], $datos['despachoT'], $datos['tpoTransporte'], $datos['transportista'],   
            $datos['chofer'], $datos['placa'], $datos['venMueBon'], $datos['movimiento'], 
            $datos['motivo'], $datos['almacen'], $datos['peso'], $datos['text_obs'], $datos['biene'], $token
        );
        $result = mysqli_stmt_execute($stmt);
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Detalle actualizado exitosamente']);
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conection);
    }
    if ($_POST['action'] == 'despachoTotalGuia') {
        $are = $_POST['are'];
    
        // Contar cuántas filas deberían actualizarse
        $count_stmt = mysqli_prepare($conection, "SELECT COUNT(*) FROM detalle_temp_guia dt 
                        WHERE dt.TOKEN_USER = ? AND dt.CAN_PROD - dt.TOT_DES > 0;");
        
        mysqli_stmt_bind_param($count_stmt, "s", $token);
        mysqli_stmt_execute($count_stmt);
        mysqli_stmt_bind_result($count_stmt, $total_rows_to_update);
        mysqli_stmt_fetch($count_stmt);
        mysqli_stmt_close($count_stmt);
        
        // Ejecutar el UPDATE
        $stmt = mysqli_prepare($conection, "UPDATE detalle_temp_guia dt
                SET dt.A_DES = dt.CAN_PROD - dt.TOT_DES 
                WHERE dt.TOKEN_USER = ? AND
                (SELECT s.STK_ACT - dt.CAN_PROD - dt.TOT_DES 
                    FROM stock s WHERE s.ID_MPRODUC = dt.ID_MPRODUC AND s.ID_AREA = ?) >= 0;");
        
        mysqli_stmt_bind_param($stmt, "si", $token, $are);
        $result = mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        // echo $affected_rows , $total_rows_to_update;
        if ($result) {
            if ($affected_rows == 0) {
                // Ninguna fila se actualizó, falta de stock en todas
                echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
            } elseif ($affected_rows < $total_rows_to_update) {
                // Éxito parcial, algunas filas no se pudieron actualizar
                echo json_encode(['success' => true, 'message' => 'Algunos productos no se actualizaron por falta de stock', 'partial' => true]);
            } else {
                // Éxito total, todas las filas se actualizaron correctamente
                echo json_encode(['success' => true, 'message' => 'Todos los productos fueron actualizados correctamente']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
        }
    
        mysqli_stmt_close($stmt);
        mysqli_close($conection);
    }
    
    
    
    // Busqueda pedido ajax despacho facturacion tdoc doc
    if ($_POST['action'] == 'viewGuidesPending') {
        $ped = $_POST['ped'];
        $query = mysqli_query($conection, "SELECT g.id, g.fec_guia,c.des_cli FROM m_guia g 
        LEFT JOIN m_client c ON g.id_mclient = c.id 
        WHERE g.id_mpedido = '$ped' and g.swt_est = 1 ");

        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $guias = ''; 
        while ($row = mysqli_fetch_assoc($query)) {
            $guias .= "<tr class='table_guide' data-rowid='{$row['id']}' onclick='selectedGuide(this)'><td>{$row['id']}</td><td>{$row['fec_guia']}</td><td>{$row['des_cli']}</td></tr>";
        }
        echo json_encode(['body' => $guias]);
        mysqli_close($conection);
    }

    // Busqueda pedido ajax despacho facturacion tdoc
    if ($_POST['action'] == 'updateContentFact') {
        $id = $_POST['id'];
        $val = $_POST['val'];

        $set = 'UPDATE detalle_temp_fact SET ';
        $params = [];
        $types = '';

        if ($id == 'fec_fact') {
            $set .= "  FEC_DOCU = ? ";
            $params[] = $val;
            $types .= 's';
        }
        if ($id == 'cond_pago') {
            $set .= " ID_DTAB_CPG = ? ";
            $params[] = $val;
            $types .= 'i';
        }
        if ($id == 'fec_venc') {
            $set .= "  FEC_VCTO = ? ";
            $params[] = $val;
            $types .= 's';
        }
        if ($id == 'doc_ref') {
            $set .= "  NUM_OCOM = ? ";
            $params[] = $val;
            $types .= 's';
        }
        if ($id == 'text_obs') {
            $set .= "  OBS_FCLI = ? ";
            $params[] = $val;
            $types .= 's';
        }
        if ($id == 'tributos') {
            $set .= "  VAL_FOTRI = ? ";
            $params[] = $val;
            $types .= 'd';
        }
        if ($id == 'desc_glob') {
            $set .= "  POR_TDCT = ? ";
            $params[] = $val;
            $types .= 'd';
        }
        if ($id == 'financieros') {
            $set .= "  VAL_F1 = ? ";
            $params[] = $val;
            $types .= 'd';
        }
        if ($id == 'flete') {
            $set .= "  VAL_FFLE = ? ";
            $params[] = $val;
            $types .= 'd';
        }
        if ($id == 'tper') {
            $set .= "  POR_TPER = ? ";
            $params[] = $val;
            $types .= 'd';
        }
        $set .= " WHERE TOKEN_USER = ?"; 
        $params[] = $token;
        $types .= 's';

        $stmt = $conection->prepare($set);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $response = [
            'success' => ($stmt->affected_rows > 0) ? true : false
        ];

        echo json_encode($response);

        $stmt->close();
        $conection->close();
    }

    
    if ($_POST['action'] == 'setTempZero') {
        $query_update = mysqli_query($conection,"UPDATE detalle_temp_guia 
                  SET A_DES = 0
                  WHERE TOKEN_USER = '$token'");
        if (!$query_update) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Detalle actualizado exitosamente']);
        }
        
        mysqli_close($conection);
    }

    
    if ($_POST['action'] == 'updateDescripcionFact') {
        $des = $_POST['des'];
        $id = $_POST['id'];
        $query = "UPDATE detalle_temp_fact
                  SET OBS_DOC = ? 
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "si",$des,$id);
        $result = mysqli_stmt_execute($stmt);
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Detalle actualizado exitosamente']);
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'procesarFactura') {
        $codigo = $_POST['codigo'];
        $fec = $_POST['fec'];
        $id = $_POST['tpOpc'] ?? 2;
        //Detraccion
        $detra = $_POST['detra'] ?? 0;
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $cambio = $_POST['cambio'] ?? 0;
        $pago = $_POST['pago'] ?? 0;

        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));
        $moduleStatus = checkModuleStatus($conection, $month, $year, $id);
        $fecha_hora = "$fec $current_time";

        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }
    
        $query = "CALL procesarFactura(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conection->prepare($query);
    
    
        $stmt->bind_param("ssiisisdi", $token, $current_date, $user_id, $codigo, $fecha_hora, $detra,$fecha,$cambio,$pago); 
    
        $result = $stmt->execute();
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
            $stmt->close();
            mysqli_close($conection);
            exit;
        }
    
        $result = $stmt->get_result();
    
        if ($result) {
            $row = $result->fetch_assoc();
            $message = $row['message'];
    
            if ($message === 'success') {
                $id = $row['ID'];
                $swt = $row['swt'];
                //En caso halla validado la facturacion electronica
                if(!$swt){
                    $msg = ', facturación electrónica desactivada';
                    $id = 0;
                }else if($swt == 1){
                    $msg = ', serie no asignada a factura electronica';
                    $id = 0;
                }else{
                    $msg = ', enviada a SUNAT!';
                }
                echo json_encode(['success' => true, 'message' => "Factura procesada exitosamente$msg", 'id' => $id]);

            } else {
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la obtención de resultados: ' . mysqli_stmt_error($stmt)]);
        }
    
        $stmt->close();
        mysqli_close($conection);
    }

    //Mostrar Facturas 
    if ($_POST['action'] == 'buscarFacturaAjax') {
        $factura = $_POST['factura'] ?? '';
        $cliente = $_POST['cliente'] ?? '';
        $des = $_POST['des'] ?? '';
        $swt = isset($_POST['swt']) && $_POST['swt'] != 0 ? $_POST['swt'] : '';
        $fec_desde = $_POST['fec_desde'] ?? '';
        $fec_hasta = $_POST['fec_hasta'] ?? '';
        
        $tdoc = $_POST['tdoc'] ?? '';
        $estado = $_POST['estado'] ?? '';

        $where = ' WHERE 1=1 ';
        $params = [];
        $types = '';

        if (!empty($factura)) {
            $where .= " AND (d.id LIKE ? OR d.obs_fcli = ?)";
            $params[] = "%$factura%";
            $params[] = "%$factura%";
            $types .= 'ss';
        }
        if (!empty($cliente)) {
            $where .= " AND (c.des_cli LIKE ? OR d.des_cli LIKE ? OR c.ruc_cli LIKE ?) ";
            $params[] = "%$des%";
            $params[] = "%$des%";
            $params[] = "%$cliente%";
            $types .= 'sss';
        }
        if (!empty($swt)) {
            $where .= " AND d.id_dtab_srr = ? ";
            $params[] = $swt;
            $types .= 'i';
        }
        if (!empty($fec_desde) && !empty($fec_hasta)) {
            if ($fec_desde > $fec_hasta) {
                echo json_encode(['error' => 'La fecha desde no puede ser mayor que la fecha hasta.']);
                exit();
            } elseif ($fec_desde == $fec_hasta) {
                $where .= " AND d.fec_docu LIKE ? ";
                $params[] = "$fec_desde%";
                $types .= 's';
            } else {
                $f_de = "$fec_desde 00:00:00";
                $f_a = "$fec_hasta 23:59:59";
                $where .= " AND d.fec_docu BETWEEN ? AND ? ";
                $params[] = $f_de;
                $params[] = $f_a;
                $types .= 'ss';
            }
        }
        //Añadir logica y valores para el tipo de documento, o en caso contrario usar los id para las comprobaciones
        // if (!empty($tdoc)) {
        //     $where .= " AND d.id_dtab_srr = ? ";
        //     $params[] = $tdoc;
        //     $types .= 'i';
        // }
        
        // if (!empty($estado)) {
        //     $where .= " AND d.id_dtab_srr = ? ";
        //     $params[] = $estado;
        //     $types .= 'i';
        // }

        $query = "SELECT  d.id, d.fec_docu, tdoc.des_item as tdoc,srr.des_item as srr, d.num_docu, d.des_cli, 
                    CASE 
                    WHEN e.est_baja IS NULL OR e.est_baja = '' THEN e.rpta_sunat 
                    ELSE e.est_baja 
                END AS rpta_sunat,
                e.est_rptasunat,d.ruta_xml
                FROM t_efactdocu e 
                LEFT JOIN m_doccli d ON e.id_m_doccli = d.id 
                LEFT JOIN d_tablas srr ON d.id_dtab_srr = srr.id 
                LEFT JOIN d_tablas tdoc ON d.id_dtab_tdoc = tdoc.id 
                LEFT JOIN m_client c ON d.id_mclient = c.id 
                $where ORDER BY e.id DESC";

        $stmt = $conection->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        $numrows = $result->num_rows; 

        while ($row = $result->fetch_assoc()) {
            
            $row['numrows'] = $numrows;
            $data[] = $row;
        }
        $response = [
            'numrows' => $numrows,
            'data' => $data
        ];
        echo json_encode($response);

        $stmt->close();
        $conection->close();
    }

    //Enviando archivos desde efact
    if ($_POST['action'] == 'resendFact') {
        $id = $_POST['id'];
        header("location: ../sistema/greenter/factura.php?id=$id");
        echo json_encode('Se envió correctamente.');    
    }

    if ($_POST['action'] == 'bajaFact') {
        $ids = $_POST['ids']; 
        $ids = json_decode($ids); 
        header("Location: ../sistema/greenter/baja.php?ids=" . urlencode(implode(',', $ids)));
        
        echo json_encode([
            'success' => true,
            'msg' => 'Se envió la solicitud correctamente.'
        ]);
    }

    //Actualizar tabla detalle_temp_in
    if ($_POST['action'] == 'updateTableIn') {
        $are = $_POST['are'];
        $mov = $_POST['mov'];
        $fec = $_POST['fec'];
        $obs = $_POST['obs']??'';
        $ref = $_POST['ref']??'';
        $val = $_POST['val'];
        //Preguntar que sera mejor guardar, null no se puede
        $ori = $_POST['ori']??0;
        $cen = $_POST['cen']??0;
    
        if($val == 1) {
            $id = $_POST['id'];
            $can = $_POST['can'];
            $uni = $_POST['uni'];
            $tot = $_POST['tot'];
            $query = mysqli_prepare($conection, "UPDATE detalle_temp_in SET CAN_PROD = ?, PRE_PROD = ?, PRE_GUIA = ? WHERE ID = ?");
            mysqli_stmt_bind_param($query, "dddi", $can, $uni, $tot, $id);
            mysqli_stmt_execute($query);if (!$query) {
                echo json_encode(['success' => false, 'error' => mysqli_error($conection)]);
                exit();
            }
        }else if ($val == 2) {
            $id = $_POST["id"];
            $can = $_POST["can"];
            $query = mysqli_prepare($conection, "UPDATE detalle_temp_out SET CAN_PROD = ? WHERE ID = ?");
            mysqli_stmt_bind_param($query, "di", $can, $id);
            mysqli_stmt_execute($query);if (!$query) {
                echo json_encode(['success' => false, 'error' => mysqli_error($conection)]);
                exit();
            }
        }
        
    

        if($ori){
            $queryTot = mysqli_prepare($conection, "UPDATE detalle_temp_out SET ID_DTAB_CDGAREA = ?, ID_DTAB_TMOV = ?, FEC_GUIA = ?, OBS_GUIA = ?, REF1 = ?, ID_DTAB_ORIAREA = ?, ID_DTAB_CCT = ? WHERE TOKEN_USER = ?");
            mysqli_stmt_bind_param($queryTot, "iisssiis", $are, $mov, $fec, $obs, $ref,$ori,$cen, $token);
        }else{
            $queryTot = mysqli_prepare($conection, "UPDATE detalle_temp_in SET ID_DTAB_CDGAREA = ?, ID_DTAB_TMOV = ?, FEC_GUIA = ?, OBS_GUIA = ?, REF1 = ? WHERE TOKEN_USER = ?");
            mysqli_stmt_bind_param($queryTot, "iissss", $are, $mov, $fec, $obs, $ref, $token);
        }
        mysqli_stmt_execute($queryTot);
    
        if (!$queryTot) {
            echo json_encode(['success' => false, 'error' => mysqli_error($conection)]);
            exit();
        }
        mysqli_stmt_close($queryTot);
        mysqli_commit($conection);
    
        echo json_encode(['success' => true]);
    
        mysqli_close($conection);
    }
    
    if ($_POST['action'] == 'procesarNotaIngreso') {
        $id = $_POST['tpOpc'] ?? 1;
        $fec = $_POST['fec'];
        $val = $_POST['val'];
        $swt = ($val==1)?0:1;
        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));
        $moduleStatus = checkModuleStatus($conection, $month, $year, $id);
        
        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }
        $query = ($val==1)?"CALL procesarIngreso(?, ?, ?, ?, ?)":"CALL procesarSalida(?, ?, ?, ?, ?)";
        
        $stmt = $conection->prepare($query);
    
        $stmt->bind_param("ssiis", $token, $current_date, $user_id,$swt,$fec); // Asegúrate de que los tipos sean correctos
    
        $result = $stmt->execute();
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
            $stmt->close();
            mysqli_close($conection);
            exit;
        }
    
        $result = $stmt->get_result();
    
        if ($result) {
            $row = $result->fetch_assoc();
            $message = $row['message'];
    
            if ($message === 'success') {
                echo json_encode(['success' => true, 'message' => 'Ingreso procesado correctamente', 'idnota' => $row['idnota']]);
            } else {
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la obtención de resultados: ' . mysqli_stmt_error($stmt)]);
        }
    
        $stmt->close();
        mysqli_close($conection);
    }
    
    // Mostrar guías para transferencia
    if ($_POST['action'] == 'transferenciasGuias') {
        $year = $_POST['year'] ?? '';
        $month = $_POST['month'] ?? '';
        $ori = $_POST['ori'] ?? '';
        $des = $_POST['des'] ?? '';
        //Añadiendo notas de salida, falta guia de remision

        $where = ' WHERE m.id_dtab_tpg = 3827 AND m.id_dtab_cdgarea IS NOT NULL AND m.id_dtab_cdgarea != 0';
        $params = [];
        $types = '';

        if (!empty($ori)) {
            $where .= " AND (m.ID_DTAB_ORIAREA = ?)";
            $params[] = $ori;
            $types .= 'i';
        }
        if (!empty($des)) {
            $where .= " AND (m.ID_DTAB_CDGAREA = ?) ";
            $params[] = $des;
            $types .= 'i';
        }
        if (!empty($year)) {
            $where .= " AND YEAR(m.fec_guia) = ?";
            $params[] = $year;
            $types .= 'i';
        }
        if (!empty($month)) {
            $where .= " AND MONTH(m.fec_guia) = ?";
            $params[] = $month;
            $types .= 'i';
        }

        $query = "SELECT m.id, tpg.abr_item, m.num_grem, m.fec_guia, ori.des_item as des_ori, cdg.des_item, m.id_mguia_transf, tra.fec_guia as fec_ing
                FROM m_guia m 
                LEFT JOIN d_tablas tpg ON m.id_dtab_tpg = tpg.id 
                LEFT JOIN d_tablas ori ON m.id_dtab_oriarea = ori.id 
                LEFT JOIN d_tablas cdg ON m.id_dtab_cdgarea = cdg.id 
                LEFT JOIN m_guia tra ON m.id_mguia_transf = tra.id 
                $where 
                ORDER BY m.id DESC";

        $stmt = $conection->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        $numrows = $result->num_rows; 

        while ($row = $result->fetch_assoc()) {
            $row['numrows'] = $numrows;
            $data[] = $row;
        }
        $response = [
            'numrows' => $numrows,
            'data' => $data
        ];
        echo json_encode($response);

        $stmt->close();
        $conection->close();
    }
    // Traer información detallada de guía para transferir
    if ($_POST['action'] == 'detalleTransferencia') {
        $id = $_POST['id'];

        $query = "SELECT tpg.des_item, m.num_grem, m.fec_guia, p.cdg_prod, d.obs_guia, d.can_dgui
                FROM d_guia d 
                INNER JOIN m_guia m ON d.id_mguia = m.id
                LEFT JOIN d_tablas tpg ON m.id_dtab_tpg = tpg.id 
                LEFT JOIN m_produc p ON d.id_mproduc = p.id
                WHERE d.id_mguia = ?";

        $stmt = $conection->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode(['data' => $data]);

        $stmt->close();
        $conection->close();
    }

    
    if ($_POST['action'] == 'confirmarTransferencia') {
        $fec = $_POST['fec'];
        $id = $_POST['id'];
        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));
        $moduleStatus = checkModuleStatus($conection, $month, $year, 1);
        
        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }
        $query = "CALL procesarIngreso(?, ?, ?, ?, ?)";
        
        $stmt = $conection->prepare($query);
    
        $stmt->bind_param("ssiis", $token, $current_date, $user_id, $id,$fec); // Asegúrate de que los tipos sean correctos
    
        $result = $stmt->execute();
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
            $stmt->close();
            mysqli_close($conection);
            exit;
        }
    
        $result = $stmt->get_result();
    
        if ($result) {
            $row = $result->fetch_assoc();
            $message = $row['message'];
    
            if ($message === 'success') {
                echo json_encode(['success' => true, 'message' => 'Ingreso procesado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la obtención de resultados: ' . mysqli_stmt_error($stmt)]);
        }
    
        $stmt->close();
        mysqli_close($conection);
    }

    //Guardar global info pedido srr  num  fec  cli  mon  cpg  ven  list  ref  swt
    if ($_POST['action'] == 'cambioPedidoGlobal') {
        $srr = $_POST['srr'];
        $num = $_POST['num'];
        $time = date('H:i:s');
        $fec = $_POST['fec'].' '.$time;
        $cli = $_POST['cli'];
        $mon = $_POST['mon'];
        $cpg = $_POST['cpg'];
        $ven = $_POST['ven'];
        $list = $_POST['list'];
        $ref = $_POST['ref'];
        $swt_cot = $_POST['swt_cot'];

        $idMped = isset($_POST['idMped']) && $_POST['idMped'] !== '' ? $_POST['idMped'] : 0;

        $query_update = "UPDATE detalle_temp SET ID_DTAB_SRR = ?,NUM_PED = ?,FEC_PED = ?, ID_MCLIENT = ?, ID_DTAB_MON = ?, ID_DTAB_CPAG = ?, ID_MVENDED = ?, ID_DTAB_LIST = ?, NUM_OCOM = ?, SWT_COT = ? WHERE token_user = ? AND id_mpedido = ?";
        $stmt = $conection->prepare($query_update);
        $stmt->bind_param('iisiiiiisisi', $srr, $num, $fec,  $cli,  $mon,  $cpg,  $ven,  $list,  $ref,  $swt_cot, $token, $idMped);
        $stmt->execute();
        mysqli_close($conection);
    }
    //Guardar global info orden de compra srr  num  fec  cli  mon  cpg  ven  list  ref  swt
    if ($_POST['action'] == 'cambioOcomGlobal') {
        $srr = $_POST['srr'];
        $num = $_POST['num'];
        $fec = $_POST['fec'];
        $prv = $_POST['prv']??'';
        $mon = $_POST['mon'];
        $cpg = $_POST['cpg'];
        $ven = $_POST['ven'];
        $list = $_POST['list'];
        $query_update = "UPDATE detalle_temp_ocom SET ID_DTAB_SRR = ?,NUM_OCOM = ?,FEC_OCOM = ?, ID_MPROVEE = ?, ID_DTAB_MON = ?, ID_DTAB_CPAG = ?, ID_MVENDED = ?, ID_DTAB_LIST = ? WHERE token_user = ?";
        $stmt = $conection->prepare($query_update);
        $stmt->bind_param('iisiiiiis', $srr, $num, $fec,  $prv,  $mon,  $cpg,  $ven,  $list, $token);
        $stmt->execute();
        mysqli_close($conection);
    }

    
    //-------------Buscar Proveedor
    if ($_POST['action'] == 'searchSupplier') {
        if (!empty($_POST['supplier'])) {
            $busqueda = $_POST['supplier'];
            $sql = "SELECT * FROM m_provee WHERE (id LIKE '%$busqueda%' OR ruc_prv LIKE '%$busqueda%' OR des_prv LIKE '%$busqueda%' OR dir_prv LIKE '%$busqueda%' OR tel_prv LIKE '%$busqueda%')  AND swt_prv = 1";
        } else {
            $sql = "SELECT * FROM m_provee WHERE swt_prv = 1";
        }
        $registros = $_POST['registros'];
        $pagina = $_POST['pagina'];
        $offset = ($pagina - 1) * $registros;
        $result = $conection->query($sql);
        $num_rows = $result->num_rows;
        $sql .= " LIMIT $offset, $registros";
        $result = $conection->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            $data['error'] = 'No se encontraron proveedores.';
        }
        $total_paginas = ceil($num_rows / $registros);
        $data['total_paginas'] = $total_paginas;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_POST['action'] == 'addNewSupplier') {
        if (empty($_POST['razon']) || empty($_POST['tel']) || empty($_POST['dir']) || empty($_POST['ruc']) || empty($_POST['ubigeo'])) {
            echo json_encode(['success' => false, 'message' => 'Completar todos los campos.']);
        } else {
            $razon = $_POST['razon'];
            $ruc = $_POST['ruc'];
            $telefono = $_POST['tel'];
            $direccion = $_POST['dir'];
            $doc = $_POST['doc'];
            $ubigeo = ($_POST['ubigeo'] == '-') ? 0 : $_POST['ubigeo'];
            $swt = $_POST['swt'] ?? null;
    
            try {
                if (!$swt) {
                    // Inserción de nuevo proveedor
                    $query_insert = mysqli_query($conection, "INSERT INTO m_provee(ruc_prv, des_prv, tel_prv, dir_prv, id_dtab_tdoc, id_mubigeo, id_musuari, fec_usu)
                    VALUES('$ruc', '$razon', '$telefono', '$direccion', $doc, $ubigeo, $user_id, '$current_date')");
    
                    if ($query_insert) {
                        echo json_encode(['success' => true, 'message' => mysqli_insert_id($conection)]);
                    } else {
                        throw new mysqli_sql_exception(mysqli_error($conection));
                    }
                } else {
                    // Actualización de proveedor existente
                    $query_update = mysqli_query($conection, "UPDATE m_provee SET ruc_prv = '$ruc', des_prv = '$razon', tel_prv = '$telefono', dir_prv = '$direccion', 
                    id_dtab_tdoc = $doc, id_mubigeo = $ubigeo, id_musuari = $user_id, fec_usu = '$current_date' WHERE ID = $swt");
    
                    if ($query_update) {
                        echo json_encode(['success' => true, 'message' => $swt]);
                    } else {
                        throw new mysqli_sql_exception(mysqli_error($conection));
                    }
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) { // Código de error para entrada duplicada
                    echo json_encode(['success' => false, 'message' => 'Entrada duplicada para RUC.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al registrar o actualizar el proveedor.']);
                }
            }
    
            mysqli_close($conection);
        }
    }
    
    
    
    if ($_POST['action'] == 'consultar_pedido_ocom') {
        $serie = $_POST['serieId'] ?? '';
        $num = $_POST['numSerie'] ?? '';
        $swt = $_POST['swt'] ?? 0;
        // $idMped = $_POST['idMped'] ?? 0;
    
        if (!$num || !$serie) {
            echo json_encode(['error' => 'No ID or NUM provided']);
            exit();
        }
    
        // Definir la consulta base según el valor de $swt
        $query = !$swt ? 
            "SELECT m.id_mclient as idcliente, c.ruc_cli as ruc, c.des_cli as nombre, 
            c.tel_cli as telefono, c.dir_cli as direccion, m.fec_ped, m.id_dtab_mon, d.des_item, u.id as user, 
            u.des_usr, m.id_mvended as id, m.swt_cot, m.swt_igv, m.num_ocom, m.fec_usu , m.swt_ped 
            FROM m_pedido m 
            LEFT JOIN m_client c ON c.id = m.id_mclient
            LEFT JOIN m_usuari u ON m.id_musuari = u.id 
            LEFT JOIN d_tablas d ON m.id_dtab_mon = d.id 
            WHERE m.id_dtab_srr = ? AND m.num_ped = ?" :
            
            "SELECT m.id_mprovee as idprovee, p.ruc_prv as ruc, p.des_prv as nombre, 
            m.fec_ocom, m.id_dtab_mon, d.des_item, u.id as user, u.des_usr, m.id_mvended as id,
            m.num_ocom, m.fec_usu 
            FROM m_ordcom m 
            LEFT JOIN m_provee p ON p.id = m.id_mprovee 
            LEFT JOIN m_usuari u ON m.id_musuari = u.id 
            LEFT JOIN d_tablas d ON m.id_dtab_mon = d.id 
            WHERE m.ID_DTAB_SRR  = ? AND m.num_ocom = ?";
    
        // Preparar la declaración
        if ($stmt = mysqli_prepare($conection, $query)) {
            mysqli_stmt_bind_param($stmt, 'ii', $serie, $num);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['error' => 'Error en la consulta preparada']);
            exit();
        }
    
        // Procedimiento almacenado según el valor de $swt
        $proc_name = ($swt == 0) ? 'consultar_pedido' : 'consultar_ocom';
    
        if ($stmt = mysqli_prepare($conection, "CALL $proc_name(?, ?, ?)")) {
            mysqli_stmt_bind_param($stmt, 'iis', $serie, $num, $token);
            mysqli_stmt_execute($stmt);
            $result_call = mysqli_stmt_get_result($stmt);
            $resultado = mysqli_fetch_all($result_call, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['error' => 'Error en la llamada al procedimiento almacenado']);
            exit();
        }
        echo json_encode(['clientes' => $clientes, 'resultado_procedimiento' => $resultado]);
        mysqli_close($conection);
    }
    
    if ($_POST['action'] == 'consulta_num_almacen') {
        $are = $_POST['are'];
        $tpg = $_POST['tpg'];
        if (!isset($tpg)) {
            echo json_encode(['error' => 'No tpgId provided']);
            exit();
        }

        $query = "SELECT COALESCE(MAX(num_ero), 0) + 1 as num_guia,(SELECT num_item FROM d_tablas WHERE ID=$are) as num_item FROM t_guia WHERE id_dtab_tpg = $tpg AND ID_DTAB_SRR = 0;";

        $result = mysqli_query($conection, $query);

        if (!$result) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }

        $row = mysqli_fetch_assoc($result);
        echo json_encode(['num_guia' => $row['num_guia'],'num_item' => $row['num_item']]);

        mysqli_close($conection);
    }

    //Buscar proveedor//preparar
    if($_POST['action']== 'searchSupplierRuc'){
        $search = $_POST['search'];
        $query = mysqli_query($conection,"SELECT ID,RUC_PRV,DES_PRV FROM m_provee WHERE ruc_prv LIKE '$search' and swt_prv = 1");

        $result = mysqli_num_rows($query);
        $data = ($result>0) ? mysqli_fetch_assoc($query) : 0;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
        mysqli_close($conection);
    }

    //Consultar ruc proveedor
    if($_POST['action'] == 'searchSupplierApi'){
        $search= $_POST['search'];
        $url = "https://api.apis.net.pe/v1/ruc?numero=" . urlencode($search);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

        
    // Anular Documento temporal
    if ($_POST['action'] == 'anularDocumento') {
        $swt = $_POST['swt'] ?? 0;

        $tdoc = $_POST['tdoc'] ?? 0;
        $srr = $_POST['srr'] ?? 0;
        $num = $_POST['num'] ?? 0;
        switch ($swt) {
            case 0:
                $table = 'detalle_temp'; 
                $token = "$token' AND id_mpedido = '$idMped";
                break;
            case 1:
                $table = 'detalle_temp_in'; break;
            case 2:
                $table = 'detalle_temp_out'; break;
            case 3:
                $table = 'detalle_temp_ocom'; break;
            case 4:
                $table = 'detalle_temp_doc'; 
                $token = "$token' AND id_dtab_tdoc = '$tdoc' AND id_dtab_srr = '$srr' AND NUM_DOCU = '$num";
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Valor de swt no válido']);
                $conection->close(); exit;
        }
        $query = mysqli_query($conection, "DELETE FROM $table WHERE token_user = '$token'");
        if ($query) {
            $affectedRows = mysqli_affected_rows($conection);
            if ($affectedRows > 0) {
                echo json_encode(['status' => 'ok', 'message' => 'Documento anulado exitosamente']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se eliminaron registros']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => "Error executing query: $conection->error"]);
        }
        $conection->close();
        exit;
    }


    // Procesar -Venta- || Pedido
    if ($_POST['action'] == 'procesarOrden' || $_POST['action'] == 'actualizarOrden') {
        $fec = $_POST['fec'];
        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));

        // Check module status
        $moduleStatus = checkModuleStatus($conection, $month, $year, 3);
        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }

        $query = mysqli_query($conection, "SELECT * FROM detalle_temp_ocom WHERE token_user = '$token'");
        $result = mysqli_num_rows($query);

        if ($result > 0) {
            if ($_POST['action'] == 'procesarOrden') {
                $query_procesar = mysqli_query($conection, "CALL procesar_orden($user_id, '$token','$fec','$current_date')");
            } else {
                $id_orden = $_POST['id_orden'] ?? 0;
                $query_procesar = mysqli_query($conection, "CALL actualizar_orden($user_id, '$token', '$fec','$current_date', $id_orden)");
            }

            if ($query_procesar) {
                $data = mysqli_fetch_assoc($query_procesar);
                if ($data && isset($data['message'])) {
                    echo json_encode(['success' => true, 'message' => $data['message'], 'idocom' => $data['idocom']]);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Operación realizada con éxito']);
                }
            } else {
                $error_message = mysqli_error($conection);
                echo json_encode(['success' => false, 'message' => 'Error al ejecutar el procedimiento almacenado', 'error' => $error_message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron detalles temporales']);
        }
        mysqli_close($conection);
        exit;
    }
    if ($_POST['action'] == 'optionsOcom') {
        $prv = $_POST['prv']??'';
        $query = mysqli_query($conection, 
        "SELECT 
                    o.id, 
                    o.num_ero, 
                    p.ruc_prv, 
                    p.des_prv,
                    COALESCE(SUM(aggr_dg.total_dgui), 0) AS total_dgui, 
                    SUM(d.CAN_PPRD) AS total_pprd
                FROM 
                    d_ordcom d
                    LEFT JOIN m_ordcom o ON o.id = d.id_mordcom
                    LEFT JOIN m_provee p ON o.id_mprovee = p.id
                    LEFT JOIN (
                        SELECT 
                            dg.id_dordcom, 
                            SUM(dg.CAN_DGUI) AS total_dgui
                        FROM 
                            d_guia dg
                        LEFT JOIN m_guia mg ON mg.id = dg.id_mguia
                        WHERE 
                            mg.SWT_EST = 1
                        GROUP BY 
                            dg.id_dordcom
                    ) aggr_dg ON aggr_dg.id_dordcom = d.id
                WHERE 
                    o.swt_est = 1 -- Considera solo órdenes activas si es necesario
                    AND o.id_mprovee LIKE '%$prv%'
                GROUP BY 
                    o.id, o.num_ero, p.ruc_prv, p.des_prv
                HAVING 
                    COALESCE(SUM(aggr_dg.total_dgui), 0) < SUM(d.CAN_PPRD)
                ORDER BY 
                    o.id DESC;");

        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $options []= '<option value="0">Seleccionar Orden de Compra</option>';
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = '<option value="' . $row['id'] . '">'.$row['num_ero'].' | '.$row['ruc_prv'].' | '.$row['des_prv'].'</option>';
        }

        echo json_encode(['options' => implode('', $options)]);

        mysqli_close($conection);
        
    }

    if ($_POST['action'] == 'optionsCreditNote') {
        $query = mysqli_query($conection, 
        "SELECT 
                    m.id, 
                    m.num_ero, 
                    c.ruc_cli, 
                    c.des_cli, 
                    COALESCE(SUM(aggr_dg.total_dgui), 0) AS total_dgui, 
                    SUM(dd.CAN_DFAC) AS total_dfac
                FROM  
                    d_doccli dd
                    LEFT JOIN m_doccli m ON m.id = dd.id_mdoccli 
                    LEFT JOIN m_client c ON m.id_mclient = c.id
                    LEFT JOIN (
                        SELECT dg.id_ddoccli, SUM(dg.CAN_DGUI) AS total_dgui
                        FROM d_guia dg
                        LEFT JOIN m_guia mg ON mg.id = dg.id_mguia
                        WHERE mg.SWT_EST = 1
                        GROUP BY dg.id_ddoccli
                    ) aggr_dg ON aggr_dg.id_ddoccli = dd.id
                WHERE 
                    m.id_dtab_tdoc = 3692 
                    AND m.SWT_EST = 1
                    AND dd.ID_MPRODUC != 1
                GROUP BY 
                    m.id, m.num_ero, c.ruc_cli, c.des_cli
                HAVING 
                    COALESCE(SUM(aggr_dg.total_dgui), 0) < SUM(dd.CAN_DFAC)
                ORDER BY 
                    m.id DESC"); 

        if (!$query) { 
            echo json_encode(['error' => mysqli_error($conection)]); 
            exit(); 
        } 
        $options []= '<option value="0">Seleccionar Orden de Compra</option>';
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = '<option value="' . $row['id'] . '">'.$row['num_ero'].' | '.$row['ruc_cli'].' | '.$row['des_cli'].'</option>';
        }
        echo json_encode(['options' => implode('', $options)]);

        mysqli_close($conection);
        
    }

    if ($_POST['action'] == 'insertOrderCredit') {
        $swt = $_POST['swt'];
        $id = $_POST['id'];
        
        mysqli_autocommit($conection, false);
        
        $query = mysqli_prepare($conection, "CALL insertOrderCredit(?,?,?)");
        mysqli_stmt_bind_param($query, "iis", $id, $swt,$token);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            mysqli_rollback($conection);
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        mysqli_commit($conection);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'searchTipoCambio') {
        $busqueda = !empty($_POST['busqueda']) ? $_POST['busqueda'] : '';
        $registros = intval($_POST['registros']??1);
        $pagina = intval($_POST['pagina']??1);
        $offset = ($pagina - 1) * $registros;
    
        $whereClause = "1 = 1";
        if ($busqueda) {
            $whereClause .= " AND (FEC_CMB LIKE '%$busqueda%')";
        }
    
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM t_cambio WHERE $whereClause ORDER BY FEC_CMB DESC LIMIT $offset, $registros ";
        $result = $conection->query($sql);
        $num_rows = $conection->query("SELECT FOUND_ROWS() AS total")->fetch_assoc()['total'];
        
        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Transformar null a cadena vacía antes de agregar al array
                foreach ($row as $key => $value) {
                    $row[$key] = $value === null ? '' : $value;
                }
                $data['data'][] = $row;
            }
        } else {
            $data['error'] = 'No se encontraron usuarios.';
        }
    
        $data['total_paginas'] = ceil($num_rows / $registros);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_POST['action'] == 'addEditExchange') {
        // if (empty($_POST['fec']) || empty($_POST['cmb']) || empty($_POST['cmbc'])) {
        //     $alert = ['success'=>false,'message'=>'Completar todos los campos.'];
        // } else {
            $fec = $_POST['fec'];
            $cmb = $_POST['cmb'];
            $cmbc = $_POST['cmbc'];
            $cmb_s = $_POST['cmb_s']??0;
            $cmbc_s = $_POST['cmbc_s']??0;
    
            $query = "CALL addEditExchange(?, ?, ?, ?, ?, ? ,?)";
            if ($stmt = $conection->prepare($query)) {
                // Vincular los parámetros
                $stmt->bind_param("sddddis", $fec, $cmb, $cmbc, $cmb_s, $cmbc_s,$user_id,$current_date);
    
                $alert = $stmt->execute()?['success'=>true,'message'=>'Operación exitosa.']:['success'=>false,'message'=>$stmt->error];
                $stmt->close();
            } else {
                $alert = ['success'=>true,'message'=>$conection->error] ;
            }
            $conection->close();
        // }
        echo json_encode($alert, JSON_UNESCAPED_UNICODE);
    }



    if ($_POST['action'] == 'searchListaPrecios') {
        $busqueda = !empty($_POST['busqueda']) ? $_POST['busqueda'] : '';
        $registros = intval($_POST['registros']??1);
        $pagina = intval($_POST['pagina']??1);
        $offset = ($pagina - 1) * $registros;
    
        $whereClause = "1 = 1";
        if ($busqueda) {
            $whereClause .= " AND (l.ID_DTAB_CDGLIST  LIKE '%$busqueda%' OR list.DES_ITEM LIKE '%$busqueda%' OR p.CDG_PROD LIKE '%$busqueda%' OR p.CDG_EQV LIKE '%$busqueda%' OR marc.DES_ITEM LIKE '%$busqueda%' OR p.DES_PROD LIKE '%$busqueda%' OR c.RUC_CLI LIKE '%$busqueda%' OR c.DES_CLI LIKE '%$busqueda%')";
        }
    
        $sql = "SELECT SQL_CALC_FOUND_ROWS l.ID, l.ID_DTAB_CDGLIST, list.DES_ITEM AS LISTA, p.CDG_PROD, p.CDG_EQV, marc.DES_ITEM AS MARCA ,p.DES_PROD, c.RUC_CLI, l.PRE_SOL, l.PRE_DOL, l.ID_MPRODUC, l.ID_MCLIENT FROM m_lista l 
        LEFT JOIN d_tablas list ON list.id = l.ID_DTAB_CDGLIST 
        LEFT JOIN m_produc p ON p.id = l.ID_MPRODUC
        LEFT JOIN d_tablas marc ON marc.id = p.ID_DTAB_MARCA
        LEFT JOIN m_client c ON c.id = l.ID_MCLIENT  
        WHERE $whereClause ORDER BY l.id DESC LIMIT $offset, $registros ";
        $result = $conection->query($sql);
        $num_rows = $conection->query("SELECT FOUND_ROWS() AS total")->fetch_assoc()['total'];
        
        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Transformar null a cadena vacía antes de agregar al array
                foreach ($row as $key => $value) {
                    $row[$key] = $value === null ? '' : $value;
                }
                $data['data'][] = $row;
            }
        } else {
            $data['error'] = 'No se encontraron usuarios.';
        }
    
        $data['total_paginas'] = ceil($num_rows / $registros);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    
    if ($_POST['action'] == 'addEditList') {
        $list = $_POST['list'];
        $pro = $_POST['pro'];
        $cli = $_POST['cli'];
        $mn = $_POST['mn'];
        $me = $_POST['me'];
        mysqli_autocommit($conection, false);
        
        $query = mysqli_prepare($conection, "CALL add_edit_list(?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($query, "iiiddsi", $list, $pro, $cli, $mn, $me, $current_date, $user_id);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            mysqli_rollback($conection);
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        mysqli_commit($conection);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'deleteProductList') {
        if (empty($_POST['id']??'')) { 
            echo json_encode(['error' => 'ID is missing.']);
        } else {
            $id = $_POST['id'];
            $query_delete = mysqli_query($conection, "DELETE FROM m_lista WHERE id = $id ");
            if(!$query_delete){
                echo json_encode(["success"=> false,"error"=> mysqli_error($conection)]);
            }
            echo json_encode(["success"=> true]);

            mysqli_close($conection);
        }
    }

    if ($_POST['action'] == 'addNewClient') {
        $requiredFields = ['razon_social', 'dir_cliente', 'ruc_cliente', 'tipo', 'cpg', 'mon', 'ubigeo'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'Completar campos obligatorios *.']);
                exit;
            }
        }
        if (empty($_POST['email'])) {
            echo json_encode(['success' => false, 'message' => 'Formato incorrecto de correo.']);
            exit;
        }
        $razon_social = $_POST['razon_social'];
        $ruc = $_POST['ruc_cliente'];
        $telefono = $_POST['tel_cliente'];
        $direccion = $_POST['dir_cliente'];
        $tipo_documento = $_POST['tipo_documento'];
        $ubigeo = $_POST['ubigeo'];
        $usuario_id = $_SESSION[$_SESSION['db'].'idUser'];
        $tipo = $_POST['tipo'];
        $ven = $_POST['ven'];
        $cpg = $_POST['cpg'];
        $mon = $_POST['mon'];
        $email = $_POST['email'];
        $swt_update = $_POST['swt_update'] ?? '';
        $est = $_POST['est'] ?? 1;
        
        $stmt = $conection->prepare(empty($swt_update) ? 
            "INSERT INTO m_client (ruc_cli, des_cli, tel_cli, dir_cli, id_dtab_tdoc, id_mubigeo, id_musuari, ID_DTAB_TCLI, ID_MVENDED, ID_DTAB_CPAG, ID_DTAB_MON, EMA_CLI, SWT_CLI) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" : 
            "UPDATE m_client SET ruc_cli = ?, des_cli = ?, tel_cli = ?, dir_cli = ?, id_dtab_tdoc = ?, id_mubigeo = ?, id_musuari = ?, ID_DTAB_TCLI = ?, ID_MVENDED = ?, ID_DTAB_CPAG = ?, ID_DTAB_MON = ?, EMA_CLI = ?, SWT_CLI = ?
            WHERE ID = ?"
        );
    
        if (empty($swt_update)) {
            $stmt->bind_param('ssssiiiiiiisi', $ruc, $razon_social, $telefono, $direccion, $tipo_documento, $ubigeo, $usuario_id, $tipo, $ven, $cpg, $mon, $email, $est);
        } else {
            $stmt->bind_param('ssssiiiiiiisii', $ruc, $razon_social, $telefono, $direccion, $tipo_documento, $ubigeo, $usuario_id, $tipo, $ven, $cpg, $mon, $email, $est, $swt_update);
        }
        try {
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new mysqli_sql_exception($conection->error);
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Código de error para entrada duplicada
                echo json_encode(['success' => false, 'message' => 'Entrada duplicada para RUC.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al ' . (empty($swt_update) ? 'crear' : 'actualizar') . ' cliente.']);
            }
        }
        $stmt->close();
        $conection->close();
    }
    
    if ($_POST['action'] == 'TdocByModule') {
        $val = $_POST['val'];
        $id_mtablas = $val == 0 ? 21 : 12;
    
        $stmt = mysqli_prepare($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = ?");
        if ($stmt === false) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_mtablas);
        mysqli_stmt_execute($stmt);
    
        $result = mysqli_stmt_get_result($stmt);
        if ($result === false) {
            echo json_encode(['error' => mysqli_error($conection)]);
            mysqli_stmt_close($stmt);
            exit();
        }
        $options = [];
        // $options[] = '<option value="0">Seleccionar Documento</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = '<option value="' . $row['id']. '">' . $row['des_item']. '</option>';
        }
    
        // Envía la respuesta en formato JSON
        echo json_encode(['options' => implode('', $options)]);
    
        // Cierra la declaración y la conexión
        mysqli_stmt_close($stmt);
        mysqli_close($conection);
    }
    
    // Anular Documento
    if ($_POST['action'] == 'anularDocumentoVal') {
        $val = $_POST['val'];
        $tdoc = $_POST['tdoc'];
        $srr = $_POST['srr'];
        $crr = $_POST['crr'];

        $swtEst = "SWT_EST = 0";
        $table = '';
        $columTdoc = '';
        $columNum = '';

        // Lógica del switch anidado con validaciones
        switch ($tdoc) {
            case 3997:  // Anulación de Orden de Compra
                $table = 'm_ordcom';
                $columTdoc = '';
                $columNum = 'num_ocom';
                break;
                
            case 3982:  // Anulación de Pedido
                $table = 'm_pedido';
                $columTdoc = '';
                $columNum = 'num_ped';
                $swtEst = "SWT_PED = 0";

                // Validación: Comprobar si existen guías relacionadas al pedido con swt_est != 1
                $queryValPedido = "SELECT 1 FROM d_guia dg
                    LEFT JOIN m_guia mg ON dg.id_mguia = mg.id
                    LEFT JOIN d_pedido dp ON dp.id = dg.id_dpedido
                    LEFT JOIN m_pedido mp ON mp.id = dp.id_mpedido
                    WHERE mp.id_dtab_srr = ? AND $columNum = ? AND mg.swt_est != 0";

                $stmtPedido = $conection->prepare($queryValPedido);
                $stmtPedido->bind_param('ii', $srr, $crr);
                $stmtPedido->execute();
                $resultPedido = $stmtPedido->get_result();

                if ($resultPedido->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'Existen guías asociadas al pedido.']);
                    $stmtPedido->close();
                    $conection->close();
                    exit;
                }
                $stmtPedido->close();
                break;

            default:
                switch ($val) {
                    case 0:  // Anulación de Guía
                        $table = 'm_guia';
                        $columTdoc = "id_dtab_tpg = ? AND";
                        $columNum = 'num_guia';

                        // Validación: Comprobar si existen documentos cliente relacionados con swt_est != 1
                        $queryValGuia = "SELECT 1 FROM d_doccli dd
                            LEFT JOIN m_doccli md ON dd.id_mdoccli = md.id
                            LEFT JOIN d_guia dg ON dg.id = dd.id_dguia
                            LEFT JOIN m_guia mg ON mg.id = dg.id_mguia
                            WHERE mg.id_dtab_srr = ? AND $columNum = ? AND md.swt_est != 0";

                        $stmtGuia = $conection->prepare($queryValGuia);
                        $stmtGuia->bind_param('ii', $srr, $crr);
                        $stmtGuia->execute();
                        $resultGuia = $stmtGuia->get_result();

                        if ($resultGuia->num_rows > 0) {
                            echo json_encode(['success' => false, 'message' => 'Existen documentos asociados a la guía.']);
                            $stmtGuia->close();
                            $conection->close();
                            exit;
                        }
                        $stmtGuia->close();
                        break;

                    case 1:
                    case 2:  // Anulación de Documento Cliente
                        $table = 'm_doccli';
                        $columTdoc = "id_dtab_tdoc = ? AND";
                        $columNum = 'num_docu';
                        break;

                    default:
                        echo json_encode(['status' => 'error', 'message' => "Valor enviado no válido: $val"]);
                        $conection->close();
                        exit;
                }
                break;
        }

        // Si todo está correcto, proceder a realizar la actualización
        $query = "UPDATE $table SET $swtEst WHERE $columTdoc ID_DTAB_SRR = ? AND $columNum = ?";
        $stmtUpdate = $conection->prepare($query);
        
        if (!empty($columTdoc)) {
            $stmtUpdate->bind_param('iis', $tdoc, $srr, $crr);
        } else {
            $stmtUpdate->bind_param('is', $srr, $crr);
        }
        
        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Documento anulado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error ejecutando la consulta.']);
        }

        $stmtUpdate->close();
        $conection->close();
        exit;
    }
    
    // Traer ID del Documento
    if ($_POST['action'] == 'idDocumentoSrrCrr') {
        $val = $_POST['val'];
        $tdoc = $_POST['tdoc'];
        $srr = $_POST['srr'];
        $crr = $_POST['crr'];

        $table = '';
        $columTdoc = '';
        $columNum = '';
        $tipo = '';

        switch ($tdoc) {
            case 3997:  // Orden de Compra
                $table = 'm_ordcom';
                $columTdoc = '';
                $columNum = 'num_ocom';
                $tipo = 3;  // Tipo 3 para Orden de Compra
                break;
                
            case 3982:  // Pedido
                $table = 'm_pedido';
                $columTdoc = '';
                $columNum = 'num_ped';
                $tipo = 0;  // Tipo 0 para Pedido
                break;

            default:
                switch ($val) {
                    case 0:  // Guía
                        $table = 'm_guia';
                        $columTdoc = "id_dtab_tpg = ? AND";
                        $columNum = 'num_guia';
                        $tipo = 1;  // Tipo 1 para Guía
                        break;

                    case 1:
                    case 2:  // Documento Cliente
                        $table = 'm_doccli';
                        $columTdoc = "id_dtab_tdoc = ? AND";
                        $columNum = 'num_docu';
                        $tipo = 4;  // Tipo 4 para Documento Cliente
                        break;

                    default:
                        echo json_encode(['status' => 'error', 'message' => "Valor enviado no válido: $val"]);
                        $conection->close();
                        exit;
                }
                break;
        }

        // Preparar consulta para obtener el ID
        $query = "SELECT ID FROM $table WHERE $columTdoc ID_DTAB_SRR = ? AND $columNum = ?";
        $stmt = $conection->prepare($query);

        if (!empty($columTdoc)) {
            $stmt->bind_param('iis', $tdoc, $srr, $crr);
        } else {
            $stmt->bind_param('is', $srr, $crr);
        }

        $stmt->execute();
        $resultQuery = $stmt->get_result();
        $result = $resultQuery->fetch_assoc();

        if ($result) {
            // Incluir el tipo en la respuesta
            echo json_encode(['success' => true, 'id' => $result['ID'], 'tipo' => $tipo]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error ejecutando la consulta:$query, $tdoc, $srr, $crr"]);
        }

        $stmt->close();
        $conection->close();
        exit;
    }

    
    
    if ($_POST['action'] == 'bonusChange') {
        $crr = $_POST['crr'];
        $swt = $_POST['swt'];
        
        mysqli_autocommit($conection, false);
        
        $query = mysqli_prepare($conection, "CALL bonusChange(?,?)");
        mysqli_stmt_bind_param($query, "ii", $crr, $swt);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            mysqli_rollback($conection); // Deshacer la transacción en caso de error
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        mysqli_commit($conection);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    //Obtener el ultimo documento
    if ($_POST['action'] == 'getNewDoc') {
        $val = $_POST['val'] ?? 0;
        $srr = $_POST['srr'] ?? 0;
        $tdoc = '';
        switch ($val) {
            case 0:
                $num = 'NUM_PED'; 
                $table = 'm_pedido';
                break;
            // case 1:
            //     $tdoc = '3982'; 
            //     $table = 't_doccli';
            //     break;
            // case 2:
            //     $tdoc = '3982'; 
            //     $table = 't_doccli';
            //     break;
            // case 3:
            //     $tdoc = '3982'; 
            //     $table = 't_doccli';
            //     break;
            case 4:
                $num = 'NUM_DOCU'; 
                $table = 'm_doccli';
                $tdoc = " AND ID_DTAB_TDOC = ".$_POST['tdoc'];
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Valor de swt no válido']);
                $conection->close(); 
                exit;
        }
        $query = mysqli_query($conection, "SELECT COALESCE(MAX($num),0)+1 AS newNum FROM $table WHERE ID_DTAB_SRR = $srr $tdoc ");
        $result = mysqli_fetch_assoc($query);
        if ($query) {
            echo json_encode(['status' => 'success', 'num' => $result['newNum']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Error executing query: $conection->error"]);
        }
        $conection->close();
        exit;
    }

    if ($_POST['action'] == 'checkTempDetail') {
        $numSerie = (int)$_POST['numSerie'];
        $serieId = (int)$_POST['serieId'];
        
        $stmt = $conection->prepare("SELECT correlativo FROM detalle_temp WHERE token_user = ? AND ID_DTAB_SRR = ? AND NUM_PED = ? AND id_mpedido != 0 AND swt_ped = 1");
        $stmt->bind_param("sii", $token, $serieId, $numSerie); 
        
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = ($result->num_rows > 0) ? 1 : 0;

        echo $data;
        $stmt->close();
        $conection->close();
    }

    // Borrar detalle temp con srr y crr
    if ($_POST['action'] == 'deletePreviousDetail') {
        $srr = $_POST['srr'] ?? 0;
        $crr = $_POST['crr'] ?? 0;
        $tdoc = $_POST['tdoc'] ?? 0;
        if($tdoc){
            $stmt = $conection->prepare("DELETE FROM detalle_temp_doc WHERE TOKEN_USER = ? AND ID_DTAB_SRR = ? AND NUM_DOCU = ? AND ID_DTAB_TDOC = ?");
            $stmt->bind_param("siii", $token, $srr, $crr, $tdoc); 
        }else{
            $stmt = $conection->prepare("DELETE FROM detalle_temp WHERE token_user = ? AND ID_DTAB_SRR = ? AND NUM_PED = ? AND id_mpedido != 0");
            $stmt->bind_param("sii", $token, $srr, $crr); 
        }
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        $stmt->close();
        $conection->close();
    }

    
    if ($_POST['action'] == 'changeCoinDetails') {
        $mon = $_POST['mon'];
        $fec = $_POST['fec'];
        $idMped = $_POST['idMped'];
        $lista = $_POST['lista'];
        
        mysqli_autocommit($conection, false);
        
        $query = mysqli_prepare($conection, "CALL changeCoinDetails(?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($query, "isisi", $mon, $fec,$idMped,$token,$lista);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            mysqli_rollback($conection); // Deshacer la transacción en caso de error
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        mysqli_commit($conection);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'consultarCodForm') {
        $query = "SELECT swt_codform FROM t_tablas LIMIT 1";
        $stmt = $conection->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        echo $data['swt_codform'];
        $stmt->close();
        $conection->close();
    }
    
    if ($_POST['action'] == 'updateCodForm') {
        $swt_codform = $_POST['swt_codform'];
        $query = "UPDATE t_tablas SET swt_codform = ? LIMIT 1";
        $stmt = $conection->prepare($query);
        $stmt->bind_param("i", $swt_codform);
        $stmt->execute();
        echo "swt_codform actualizado";
        $stmt->close();
        $conection->close();
    }

    if ($_POST['action'] == 'getChangeRate') {
        $fec = $conection->real_escape_string($_POST['fec']); // Sanitiza el input
    
        $query = "SELECT
                CASE
                    WHEN tcv_venta <> 0 THEN tcv_venta
                    WHEN tcv_venta = 0 AND tip_cmb <> 0 THEN tip_cmb
                    ELSE 1
                END AS tcambio
            FROM (
                SELECT 
                    TIP_CMB AS tip_cmb,
                    TCV_VENTA AS tcv_venta
                FROM t_cambio
                WHERE FEC_CMB = ?
            ) AS subtabla
        ";
    
        // Preparar y ejecutar la consulta
        if ($stmt = $conection->prepare($query)) {
            $stmt->bind_param('s', $fec); // Enlaza el parámetro
            $stmt->execute();
            $result = $stmt->get_result();
    
            // Obtener y enviar el resultado
            if ($data = $result->fetch_assoc()) {
                echo json_encode($data['tcambio']); // Devuelve el resultado en formato JSON
            } else {
                echo json_encode(null); // Manejo de caso cuando no hay datos
            }
    
            $stmt->close();
        } else {
            echo json_encode(['error' => 'Error en la preparación de la consulta.']);
        }
    
        $conection->close();
    }

    // d_tablas SUNAT COD DE BBySS DETRACCION
    if ($_POST['action'] == 'numeroCuentaBancaria') {
        $query = mysqli_query($conection, "SELECT des_item FROM d_tablas WHERE id_mtablas=3 and val_item=1;");
        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $row = mysqli_fetch_assoc($query);
        $num = $row['des_item'];
    
        echo json_encode($num);
        mysqli_close($conection);        
    }

    // d_tablas SUNAT COD DE BBySS DETRACCION
    if ($_POST['action'] == 'codBienServ') {
        $query = mysqli_query($conection, "SELECT id, des_item,val_item FROM d_tablas WHERE id_mtablas = 81");
    
        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $options = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = '<option data-tasa="'.$row['val_item'].'" value="' . $row['id'] . '" >' . $row['des_item'] . '</option>';
        }
    
        echo json_encode( implode('', $options));
        mysqli_close($conection);        
    }
    
    // d_tablas medio de pago sunat
    if ($_POST['action'] == 'medioPago') {
        $query = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas = 87");
        if (!$query) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $options = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $options[] = '<option value="' . $row['id'] . '" >' . $row['des_item'] . '</option>';
        }
    
        echo json_encode( implode('', $options));
        mysqli_close($conection);        
    }
    
    if ($_POST['action'] == 'sunatChangeRate') {
        $fec = $conection->real_escape_string($_POST['fec']); 
        $query = "SELECT tip_cmb
                FROM  t_cambio
                WHERE FEC_CMB = ?";

        // Preparar y ejecutar la consulta
        if ($stmt = $conection->prepare($query)) {
            $stmt->bind_param('s', $fec); // Enlaza el parámetro
            $stmt->execute();
            $result = $stmt->get_result();

            // Obtener y enviar el resultado
            if ($data = $result->fetch_assoc()) {
                echo json_encode($data['tip_cmb']); 
            } else {
                echo json_encode(null);
            }
            $stmt->close();
        } else {
            echo json_encode(['error' => 'Error en la preparación de la consulta.']);
        }
        $conection->close();
    }
    //Traer series y num max por documento
    
    if ($_POST['action'] == 'consultaDocSeries') {
        $tdoc = isset($_POST['tdoc']) ? "AND td.id_dtab_tdc = ".$_POST['tdoc'] : '';
        $serieId = $_POST['serieId']??0;

        $query_serie = mysqli_query($conection, "SELECT d.id, d.des_item FROM m_usrseries m
                                                INNER JOIN d_tablas d ON m.id_dtab_srr = d.id 
                                                WHERE id_mtablas = 1 AND m.id_musuari = $user_id 
                                                ORDER BY m.swt_default DESC, m.id_dtab_srr DESC");

        if (!$query_serie) {
            echo json_encode(['error' => mysqli_error($conection)]);
            exit();
        }
        $series = [];
        while ($row = mysqli_fetch_assoc($query_serie)) {
            $series[] = $row;
        }
        if ($serieId == 0) {
            $serieId = $series[0]['id'];
        }
        $query_default = mysqli_query($conection,"SELECT COALESCE(MAX(td.num_ero), 0) + 1 as num_ped FROM t_doccli td WHERE td.id_dtab_srr = $serieId $tdoc ");
        // echo "SELECT COALESCE(MAX(td.num_ero), 0) + 1 as num_ped FROM t_doccli td WHERE td.id_dtab_srr = $serieId $tdoc ";
        $valor_qd = mysqli_fetch_assoc( $query_default );
        $seriePorDefecto = $valor_qd['num_ped']; // Valor por defecto para el número de serie

        echo json_encode(['series' => $series, 'seriePorDefecto' => $seriePorDefecto]);

        mysqli_close($conection);
    }

    // Buscar proveedor
    if ($_POST['action'] == 'checkDetailsTempDoc') {
        $srr = $_POST['srr'];
        $num = $_POST['num'];
        $tdoc = $_POST['tdoc'];
        
        $stmt = $conection->prepare("SELECT id FROM detalle_temp_doc WHERE token_user = ? AND ID_DTAB_TDOC = ? AND ID_DTAB_SRR = ? AND NUM_DOCU = ? AND id_mdoccli != 0 AND swt_est = 1");
        $stmt->bind_param("siii", $token, $tdoc, $srr, $num); 
        
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = ($result->num_rows > 0) ? 1 : 0;

        echo $data;
        $stmt->close();
        $conection->close();
    }


    if ($_POST['action'] == 'consultar_documento') {
        $tdoc = $_POST['tdoc'] ?? 0;
        $srr = $_POST['srr'] ?? 0;
        $num = $_POST['num'] ?? 0;
        // $idMped = $_POST['idMped'] ?? 0;
    
        if (!$num || !$srr || !$tdoc) {
            echo json_encode(['error' => 'Tdoc, srr o num no recibido']);
            exit();
        }

        // Definir la consulta base según el valor de $swt
        $query = "SELECT c.id as idcliente, c.ruc_cli as ruc, c.des_cli as nombre, m.fec_docu, m.id_dtab_mon, mon.des_item as mon, u.id as user, u.des_usr, m.swt_igv, m.num_docu, m.fec_usu , m.swt_est 
            FROM m_doccli m 
            LEFT JOIN m_client c ON c.id = m.id_mclient
            LEFT JOIN m_usuari u ON m.id_musuari = u.id 
            LEFT JOIN d_tablas mon ON m.id_dtab_mon = mon.id 
            WHERE m.id_dtab_tdoc = ? AND m.id_dtab_srr = ? AND m.num_docu = ?";

        if ($stmt = mysqli_prepare($conection, $query)) {
            mysqli_stmt_bind_param($stmt, 'iii', $tdoc,$srr, $num);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['error' => 'Error en la consulta preparada']);
            exit();
        }

        if ($stmt = mysqli_prepare($conection, "CALL consultar_documento(?, ?, ?, ?)")) {
            mysqli_stmt_bind_param($stmt, 'iiis', $tdoc,$srr, $num, $token);
            mysqli_stmt_execute($stmt);
            $result_call = mysqli_stmt_get_result($stmt);
            $resultado = mysqli_fetch_all($result_call, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['error' => 'Error en la llamada al procedimiento almacenado']);
            exit();
        }
        echo json_encode(['clientes' => $clientes, 'resultado_procedimiento' => $resultado]);
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'searchNumRef') {
        $cli = $_POST['cli'];
        $tref = $_POST['tref'];
    
        $query = mysqli_prepare($conection, "SELECT ID, NUM_ERO, DATE(FEC_DOCU) as FEC_DOCU FROM m_doccli WHERE ID_MCLIENT = ? AND ID_DTAB_TDOC = ? AND SWT_EST = 1 ORDER BY FEC_DOCU DESC, ID DESC");
        mysqli_stmt_bind_param($query, "ii", $cli, $tref);
        
        if (!mysqli_stmt_execute($query)) {
            echo json_encode(['error' => mysqli_stmt_error($query)]);
            exit();
        }
        $result = mysqli_stmt_get_result($query);
        $options[] = '<option value="0">Seleccionar Documento</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = '<option data-fec="'.$row['FEC_DOCU'].'" value="' . $row['ID'] . '">' . $row['NUM_ERO'] . ' | ' . $row['FEC_DOCU'] . '</option>';
        }
        echo json_encode(['options' => implode('', $options)]);
        mysqli_stmt_close($query);
        mysqli_close($conection);
    }

    if ($_POST['action'] == 'cambioDocGlobal') {
        $tdoc = $_POST['tdoc'];
        $srr = $_POST['srr'];
        $num = $_POST['num'];
        $time = date('H:i:s');
        $fec = $_POST['fec'].' '.$time;
        $cli = $_POST['cli']??1;
        $mon = $_POST['mon'];
        $cpg = $_POST['cpg']??0;
        
        $nref = $_POST['nref']??-1;
        echo "UPDATE detalle_temp_doc SET FEC_DOCU = $fec, ID_MCLIENT = $cli, ID_DTAB_MON = $mon, ID_DTAB_CPAG = $cpg, ID_MDOCCLIREF = $nref WHERE id_dtab_tdoc = $tdoc AND token_user = $token AND ID_DTAB_SRR = $srr AND NUM_DOCU = $num";

        $query_update = "UPDATE detalle_temp_doc SET FEC_DOCU = ?, ID_MCLIENT = ?, ID_DTAB_MON = ?, ID_DTAB_CPAG = ? WHERE id_dtab_tdoc = ? AND token_user = ? AND ID_DTAB_SRR = ? AND NUM_DOCU = ?";
        $stmt = $conection->prepare($query_update);
        $stmt->bind_param('siiiisii', $fec,  $cli,  $mon, $cpg, $tdoc, $token, $srr, $num);
        $stmt->execute();
        mysqli_close($conection);
    }

    
    if ($_POST['action'] == 'procesar_documento') {
        $tdoc = $_POST['tdoc'] ?? 0;
        $srr = $_POST['srr'] ?? 0;
        $crr = $_POST['crr'] ?? 0;
        
        $fec = $_POST['fec'];
        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));
        $moduleStatus = checkModuleStatus($conection, $month, $year, 2);//Modulo clientes -> 2
        $fecha_hora = "$fec $current_time";

        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }
        //Depuracion
        // echo $token, $current_date, $user_id, $fecha_hora, $tdoc, $srr, $crr;
        // exit;
        $query = "CALL procesar_documento(?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conection->prepare($query);
    
    
        $stmt->bind_param("ssisiii", $token, $current_date, $user_id, $fecha_hora, $tdoc, $srr, $crr); 
    
        $result = $stmt->execute();
    
        if (!$result) {
            echo json_encode(['success' => false, 'message' => mysqli_stmt_error($stmt)]);
            $stmt->close();
            mysqli_close($conection);
            exit;
        }
    
        $result = $stmt->get_result();
    
        if ($result) {
            $row = $result->fetch_assoc();
            $message = $row['message'];

            if ($message === 'success') {
                $id = $row['ID'];
                $swt = $row['swt'];
                //En caso halla validado la facturacion electronica
                if(!$swt){
                    $msg = ', facturación electrónica desactivada';
                    $id = 0;
                }else if($swt == 1){
                    $msg = ', serie no asignada a factura electronica';
                    $id = 0;
                }else{
                    $msg = ', enviada a SUNAT!';
                }
                echo json_encode(['success' => true, 'message' => "Factura procesada exitosamente$msg", 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'message' => $message]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la obtención de resultados: ' . mysqli_stmt_error($stmt)]);
        }
    
        $stmt->close();
        mysqli_close($conection);
    }

    
    if ($_POST['action'] == 'changeCoinDocument') {
        $mon = $_POST['mon'];
        $fec = $_POST['fec'];
        
        $tdoc = $_POST['tdoc']??0;
        $srr = $_POST['srr']??0;
        $crr = $_POST['crr']??0;
        $tcambio = $_POST['tcambio']??1;
        
        $query = mysqli_prepare($conection, "UPDATE detalle_temp_doc 
                                SET PRE_DFAC =  
                                CASE WHEN ? = 3503 THEN PRE_DFAC * ? ELSE PRE_DFAC / ? END 
                                WHERE TOKEN_USER = ? AND id_dtab_tdoc = ? AND NUM_DOCU = ? AND id_dtab_srr = ?");
        mysqli_stmt_bind_param($query, "iddsiii", $mon, $tcambio, $tcambio, $token, $tdoc, $crr, $srr);
        mysqli_stmt_execute($query);
        
        if (!$query) {
            echo json_encode(['success' => false,'error' => mysqli_error($conection)]);
            exit();
        }
        
        mysqli_stmt_close($query);
        
        echo json_encode(['success' => true]);
        
        mysqli_close($conection);
    }

    
    if ($_POST['action'] == 'addProduc99999') {
        $tdoc = $_POST['tdoc'];
        $srr = $_POST['srr'];
        $crr = $_POST['crr'];

        $nref = $_POST['nref'];
        $mon = $_POST['mon'];
        $cpag = $_POST['cpag'];

        // Inserción de nuevo proveedor
        $query_insert = mysqli_query($conection, 
        "INSERT INTO detalle_temp_doc (
                    TOKEN_USER, ID_DTAB_TDOC, ID_DTAB_SRR, NUM_DOCU, ID_MDOCCLIREF, ID_DTAB_MON, ID_DTAB_CPAG, ID_MDOCCLI,
                    ID_MPRODUC, OBS_DOC, 
                    CAN_DFAC, CAN_MAX, 
                    PRE_DFAC, POR_TDES, DCT_DFAC, IMP_DFAC, 
                    SWT_AFECTO, SWT_IGV, POR_TDCT, POR_TIGV, SWT_EST)
                SELECT 
                    '$token', $tdoc, $srr, $crr, $nref, $mon, $cpag, 0,
                    ID, DES_PROD, 
                    1, 1, 
                    0, 0, 0, 0, 
                    1, 0, 0, (SELECT NPORIGV FROM t_tablas), 1
                FROM m_produc WHERE ID = 1
                UNION ALL
                SELECT 
                    '$token', $tdoc, $srr, $crr, $nref, $mon, $cpag, 0,
                    ID, DES_PROD, 
                    1, 1, 
                    0, 0, 0, 0, 
                    0, 0, 0, (SELECT NPORIGV FROM t_tablas), 1
                FROM m_produc WHERE ID = 1");

        if ($query_insert) {
            echo json_encode(['success' => true, 'message' => mysqli_insert_id($conection)]);
        } else {
            throw new mysqli_sql_exception(mysqli_error($conection));
        }
        mysqli_close($conection);
        
    }
    
    
}

