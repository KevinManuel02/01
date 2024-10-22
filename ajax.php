<?php

session_start();
if(empty($_SESSION[$_SESSION['db'].'active']))
{
    header('location: ../');
    exit();
}
include "../conexion.php";
//print_r($_POST);    exit;
date_default_timezone_set('America/Lima');
$current_date = date('Y-m-d H:i:s');
$idUser = $_SESSION[$_SESSION['db'].'idUser'];
$token = md5($idUser);
if(!empty($_POST)){
    //Extraer los datos del producto
    if($_POST['action'] == 'infoProducto'){
        
        if (empty($_POST['busqueda'])){
            $busqueda = '';
        }else{
            $busqueda = $_POST['busqueda'];
        }
        if (empty($_POST['producto'])){
            $producto = '';
        }else{
            $producto = $_POST['producto'];
        }
        $query = mysqli_query($conection,"SELECT id, des_prod,val_sol FROM m_produc WHERE  swt_prod = 1 and id = $producto");

        mysqli_close($conection);
        $result = mysqli_num_rows($query);
        if($result > 0){
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo 'no data';
        exit;
    }
    //Actualizar lista de modulos
    if($_POST['action'] == 'updateModuleList'){
        $busqueda = $_POST['busqueda']; 
        $listaBusqueda = '';
        $query = mysqli_query($conection, "SELECT p.id,  p.des_item, p.frm_item, p.icon_item 
        FROM d_dopcion p 
        INNER JOIN d_usuari u ON u.id_dopcion = p.id 
        WHERE p.id_mopcion IN (SELECT m.id  FROM m_opcion m WHERE swt_opc = 1) 
        AND p.id_dopcion IN (SELECT num_item FROM d_opcion 
                                WHERE (p.des_item LIKE '%$busqueda%' OR p.frm_item LIKE '%$busqueda%' OR p.icon_item LIKE '%$busqueda%') 
                                AND  id_mopcion = p.id_mopcion AND num_item = p.id_dopcion AND swt_item = 1)  
        AND p.swt_opc = 1 AND u.id_musuari = $idUser ORDER BY p.des_item LIMIT 8;");
            mysqli_close($conection);
            $result = mysqli_num_rows($query);

        if($result>0){
            while ($data = mysqli_fetch_array($query)){
                ////Mostrando modulos
                
                $listaBusqueda .= 
                        '<li class="principal sidebar__item">
                            <a class="material-symbols-outlined" data-idDopcion="'.$data['id'].'" href="'.$data['frm_item'].'">'.$data['icon_item'].'<span class="span_principal menu">'.$data['des_item'].'</span></a>
                        </li>';
            }
            $arrayData['lista'] = $listaBusqueda;
                echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
        }else{
            $listaBusqueda ='<li class="principal sidebar__item">
            <span class="span_principal menu">No se encontraron modulos</span>
            </li>';
            $arrayData['lista'] = $listaBusqueda;
                echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    if ($_POST['action'] == 'updateProductList') {
        $busqueda = $_POST['busqueda']; 
        $idcli = $_POST['idcli']??0; 
        $list = $_POST['list']??0; 
        $idMdoc = $_POST['idMdoc']??0; 
        $idTdoc = $_POST['idTdoc']??0; 
        $swtDoc = $_POST['swtDoc']??0; 
        // $queryDoc = $idMdoc ? "INNER JOIN d_doccli dd ON dd.ID_MPRODUC = p.id AND dd.ID_MDOCCLI = $idMdoc AND dd.ID NOT IN (SELECT ID_DDOCCLI FROM detalle_temp_doc WHERE token_user = '$token' AND ID_DTAB_TDOC = $idTdoc AND ID_MDOCCLIREF = $idMdoc AND ID_DDOCCLI IS NOT NULL)":'';
        if($swtDoc){
            $queryDoc = $idMdoc ? "INNER JOIN d_doccli dd ON dd.ID_MPRODUC = p.id 
                                AND dd.ID_MDOCCLI = $idMdoc 
                                AND dd.ID NOT IN 
                                    (SELECT ID_DDOCCLI 
                                    FROM detalle_temp_doc 
                                    WHERE token_user = '$token' 
                                    AND ID_DTAB_TDOC = $idTdoc 
                                    AND ID_MDOCCLIREF = $idMdoc 
                                    AND ID_DDOCCLI IS NOT NULL)":'';
            $querySelect = $idMdoc ? "dd.id as doccli,":'';
        }else{
            $queryDoc = $idMdoc ? "INNER JOIN d_ordcom doo ON doo.ID_MPRODUC = p.id 
                                AND doo.ID_MORDCOM = $idMdoc 
                                AND doo.ID NOT IN 
                                    (SELECT ID_DORDCOM 
                                    FROM detalle_temp_doc 
                                    WHERE token_user = '$token' 
                                    AND ID_DORDCOM IS NOT NULL)":'';
            $querySelect = $idMdoc ? "doo.id as doccli,":'';
        }

        if(empty($_POST['busqueda']) && $idMdoc == 0){
            $listaBusqueda = '<p>Escribir al menos 1 digito para buscar.</p>';
            $arrayData['lista'] = $listaBusqueda;
            echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $listaBusqueda = '<div class="header_product_list" style="display: none;"><span>||</span>
                            <span title="Código del sistema">Código</span><span>||</span>
                            <span title="Código Equivalente">Cod.Eqv.</span><span>||</span>
                            <span title="Descripción">Descrip.</span><span>||</span>
                            <span title="Marca del producto">Marca</span><span>||</span>';
        if(!$idMdoc){
            $listaBusqueda .= '<span title="Moneda Nacional - Moneda Extranjera">MN-ME</span><span>||</span>';
        }
                            

        $pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1; // Asegurarse que sea al menos 1
        $registros = 8;
        $offset = ($pagina - 1) * $registros;
    
        // Query to get the list of warehouses with swt_ref5 = 1
        $warehouses = [];
        $query_warehouses = $conection->query("SELECT COALESCE(abr_item,'A') as abr_item FROM d_tablas WHERE ID_MTABLAS = 5 AND swt_ref5 = 1");
        while ($row = $query_warehouses->fetch_assoc()) {
            $warehouses[] = $row['abr_item'];
        }
        
        // Prepare the dynamic columns for stock from each warehouse
        $warehouse_columns = '';
        if(!$idMdoc){
            foreach ($warehouses as $abr_item) {
                $warehouse_columns .= ", 
                    COALESCE((SELECT COALESCE(sl.STK_ACT, s.STK_ACT)
                    FROM stock s
                    LEFT JOIN stock_local sl ON sl.ID_AREA = dt.ID AND sl.ID_MPRODUC = p.id
                    WHERE s.ID_AREA = dt.ID AND s.ID_MPRODUC = p.id), 0) AS STK_$abr_item";

                $listaBusqueda .= "<span title='Stock del almacén'>STK_$abr_item</span><span>||</span>";
            }

            $listaBusqueda .= '<span title="Stock Total">Stock</span><span>||</span>';
        }
        // Now build the main product query
        // Main product query (without JOIN on d_tablas)
        $query = $conection->query("SELECT SQL_CALC_FOUND_ROWS 
                                p.id, 
                                p.des_prod, 
                                p.rut_imagen,
                                p.CDG_PROD, 
                                p.CDG_EQV,
                                marca.des_item,
                                $querySelect
                                COALESCE(
                                    (SELECT PRE_SOL FROM m_lista WHERE ID_MCLIENT = $idcli AND ID_MPRODUC = p.id AND ID_DTAB_CDGLIST = $list),
                                    (SELECT PRE_SOL FROM m_lista WHERE ID_MCLIENT = 0 AND ID_MPRODUC = p.id AND ID_DTAB_CDGLIST = $list),
                                    (SELECT val_sol FROM m_produc WHERE id = p.id)
                                ) AS soles,
                                COALESCE(
                                    (SELECT PRE_DOL FROM m_lista WHERE ID_MCLIENT = $idcli AND ID_MPRODUC = p.id AND ID_DTAB_CDGLIST = $list),
                                    (SELECT PRE_DOL FROM m_lista WHERE ID_MCLIENT = 0 AND ID_MPRODUC = p.id AND ID_DTAB_CDGLIST = $list),
                                    (SELECT val_dol FROM m_produc WHERE id = p.id)
                                ) AS dolares,
                                ROUND(COALESCE(CASE WHEN usl.STK_ACT IS NULL THEN st_total.STK_ACT ELSE usl.STK_ACT END, 0), 2) AS stock,
                                COALESCE(usl.fecha, CASE WHEN usl.STK_ACT IS NULL THEN st_total.STK_ACT ELSE usl.STK_ACT END, 0) AS fecha
                            FROM 
                                m_produc p
                            LEFT JOIN 
                                d_tablas marca ON marca.id = p.id_dtab_marca
                            LEFT JOIN 
                                ultimo_stock_local usl ON p.id = usl.ID_MPRODUC
                            LEFT JOIN (
                                SELECT ID_MPRODUC, SUM(STK_ACT) AS STK_ACT 
                                FROM stock 
                                GROUP BY ID_MPRODUC 
                                HAVING SUM(STK_ACT) > 0
                            ) st_total ON p.id = st_total.ID_MPRODUC
                            $queryDoc
                            WHERE 
                                (p.id LIKE '%$busqueda%' 
                                OR p.des_prod LIKE '%$busqueda%'  
                                OR p.cdg_prod LIKE '%$busqueda%' 
                                OR p.cdg_eqv LIKE '%$busqueda%' 
                                OR marca.des_item LIKE '%$busqueda%') 
                                AND p.swt_prod = 1 
                                AND swt_vta = 1
                            ORDER BY 
                                p.des_prod 
                            LIMIT $offset, $registros
                            ");
        $result = $conection->query("SELECT FOUND_ROWS() AS total");
        $totalRegistros = $result->fetch_assoc()['total'];
        $totalPaginas = ceil($totalRegistros / $registros);
    
        if ($query->num_rows > 0) {
            // Loop through the fetched products
            while ($data = $query->fetch_assoc()) {
                $foto = ($data['rut_imagen'] != 'img_producto.png') ? 'img/uploads/'.$data['rut_imagen'] : 'img/'.$data['rut_imagen'];
                $foto = file_exists($foto) ? $foto : 'img/uploads/producto.png';

                $listaBusqueda .= '</div><div class="product-card" id="cart-'.$data['id'].'" data-doccli="'.($data['doccli']??0).'">
                                        <img src="'.$foto.'" alt="Producto '.$data['id'].'" title="'.$data['des_prod'].'" onerror="this.onerror=null;this.src=\'img/uploads/img_producto.png\';">
                                        <div class="product-info">
                                            <h2 title="Código de producto: '.$data['CDG_PROD'].'">'.$data['CDG_PROD'].'</h2>
                                            <p title="Código equivalente: '.$data['CDG_EQV'].'">'.$data['CDG_EQV'].'</p>
                                            <p title="Descripcion: '.$data['des_prod'].'">'.$data['des_prod'].'</p>
                                            <p title="Marca: '.$data['des_item'].'">'.$data['des_item'].'</p>';
                                            
                if(!$idMdoc){   $listaBusqueda .='<p title="Moneda Nacional: '.number_format($data['soles'],4).' - Moneda Extranjera: '.number_format($data['dolares'],4).'">'.number_format($data['soles'],2).' - '.number_format($data['dolares'],2).'</p>';
                 
                // Fetch stock for each warehouse for the current product
                $query_stock = $conection->query("SELECT dt.abr_item, dt.des_item,
                                                    COALESCE(sl.STK_ACT, s.STK_ACT, 0) AS stock 
                                                FROM d_tablas dt
                                                LEFT JOIN stock_local sl ON sl.ID_AREA = dt.ID AND sl.ID_MPRODUC = {$data['id']}
                                                LEFT JOIN stock s ON s.ID_AREA = dt.ID AND s.ID_MPRODUC = {$data['id']}
                                                WHERE dt.ID_MTABLAS = 5 AND dt.swt_ref5 = 1
                                            ");

                // Loop through each warehouse's stock for the product
                while ($stock_data = $query_stock->fetch_assoc()) {
                    $listaBusqueda .= "     <p title='Stock en {$stock_data['des_item']}'>".$stock_data['stock']."</p>";
                }

                $listaBusqueda .= '         <p title="Fecha/Stock: '.$data['fecha'].'">'.$data['stock'].'</p>
                                            <i class="fa-solid fa-circle-info product" onclick="event.stopPropagation();seeAllStocks('.$data['id'].')" title="Ver Stock Total"></i>';
            }
                $listaBusqueda .='      </div>
                                    </div>';
            }

            $arrayData['lista'] = $listaBusqueda;
            $arrayData['total_paginas'] = $totalPaginas;
            $arrayData['pagina_actual'] = $pagina;
    
            echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
        } else {
            $listaBusqueda = '<p>No se encontraron registros.</p>';
            $arrayData['lista'] = $listaBusqueda;
            echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
        }
    
        exit;
    }
    
    // Mostrar stock por almacen 
    if ($_POST['action'] == 'seeAllStocks') {
        $id = $_POST['id']; 
        $list = [];
        
        $query = mysqli_query($conection, "SELECT 
                                            p.des_prod, dt.des_item, dt.abr_item,
                                            ROUND(COALESCE(sl.STK_ACT, s.STK_ACT, 0), 2) AS stock
                                        FROM 
                                            d_tablas dt
                                        LEFT JOIN 
                                            stock_local sl ON sl.ID_AREA = dt.ID AND sl.ID_MPRODUC = $id
                                        LEFT JOIN 
                                            stock s ON s.ID_AREA = dt.ID AND s.ID_MPRODUC = $id
                                        LEFT JOIN
                                            m_produc p ON p.ID = $id
                                        WHERE ROUND(COALESCE(sl.STK_ACT, s.STK_ACT, 0), 2) <> 0 and dt.ID_MTABLAS = 5 AND dt.swt_item = 1
                                        ORDER BY 
                                            stock DESC;");
        mysqli_close($conection);
        
        if (mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                // Change des_item to abr_item and send des_item as title
                $list[] = [
                    'des_prod' => $row['des_prod'],
                    'abr_item' => $row['abr_item'],  
                    'des_item' => $row['des_item'],  
                    'stock' => $row['stock']
                ];
            }
        }

        echo json_encode($list);  // Send the modified data as JSON
        exit;
    }


    //Agregar productos
    if($_POST['action'] == 'addProduct'){
        if(!empty($_POST['cantidad']) || !empty($_POST['precio'])|| !empty($_POST['producto_id'])){
            $cantidad = $_POST['cantidad'];
            $precio = $_POST['precio'];
            $producto_id = $_POST['producto_id'];

            $query_insert = mysqli_query($conection,"INSERT INTO entradas(codproducto,cantidad,precio,usuario_id) VALUES ($producto_id,$cantidad, $precio, $idUser)");

            if($query_insert){
                //Ejecutar procedimiento almacenado
                $query_upd = mysqli_query($conection,"CALL actualizar_precio_producto($cantidad,$precio,$producto_id)");
                $result_pro = mysqli_num_rows($query_upd);
                if($result_pro > 0){
                    $data = mysqli_fetch_assoc($query_upd);
                    $data['producto_id'] = $producto_id;
                    echo json_encode($data,JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }else{
                echo 'error';
            }
            mysqli_close($conection);
        }else{
            echo 'error';
        }exit;
    }

    //Eliminar producto
    if ($_POST['action'] == 'delProduct') {
        if (empty($_POST['pr']) || !is_numeric($_POST['pr'])) {
            echo 'error';
        } else {
            $idproducto = $_POST['pr'];
    
            // Preparar la consulta UPDATE usando consulta preparada
            $query_delete = mysqli_prepare($conection, "UPDATE m_produc SET swt_prod = 0 WHERE id = ?");
            mysqli_stmt_bind_param($query_delete, "i", $idproducto);
            mysqli_stmt_execute($query_delete);
    
            // Verificar si se ejecutó correctamente
            if (mysqli_stmt_affected_rows($query_delete) > 0) {
                // Consulta SELECT para obtener des_prod
                $query_select = mysqli_prepare($conection, "SELECT des_prod FROM m_produc WHERE id = ?");
                mysqli_stmt_bind_param($query_select, "i", $idproducto);
                mysqli_stmt_execute($query_select);
                mysqli_stmt_bind_result($query_select, $des_prod);
                mysqli_stmt_fetch($query_select);
    
                // Preparar la respuesta JSON
                $response = array(
                    'status' => 'success',
                    'des_prod' => $des_prod
                );
    
                // Devolver resultado como JSON
                echo json_encode($response);
            } else {
                echo 'error'; // Puedes manejar el error según sea necesario
            }
    
            // Cerrar consultas
            mysqli_stmt_close($query_select);
            mysqli_stmt_close($query_delete);
            mysqli_close($conection);
        }
    }

    //-------------Buscar Usuario
    if ($_POST['action'] == 'searchuser') {
        if (!empty($_POST['user'])) {
            $busqueda = $_POST['user'];
            $sql = "SELECT * FROM m_usuari WHERE (id LIKE '%$busqueda%' OR des_usr LIKE '%$busqueda%' OR usr_email LIKE '%$busqueda%' OR cdg_usr LIKE '%$busqueda%' OR niv_usr LIKE '%$busqueda%')  AND swt_usr = 1";
        } else {
            $sql = "SELECT * FROM m_usuari WHERE swt_usr = 1";
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
            $data['error'] = 'No se encontraron usuarios.';
        }

        $total_paginas = ceil($num_rows / $registros);

        $data['total_paginas'] = $total_paginas;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //-------------Buscar Cliente
    if ($_POST['action'] == 'searchClient') {
        $swt_cli = (($_POST['est']??0)==1)?'':'AND swt_cli = 1';
        if (!empty($_POST['client'])) {
            $busqueda = $_POST['client'];
            $sql = "SELECT * FROM m_client WHERE (id LIKE '%$busqueda%' OR ruc_cli LIKE '%$busqueda%' OR des_cli LIKE '%$busqueda%' OR dir_cli LIKE '%$busqueda%' OR tel_cli LIKE '%$busqueda%')  $swt_cli";
        } else {
            $sql = "SELECT * FROM m_client WHERE 1 = 1 $swt_cli";
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
            $data['error'] = 'No se encontraron clientes.';
        }

        $total_paginas = ceil($num_rows / $registros);

        $data['total_paginas'] = $total_paginas;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }


    if ($_POST['action'] == 'searchProd') {
        $busqueda = !empty($_POST['prod']) ? $_POST['prod'] : '';
        $registros = intval($_POST['registros']);
        $pagina = intval($_POST['pagina']);
        $offset = ($pagina - 1) * $registros;
    
        $whereClause = "swt_prod = 1";
        if ($busqueda) {
            $whereClause .= " AND (p.id LIKE '%$busqueda%' OR p.cdg_prod LIKE '%$busqueda%' OR p.des_prod LIKE '%$busqueda%' OR p.cdg_eqv LIKE '%$busqueda%' OR p.cdg_bar LIKE '%$busqueda%' OR p.rut_imagen LIKE '%$busqueda%' OR p.ubic_a LIKE '%$busqueda%' OR p.ubic_b LIKE '%$busqueda%' OR p.ubic_c LIKE '%$busqueda%' OR p.ubic_d LIKE '%$busqueda%' OR p.ubic_e LIKE '%$busqueda%' OR p.ubic_f LIKE '%$busqueda%' OR marca.des_item LIKE '%$busqueda%')";
        }
    
        $sql = "SELECT SQL_CALC_FOUND_ROWS p.*, marca.DES_ITEM, med.des_item as MED 
                FROM m_produc p 
                LEFT JOIN d_tablas marca ON marca.id = p.id_dtab_marca 
                LEFT JOIN d_tablas med ON med.id = p.id_dtab_umed 
                WHERE $whereClause
                LIMIT $offset, $registros";
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
    
    

    //Buscar Cliente
    if($_POST['action']== 'searchcliente'){
        if(!empty($_POST['cliente'])){
            $ruc = $_POST['cliente'];

            $query = mysqli_query($conection,"SELECT c.*, tlist.id AS LIST
            FROM m_client c 
            LEFT JOIN d_tablas tcli ON c.id_dtab_tcli = tcli.id 
            LEFT JOIN d_tablas tlist ON tcli.id_dtab_1 = tlist.id
            WHERE c.ruc_cli LIKE '$ruc' and c.swt_cli = 1");

            mysqli_close($conection);
            $result = mysqli_num_rows($query);

            $data=($result>0)?mysqli_fetch_assoc($query):0;
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    //Agregar cliente - ventas
    if($_POST['action']== 'addCliente'){
        $ruc = $_POST['ruc_cliente'];
        $nombre = $_POST['nom_cliente'];
        $telefono = $_POST['tel_cliente'];
        $direccion = $_POST['dir_cliente'];

        $query_insert = mysqli_query($conection,"INSERT INTO m_client(ruc_cli,des_cli,tel_cli,dir_cli,id_musuari,	
id_dtab_tdoc) VALUES('$ruc','$nombre','$telefono','$direccion',$idUser,3721)");

        if($query_insert){
            $codCliente = mysqli_insert_id($conection);
            $msg = $codCliente;
        }else{
            $msg='error';
        }
        mysqli_close($conection);
        echo $msg; 
        exit;
    }



        // Helper function to check module status
    function checkModuleStatus($conection, $month, $year) {
        $query = "SELECT swt_opc FROM m_cierre WHERE mes_cie = '$month' AND ano_cie = '$year' AND id_mopcion = 2";
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

    // Procesar -Venta- || Pedido
    if ($_POST['action'] == 'procesarVenta' || $_POST['action'] == 'actualizarPedido') {
        $codcliente = empty($_POST['codcliente']) ? 1 : intval($_POST['codcliente']);
        $serie = $_POST['serie'];
        $ven = floatval($_POST['ven']);
        $mon = intval($_POST['mon']);
        $fec = $_POST['fec'];
        $ttot = floatval($_POST['ttot']);
        $month = date('m', strtotime($fec));
        $year = date('Y', strtotime($fec));
        $swt_cot = intval($_POST['swt_cot']);
        $ref = $_POST['ref']??'';
        $pto_venta = intval($_POST['pto_venta']);
        $term_ped = intval($_POST['term_ped']);
        
        $id_ped = isset($_POST['id_ped']) && $_POST['id_ped'] !== '' ? $_POST['id_ped'] : 0;
        // echo $id_ped;
        // exit();
        // Check module status
        $moduleStatus = checkModuleStatus($conection, $month, $year);
        if ($moduleStatus === 'no_record' || is_null($moduleStatus)) {
            echo json_encode(['success' => false, 'message' => 'Módulo sin aperturar']);
            mysqli_close($conection);
            exit;
        } elseif ($moduleStatus == 0) {
            echo json_encode(['success' => false, 'message' => 'El módulo se encuentra cerrado']);
            mysqli_close($conection);
            exit;
        }

        // Prepare statement to avoid SQL injection
        $stmt = $conection->prepare("SELECT * FROM detalle_temp WHERE token_user = ? AND id_mpedido = ?");
        $stmt->bind_param("si", $token, $id_ped);
        $stmt->execute();
        if($stmt->error) {
            echo json_encode(['success' => false, 'message' => 'Error en la ejecución del procedimiento: ' . $stmt->error]);
            exit;
        }
        $result = $stmt->get_result();
        // echo "$idUser, $codcliente, $token, $serie, $mon, $fec, $ttot, $ven, $current_date, $swt_cot, $ref, $pto_venta, $term_ped";
        // exit();
        if ($result->num_rows > 0) {
            if ($_POST['action'] == 'procesarVenta') {
                $stmt = $conection->prepare("CALL procesar_venta(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisiisdisisiii", $idUser, $codcliente, $token, $serie, $mon, $fec, $ttot, $ven, $current_date, $swt_cot, $ref, $pto_venta, $term_ped, $id_ped);
            } else {
                $stmt = $conection->prepare("CALL actualizar_pedido(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisiisidisisii", $idUser, $codcliente, $token, $serie, $mon, $fec, $id_ped, $ttot, $ven, $current_date, $swt_cot, $ref, $pto_venta, $term_ped);
            }

            $stmt->execute();
            if($stmt->error) {
                echo json_encode(['success' => false, 'message' => 'Error en la ejecución del procedimiento: ' . $stmt->error]);
                exit;
            }
            $result_detalle = $stmt->get_result();

            if ($result_detalle->num_rows > 0) {
                $data = $result_detalle->fetch_assoc();
                if($data['mensaje']??0){
                    echo json_encode(['success' => false, 'message' => $data['mensaje']]);
                }else{
                    echo json_encode(['success' => true, 'data' =>$data]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en el procedimiento almacenado']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Los detalles del pedido se encuentran vacios']);
        }
        $stmt->close();
        mysqli_close($conection);
        exit;
    }

    //Info factura
    if($_POST["action"] == 'infoFactura'){
        if(!empty($_POST['nofactura'])){
            $nofactura = $_POST['nofactura'];
            $query = mysqli_query($conection,"SELECT id,imp_ttot,fec_ped FROM m_pedido WHERE id = '$nofactura' AND swt_ped = 1");
            mysqli_close($conection);

            $result = mysqli_num_rows($query);
            if($result > 0){
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }
        }else{
            echo "error";
            exit;
        }
    }

    //Info Pedido
    if($_POST["action"] == 'infoPedido'){
        if(!empty($_POST['nopedido'])){
            $nopedido = $_POST['nopedido'];

            $query = mysqli_query($conection,"SELECT f.id,f.imp_ttot, c.des_cli,c.ruc_cli,c.id as idcliente FROM m_pedido f INNER JOIN m_client c ON f.id_mclient = c.id WHERE f.id = $nopedido AND f.swt_ped = 1");
            mysqli_close($conection);

            $result = mysqli_num_rows($query);
            if($result > 0){
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }else{
                echo "error";
            }
        }else{
            echo "error";
            exit;
        }
    }

    //Info Tramite
    if($_POST["action"] == 'infoTramite'){
        if(!empty($_POST['nopedido'])){
            $nopedido = $_POST['nopedido'];

            $query = mysqli_query($conection,"SELECT f.id,f.imp_ttot, c.des_cli,c.ruc_cli,c.id as idcliente FROM m_pedido f INNER JOIN m_client c ON f.id_mclient = c.id WHERE f.id = $nopedido AND f.swt_ped = 3");
            mysqli_close($conection);

            $result = mysqli_num_rows($query);
            if($result > 0){
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }else{
                echo "error";
            }
        }else{
            echo "error2";
            exit;
        }
    }



    //Anular Factura
    if($_POST['action'] == 'anularFactura'){
        if(!empty($_POST['noFactura'])){
            $noFactura = $_POST['noFactura'];
            $query_anular = mysqli_query($conection,"CALL anular_factura($noFactura)");
            mysqli_close($conection);
            $result = mysqli_num_rows($query_anular);
            if($result > 0){
                $data = mysqli_fetch_assoc($query_anular);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }
        }else{
            echo "error";
            exit;
        }
    }

    //Anular Guia
    if($_POST['action'] == 'anulartramite'){
        if(!empty($_POST['noFactura'])){
            $noFactura = $_POST['noFactura'];
            $query_anular = mysqli_query($conection,"CALL anular_guia($noFactura,$idUser)");
            mysqli_close($conection);
            $result = mysqli_num_rows($query_anular);
            if($result > 0){
                $data = mysqli_fetch_assoc($query_anular);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }
        }else{
            echo "error";
            exit;
        }
    }

    //Tramitar pedido
    if($_POST['action'] == 'tramitarPedido'){
        if(!empty($_POST['noFactura']) && !empty($_POST['cliente'])){
            $noFactura = $_POST['noFactura'];
            $cliente = $_POST['cliente'];
            $query_tramitar = mysqli_query($conection,"CALL tramitar_pedido($noFactura,$cliente,$idUser)");
            mysqli_close($conection);
            $result = mysqli_num_rows($query_tramitar);
            if($result > 0){
                $data = mysqli_fetch_assoc($query_tramitar);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }
        }else{
            echo "error";
            exit;
        }
    }


    //Cambiar contraseña
    if($_POST['action'] == 'changePassword'){
        if(!empty($_POST['passActual']) && !empty($_POST['passNuevo'])){
            $password = md5(($_POST['passActual']));
            $newPass = md5(($_POST['passNuevo']));

            $code = '';
            $msg = '';
            $arrData = [];

            $query_user = mysqli_query($conection,"SELECT * FROM m_usuari WHERE psw_usr = '$password' AND id = '$idUser'");
            $result = mysqli_num_rows($query_user);
            if($result > 0){
                $query_update = mysqli_query($conection,"UPDATE m_usuari SET psw_usr = '$newPass' WHERE id = $idUser");
                mysqli_close($conection);

                if($query_update){
                    $code = '00';
                    $msg = "Su contraseña se ha actualizado con éxito.";
                }else{
                    $code = '2';
                    $msg = "No es posible cambiar su contraseña.";
                }
            }else{
                $code = '1';
                $msg = "La contraseña actual es incorrecta.";
            }
            $arrData =['cod'=>$code, 'msg' =>$msg];
            echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
        }
        else{
            echo "error";
        }
        exit;
    }
    //Actualizar datos empresa
    if($_POST['action'] == 'updateDataEmpresa'){

        if(empty($_POST['txtRuc']) ||empty($_POST['txtNombre']) ||empty($_POST['txtTelEmpresa']) ||empty($_POST['txtEmailEmpresa']) ||empty($_POST['txtDirEmpresa']) ||empty($_POST['txtIgv'])){
            $code = '1';
            $msg = 'Todos los campos son obligatorios';
        }else{
            $intRuc = intval($_POST['txtRuc']);
            $strNombre = $_POST['txtNombre' ];
            $strRSocial = $_POST['txtRSocial' ];
            $strTel = $_POST['txtTelEmpresa'];
            $strEmail = $_POST['txtEmailEmpresa' ];
            $strDir = $_POST['txtDirEmpresa' ];
            $strIgv = $_POST['txtIgv' ];

            $queryUpd = mysqli_query($conection,"UPDATE configuracion SET ruc = $intRuc,nombre='$strNombre',razon_social='$strRSocial',telefono='$strTel',email='$strEmail',direccion='$strDir',igv='$strIgv' WHERE id=1");

            mysqli_close($conection);
            if($queryUpd){
                $code = '00';
                $msg = 'Datos actualizados correctamente.';
            }else{
                $code = '2';
                $msg = 'Error al actualizar los datos';
            }
        }
        $arrData = ['cod'=> $code,'msg'=> $msg];
        echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
        exit    ;
    }
    
    if ($_POST['action'] == 'api_consulta') {
        // Validate and sanitize input
        $ruc = isset($_POST['ruc']) ? htmlspecialchars($_POST['ruc']) : '';
        if (empty($ruc)) {
            echo json_encode(['error' => 'RUC is required.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        $url = "https://api.apis.net.pe/v1/ruc?numero=" . urlencode($ruc);
    
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Execute cURL request
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            // Handle cURL error
            echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)], JSON_UNESCAPED_UNICODE);
            curl_close($ch);
            exit;
        }
    
        // Close cURL session
        curl_close($ch);
    
        // Decode JSON response
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Handle JSON decoding error
            echo json_encode(['error' => 'Failed to decode JSON response.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // Validate API response
        if (!isset($data['ubigeo'])) {
            echo json_encode(['error' => 'Invalid API response.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // Perform database query using prepared statements
        $ubigeo = $data['ubigeo'];
        $stmt = $conection->prepare('SELECT COALESCE(MAX(id),0) as id_ubi FROM m_ubigeo WHERE ubigeo = ?');
        $stmt->bind_param('s', $ubigeo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $data['id_ubi'] = $row['id_ubi'];
        $stmt->close();
    
        // Output JSON response
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    

    // Verifica si la acción es para actualizar la última actividad del usuario
    if ($_POST['action'] == 'actualizarActividad') {

        // Verifica si el usuario está conectado
        if (isset($idUser)) {
            require_once "../conexion.php";

            // Actualiza la última actividad del usuario
            $query_update = mysqli_query($conection, "UPDATE m_usuari SET last_activity = '$current_date' WHERE id = $idUser");

            // Cierra la conexión
            mysqli_close($conection);

            // Verifica si la actualización fue exitosa
            if ($query_update) {
                echo 'ok'; // Informa que la actualización fue exitosa
            } else {
                echo 'error'; // Informa que ocurrió un error durante la actualización
            }
        } else {
            echo 'error'; // Informa que el usuario no está conectado
        }
    }


    // Agregar datos a m_tablas
    if ($_POST['action'] == 'addMTable') {
        // Sanitizar y validar los datos del formulario
        $cdg = isset($_POST['cdg']) ? trim($_POST['cdg']) : '';
        $des = isset($_POST['des']) ? trim($_POST['des']) : '';
        $m = array();

        // Agregar datos a m_tablas
if ($_POST['action'] == 'addMTable') {
    // Sanitizar y validar los datos del formulario
    $cdg = isset($_POST['cdg']) ? trim($_POST['cdg']) : '';
    $des = isset($_POST['des']) ? trim($_POST['des']) : '';
    $m = array();

    if (!empty($cdg) && !empty($des)) {
        // Verificar si cdg_tab ya existe
        $stmt_check = $conection->prepare("SELECT id FROM m_tablas WHERE cdg_tab = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("s", $cdg);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                // cdg_tab ya existe
                $m['status'] = 'error';
                $m['message'] = 'Error: El código ya existe.';
            } else {
                // cdg_tab no existe, proceder con la inserción
                $stmt = $conection->prepare("INSERT INTO m_tablas (cdg_tab, des_tab) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ss", $cdg, $des);

                    if ($stmt->execute()) {
                        $m['status'] = 'success';
                        $m['message'] = 'Agregado con éxito';
                    } else {
                        $m['status'] = 'error';
                        $m['message'] = "Error al ejecutar la consulta: $stmt->error";
                    }

                    // Cerrar la declaración
                    $stmt->close();
                } else {
                    $m['status'] = 'error';
                    $m['message'] = "Error al preparar la consulta: $conection->error";
                }
            }

            // Cerrar la declaración de verificación
            $stmt_check->close();
        } else {
            $m['status'] = 'error';
            $m['message'] = "Error al preparar la consulta de verificación: $conection->error";
        }
    } else {
        $m['status'] = 'error';
        $m['message'] = 'Error: Los campos "cdg" y "des" no pueden estar vacíos';
    }

    echo json_encode($m, JSON_UNESCAPED_UNICODE);
    exit;
}


        echo json_encode($m, JSON_UNESCAPED_UNICODE);
        exit;
    }


    

    // Borrar datos de m_tablas
    if ($_POST['action'] == 'delMTablas') {
        if (!empty($_POST['idt'])) {
            // Obtener el ID de la tabla a eliminar y sanearlo
            $idt = $_POST['idt'];

            // Preparar la consulta de eliminación
            $stmt = $conection->prepare("DELETE FROM m_tablas WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $idt);

                try {
                    // Ejecutar la consulta
                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Eliminado con éxito'
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    // Manejar errores específicos de restricción de clave externa
                    if ($conection->errno === 1451) {
                        $response = [
                            'success' => false,
                            'message' => 'Error: No se puede eliminar la tabla porque tiene referencias en otras tablas.'
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Error al eliminar: ' . $e->getMessage()
                        ];
                    }
                }

                // Cerrar la declaración
                $stmt->close();
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Error al preparar la consulta: ' . $conection->error
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Error: No se proporcionó el ID de la tabla'
            ];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
 


    //Buscar num max d_tablas
    if($_POST['action'] == 'maxNum'){
        $id_m = $_POST['id_m']??1;
        $query_co = mysqli_query($conection,"SELECT MAX(num_item) as num_item FROM d_tablas WHERE id_mtablas = $id_m ");
        $resultado = mysqli_fetch_assoc($query_co);
        // Verificar si el resultado está vacío
        if ($resultado === null) {
            // Si está vacío, establecer el valor inicial en "001"
            $maximo= "001";
        } else {
            // Si no está vacío, obtener el valor máximo actual y sumarle 1
            $max_correlativo = $resultado['num_item'];
            $maximo= str_pad(intval($max_correlativo) + 1, strlen($max_correlativo), "0", STR_PAD_LEFT);
        }
        echo json_encode($maximo, JSON_UNESCAPED_UNICODE);
    }

    //Buscar num max d_tablas
    if ($_POST['action'] == 'modalDtablaPrincipal') {
        $id = $_POST['id'] ?? 0;
        $stmt = $conection->prepare("SELECT ref1, ref2, ref3, ref4, ref5, sql1, sql2, sql3, cdg_sunat, des_sunat FROM d_tablas WHERE id = ?");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        $stmt->close();
    }
    
    // Editar m_tablas
    if ($_POST['action'] == 'editMTable') {
        // Obtener y sanitizar los datos del formulario
        $idt = isset($_POST['idt']) ? trim($_POST['idt']) : '';
        $cdg = isset($_POST['cdg']) ? trim($_POST['cdg']) : '';
        $des = isset($_POST['des']) ? trim($_POST['des']) : '';

        // Verificar que los campos no estén vacíos
        if (!empty($cdg) && !empty($des) && !empty($idt)) {
            // Verificar si cdg_tab ya existe, excluyendo el registro actual
            $stmt_check = $conection->prepare("SELECT id FROM m_tablas WHERE cdg_tab = ? AND id != ?");
            if ($stmt_check) {
                $stmt_check->bind_param("si", $cdg, $idt);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    // cdg_tab ya existe
                    $rpt = "Error: El código ya existe.";
                } else {
                    // cdg_tab no existe, proceder con la actualización
                    $stmt = $conection->prepare("UPDATE m_tablas SET CDG_TAB = ?, DES_TAB = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("ssi", $cdg, $des, $idt);

                        // Ejecutar la consulta
                        $rpt =$stmt->execute()?"Editado con éxito":"Error al editar: $stmt->error";
                        // Cerrar la declaración
                        $stmt->close();
                    } else {
                        $rpt = "Error al preparar la consulta: $conection->error";
                    }
                }

                // Cerrar la declaración de verificación
                $stmt_check->close();
            } else {
                $rpt = "Error al preparar la consulta de verificación: $conection->error";
            }
        } else {
            $rpt = "Error: Los campos no pueden estar vacíos";
        }

        echo json_encode($rpt, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //Buscar listas de precio
    if ($_POST['action'] == 'obtenerListasPrecio') {
        $idDTable = $_POST['idDTable']??0;
        $selected = '';
        $query_default = mysqli_query($conection,"SELECT id_dtab_1 FROM d_tablas WHERE id = $idDTable");
        $result = mysqli_fetch_array($query_default);
        $default = $result['id_dtab_1'];
        
        $query_list = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas=53 ORDER BY num_item ASC");
        if ($query_list) {
            $data = '<label for="dtab1">Lista Prec.</label>
                    <select class="select_pedido in_dtab1" name="dtab1" id="dtab1">';
            while ($m = mysqli_fetch_array($query_list)) {
                $selected = ($m['id'] == $default)? 'selected':'';
                $data .= '<option value="' . htmlspecialchars($m['id'], ENT_QUOTES, 'UTF-8') . '" '.$selected.'>' . htmlspecialchars($m['des_item'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
            $data .= '</select>';
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['error' => 'Consulta fallida']);
        }

        mysqli_close($conection);
    }

    //Traer Checks de almacenes
    if ($_POST['action'] == 'obtenerChecksAlmacen') {
        $idDTable = $_POST['idDTable']??0;
        $data = '<fieldset class="fieldset_modal">';
        if($idDTable){
            $query_default = mysqli_query($conection,"SELECT COALESCE(swt_ref1,0) AS ref1,
                                                                        COALESCE(swt_ref2,0) AS ref2,
                                                                        COALESCE(swt_ref3,0) AS ref3,
                                                                        COALESCE(swt_ref4,0) AS ref4,
                                                                        COALESCE(swt_ref5,0) AS ref5
                                                                    FROM d_tablas WHERE id = $idDTable");
            $result = mysqli_fetch_assoc($query_default);
        }
        $ref1 = ($result['ref1']??0==1)?'checked':'';
        $ref2 = ($result['ref2']??0==1)?'checked':'';
        $ref3 = ($result['ref3']??0==1)?'checked':'';
        $ref4 = ($result['ref4']??0==1)?'checked':'';
        $ref5 = ($result['ref5']??0==1)?'checked':'';

        $data .= "<div class='group_modal'>
                    <input type='checkbox' name='check_ref1' id='check_ref1' $ref1>
                    <label for='check_ref1'>Almacén de producción</label>
                </div>
                <div class='group_modal'>
                    <input type='checkbox' name='check_ref2' id='check_ref2' $ref2>
                    <label for='check_ref2'>Valoriza</label>
                </div>
                <div class='group_modal'>
                    <input type='checkbox' name='check_ref3' id='check_ref3' $ref3>
                    <label for='check_ref3'>Consignación</label>
                </div>
                <div class='group_modal'>
                    <input type='checkbox' name='check_ref4' id='check_ref4' $ref4>
                    <label for='check_ref4'>Considera Stock en Pedido</label>
                </div>
                <div class='group_modal'>
                    <input type='checkbox' name='check_ref5' id='check_ref5' $ref5>
                    <label for='check_ref5'>Ver stock en Búsqueda de Producto</label>
                </div>";

        $data .= '</fieldset>';
        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        mysqli_close($conection);
    }

    if ($_POST['action'] == 'addDTable' || $_POST['action'] == 'editDTable') {
        try {
            $idm = isset($_POST['idm']) ? trim($_POST['idm']) : '';
            $cdg = isset($_POST['cdg']) ? trim($_POST['cdg']) : '';
            $des = isset($_POST['des']) ? trim($_POST['des']) : '';
            $abr = isset($_POST['abr']) ? trim($_POST['abr']) : '';
            $stt = isset($_POST['stt']) ? trim($_POST['stt']) : 1;
            $iddt = isset($_POST['iddt']) ? trim($_POST['iddt']) : null;
            $dtab1 = isset($_POST['dtab1']) ? trim($_POST['dtab1']) : 0;
            
            $valor = $_POST['valor'] ?? 0.00;
            $tipo = $_POST['tipo'] ?? '';
            $cdg_sunat = $_POST['cdg_sunat'] ?? '';
            $des_sunat = $_POST['des_sunat'] ?? '';
    
            $swt_ref1 = $_POST['swt_ref1']??0;
            $swt_ref2 = $_POST['swt_ref2']??0;
            $swt_ref3 = $_POST['swt_ref3']??0;
            $swt_ref4 = $_POST['swt_ref4']??0;
            $swt_ref5 = $_POST['swt_ref5']??0;
            
            $ref1 = $_POST['ref1']??'';
            $ref2 = $_POST['ref2']??'';
            $ref3 = $_POST['ref3']??'';
            $ref4 = $_POST['ref4']??'';
            $ref5 = $_POST['ref5']??'';
            $ref6 = $_POST['ref6']??'';
            $ref7 = $_POST['ref7']??'';
            $ref8 = $_POST['ref8']??'';

            if (!empty($cdg) && !empty($des)) {
                if ($_POST['action'] == 'addDTable') {
                    // Consulta para agregar nuevo registro
                    $stmt = $conection->prepare("INSERT INTO d_tablas (id_mtablas, num_item, des_item, abr_item, swt_item, id_dtab_1,
                    val_item, tip_item, cdg_sunat, des_sunat,
                    swt_ref1, swt_ref2, swt_ref3, swt_ref4, swt_ref5, 
                    ref1, ref2, ref3, ref4, ref5, sql1, sql2, sql3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssiidsssiiiiissssssss", $idm, $cdg, $des, $abr, $stt, $dtab1,$valor,$tipo,$cdg_sunat,$des_sunat, $swt_ref1, $swt_ref2, $swt_ref3, $swt_ref4, $swt_ref5, $ref1, $ref2, $ref3, $ref4, $ref5, $ref6, $ref7, $ref8);
                } else {
                    // Consulta para actualizar el registro existente
                    $stmt = $conection->prepare("UPDATE d_tablas SET num_item = ?, des_item = ?, abr_item = ?, swt_item = ?, id_dtab_1 = ?, 
                    val_item = ?, tip_item = ?, cdg_sunat = ?, des_sunat = ?,
                    swt_ref1 = ? , swt_ref2 = ? , swt_ref3 = ? , swt_ref4 = ? , swt_ref5 = ?, 
                    ref1 = ? , ref2 = ? , ref3 = ? , ref4 = ? , ref5 = ?, sql1 = ? , sql2 = ? , sql3 = ?  WHERE id = ?");
                    $stmt->bind_param("sssiidsssiiiiissssssssi", $cdg, $des, $abr, $stt, $dtab1,$valor,$tipo,$cdg_sunat,$des_sunat, $swt_ref1, $swt_ref2, $swt_ref3, $swt_ref4, $swt_ref5, $ref1, $ref2, $ref3, $ref4, $ref5, $ref6, $ref7, $ref8, $iddt);
                }
    
                if ($stmt->execute()) {
                    $response = ['success' => true];
                } else {
                    throw new Exception("Error en la consulta: $stmt->error");
                }
                $stmt->close();
            } else {
                throw new Exception('Error: Los campos no pueden estar vacíos.');
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($_POST['action'] == 'delDTablas') {
        try {
            if (!empty($_POST['iddt'])) {
                $stmt = $conection->prepare("DELETE FROM d_tablas WHERE id = ?");
                $stmt->bind_param("i", $_POST['iddt']);
                if ($stmt->execute()) {
                    $response = ['success' => true];
                } else {
                    throw new Exception('Error al eliminar: ' . $stmt->error);
                }
                $stmt->close();
            } else {
                throw new Exception('Error: No se proporcionó el ID de la tabla.');
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
exit;