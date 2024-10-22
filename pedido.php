<?php 
    include "includes/updatePermissions.php";
    $id_pagina=39;
    if (!isset($_SESSION[$_SESSION['db'].'perms'][$id_pagina])) {
    header('Location: ./');
    exit;
}
$modoLectura = $_SESSION[$_SESSION['db'].'perms'][$id_pagina];
    include "../conexion.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "includes/scripts.php";?>
    <title>Nuevo Pedido</title>
</head>
<body>
<?php 
    include "includes/header.php";
    
    date_default_timezone_set('America/Lima');
    $current_date = date('Y-m-d');
    ?>
    <section class="container container_pedido" id="container"> 
        <div class="row-three-columns">
            <div class="section1">
                <h1 class="pedido">REGISTRO DE PEDIDOS
                    <div class="section2">
                        <div class="nested-grid">
                            <div class="nested1">
                                <button id="consultar_primero" class="btn material-symbols-outlined btn_page">First_Page</button>    
                            </div>
                            <div class="nested3">
                                <button id="disminuir" class="btn material-symbols-outlined btn_page">Keyboard_Arrow_Left</button>
                            </div>
                            <div class="nested4">                            
                                <button id="aumentar" class="btn material-symbols-outlined btn_page">Keyboard_Arrow_Right</button>
                            </div>
                            <div class="nested2">
                                <button id="consultar_ultimo" class="btn material-symbols-outlined btn_page">Last_Page</button>
                            </div>
                        </div>    
                    </div>
                </h1>
            </div>
        </div>
        
        <div class="informacion_pedido">
            <div class="pedido_subtitulo"><h3>Información Primaria</h3>
        </div>
        <div class="info_primaria">
            <div class="width_40">
                <div class="form_div"> 
                    <label for="serie"><span>Número:</span></label>
                    <select name="serie" id="serie">
                        <!-- JavaScript -->
                    </select>
                    
                    <input type="number" name="num_ped" class="input_document" oninput="this.value = this.value.replace(/[^0-9]/g, '');" id="num_ped" min="0">
                </div>
                <div class="form_div">
                    <label for="fec_ped">Fecha:</label>
                    <input type="date" name="fec_ped" class="exchange_date input_document" id="fec_ped" value="<?php echo $current_date; ?>">
                </div>
                
                <form action="" id="form_new_cliente_venta" class="form_new_cliente_venta" >
                    <input type="hidden" id="edit_active" value="0">    
                    <input type="hidden" name="action" value="addCliente">
                    <input type="hidden" id="idcliente" name="idcliente" value="" required>
                    <input type="hidden" name="id_pedido_update" id="id_pedido_update">
                    
                    <div class="form_div">
                        <label for="ruc_cliente">R.U.C.</label>
                        <input type="text" name="ruc_cliente" id="ruc_cliente" class="input_document ruc_client<?=$_SESSION[$_SESSION['db'].'perms'][38]?'':'e';?>" required data-swt_modal="active">
                        <a id="modal_buscar_clientes" onclick="openModalClient(<?=$_SESSION[$_SESSION['db'].'perms'][38]?>);" href="#" class="btn btn_ruc modal_buscar_clientes material-symbols-outlined" title="Buscar">Search</a>    
                        
                    </div>
                    <span class="foundClient"></span>
                    <div class="form_div">
                        <label for="nom_cliente">Nombre:</label>
                        <input type="text" name="nom_cliente" class="input_document" id="nom_cliente" disabled required>
                    </div>    
                    <!-- Mostrar moneda -->
                    <div class="form_div"> 
                        <label for="moneda">Moneda:</label>
                        <?php
                            include "../conexion.php";
                            $query_mon = mysqli_query($conection,"SELECT id, des_item FROM d_tablas WHERE id_mtablas=10 ");
                                mysqli_close($conection);
                                $results = mysqli_num_rows($query_mon);
                                
                                ?>
                            <select class="select_pedido" name="moneda" id="moneda" onchange="modifyCoin(0,this)">
                                <?php   
                                if($results>0){
                                    while ($m = mysqli_fetch_array($query_mon)){
                                        ?>
                                <option value="<?=$m['id']?>">
                                    <?=$m['des_item']?>
                                </option>
                                <?php   
                                }}
                                ?>
                            </select>
                        </div> 
                        
                        <!-- Mostrar listas -->
                        <div class="form_div">
                            <label for="lista">Listas:</label>
                            <?php   

include "../conexion.php";
$query_mon = mysqli_query($conection,"SELECT id, des_item FROM d_tablas WHERE id_mtablas=53 AND id <> 1051 ORDER BY num_item ASC");
mysqli_close($conection);
$results = mysqli_num_rows($query_mon);

?>
                            <select class="select_pedido" name="lista" id="lista">
                                <?php   
                                if($results>0){
                                    while ($m = mysqli_fetch_array($query_mon)){
                                        ?>
                                <option value="<?php echo $m['id']; ?>">
                                    <?php echo $m['des_item']; ?>
                                </option>
                                <?php   
                                }}
                                // Establecer la zona horaria a "America/Lima"
                                date_default_timezone_set('America/Lima');
                                
                                // Obtener la fecha actual
                                $current_date = date('Y-m-d');
                                ?>
                            </select>
                        </div>
                        
                    </form>
                    
                </div>
                <div class="containerNumPedido">
                    <strong class="titulo_flotante">PEDIDO</strong>
                    <strong><span class="span_serie">F001</span>-<span class="span_numero">4342</span></strong>
                    <div class="cotizacion_container">
                        <input type="checkbox" name="checklista" id="modo_cot" class="modo-lista-checkbox" >
                        <label for="checklista">Cotización</label>
                    </div>
                    <div class="cotizacion_container">
                        <input type="checkbox" name="checkIgv" id="checkIgv" onchange="incluidoIgv();" class="modo-lista-checkbox" >
                        <label for="checkIgv">Incluído IGV</label>
                    </div>
                    <div class="group_checks estado_ped"></div>
                </div>
            </div>
            
            <div class="pedido_subtitulo">
                <h3>Información Adicional
                    <a href="#" class="btn-detalles material-symbols-outlined" title="Más detalles">Arrow_Drop_Down</a></h3>
                </div>
                <div class="container111 section4">   
                    <div class="form_div">
                        <label for="cond_pago">Cond. Pago:</label>
                        <?php
                        include "../conexion.php";
                        $query = mysqli_query($conection, "SELECT id, des_item FROM d_tablas WHERE id_mtablas=7 ORDER BY id ASC    ");
                        
                        $options = '';
                        while ($cond_pago = mysqli_fetch_array($query)) {
                            $options .= '<option value="' . $cond_pago['id'] . '">' . $cond_pago['des_item'] . '</option>';
                        }
                        ?>
                        <select class="select_pedido" name="cond_pago" id="cond_pago">
                            <?php echo $options; ?>
                        </select>
                    </div>
                    <!-- Mostrar vendedores -->
                    <div class="form_div">
                        <label for="vendedor">Vendedor:</label>
                        <?php   
                            include "../conexion.php";
                            $user = $_SESSION[$_SESSION['db'].'idUser'];
                            $query_user = mysqli_query($conection,"SELECT id, des_item FROM d_tablas WHERE id_mtablas=93 ORDER BY des_item ASC");
                            mysqli_close($conection);
                            $results = mysqli_num_rows($query_user);
                            ?>
                            <input type="hidden" id="idVendedor">
                            <select class="select_pedido" name="vendedor" id="vendedor">
                                <option value="0">Seleccionar vendedor:</option>
                                <?php   
                                if($results>0){
                                    while ($users = mysqli_fetch_array($query_user)){
                                        ?>
                                        <option value="<?php echo $users['id']; ?>">
                                            <?php echo $users['des_item']; ?>
                                        </option>
                                        <?php   
                                }}
                                ?>
                            </select>
                        </div>
                        
                        
                        <div class="form_div">
                            <label for="dir_cliente">Doc. Refer:</label>
                            <input type="text" name="ref_cliente" id="ref_cliente" class="input_document" >
                        </div>
                        <div class="form_div">
                            <label for="tel_cliente">Telefono:</label>
                            <input type="text" name="tel_cliente" id="tel_cliente" class="input_document" disabled required>
                        </div>
                        <div class="form_div">
                            <label for="dir_cliente">Dirección:</label>
                            <input type="text" name="dir_cliente" id="dir_cliente" class="input_document" disabled required>
                        </div>
                        <fieldset>
                        <div class="fieldset_doc">
                            <input type="radio" name="codigo" id="check_cod_prod" checked>
                            <label for="check_cod_prod">Código de Producto</label>
                        </div>
                        <div class="fieldset_doc">
                            <input type="radio" name="codigo" id="check_cod_eqv" >
                            <label for="check_cod_eqv">Código Equivalente</label>
                        </div>
                    </fieldset>
                    </div>
                    <!-- //Para el espaciado, que queden al inicio -->
                    <div></div>
                    <div></div>
                    
                </div>
                <div class="alert"></div>
                <!--Búsqueda dinamica-->
                <div class="busqueda_productos">
                    <div class="div_modo_lista">
                        <?php //Modo lista
                            include "../conexion.php";
                            $query = mysqli_query($conection, "SELECT mod_lst from t_tablas");
                            $result = mysqli_fetch_assoc($query);
                            if($result['mod_lst'] == 0){
                                $check = '';
                            }else if($result['mod_lst'] == 1){
                                $check = 'checked';
                            }
                        ?>
                        <input type="checkbox" name="checklista" id="checklista" value="Modo lista" class="modo-lista-checkbox" <?=$check?>>
                        <label for="checklista">MODO LISTA</label>
                    </div>
                    
                    
                    <input class="busqueda_dinamica_input"  type="text" name="busqueda_dinamica" id="busqueda_dinamica" value="" placeholder="Agregar productos"  required>
                    <div class="overflow">
                        <div class="width_1k">
                            <div class="product-list" id="product-list">
                            </div>
                        </div>
                    </div>
                    <div class="paginador"></div>
                    
                </div>
    <div class="table-responsive">
        <table class="tbl_venta">
            <thead>
                
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Marca</th>
                    <th>Cant.</th>
                    <th class="venta-text">Precio <span class="simbolo_mon">S/.</span></th>
                    <th class="venta-text">%Desc</th>
                    <th class="venta-text">Total</th>
                    <th >Acción</th>
                </tr>
            </thead>
            <tbody id="detalle_venta">
                <!-- contenido ajax -->            
            </tbody>
            
        </table>
    </div>
    <div id="detalle_totales">
        <!-- contenido ajax -->
    </div>
    <div class="section_bottom">
        <div class="pedido_subtitulo"><h3>Opciones adicionales</h3></div>
        <div class="section_bottom_container">
            <div class="group_checks">
                <div class="group_check">
                    <input type="checkbox" name="checkFacturarVenta" id="checkFacturarVenta" class="checkFacturarVenta modo-lista-checkbox"><label for="checkFacturarVenta">Facturar en punto de venta.</label>
                </div>
                <div class="group_check">
                    <input type="checkbox" name="checkTerminarPedido" id="checkTerminarPedido" class="checkTerminarPedido modo-lista-checkbox"><label for="checkTerminarPedido">Dar por terminado el pedido</label>
                </div>
            </div>
            
                <div class="section_bottom-right"></div>
            </div>
            
            <?php if($modoLectura==0){ ?>
            <div class="datos_venta finales">
                <div class="opciones_finales">
                    <div class="acciones_venta">
                        <a href="factura/generaFactura.php" id="btn_actualizar_pedido" class="btn displaynone guardar-actualizar-pedido" title="Guardar pedido">
                            <span class="material-symbols-outlined">Save</span>
                        </a>
                        <div onclick="anular_documento(0)" class="btn btn_new" title="Cancelar pedido"><span class="material-symbols-outlined" data-id_user="<?=$_SESSION[$_SESSION['db'].'idUser'];?>">Block</span></div>
                    </div>
                </div>
            </div>
            <div class="acciones_venta">
                <a href="factura/generaFactura.php" id="btn_guardar_pedido" class="btn displaynone guardar-actualizar-pedido" title="Duplicar Pedido">
                    <span class="material-symbols-outlined">File_Copy</span>
                </a>
                <a href="#" id="btn_view_pdf" class="btn btn_view_pdf" onclick="generarPDF($('#id_pedido_update').val())" title="Ver PDF">
                    <span class="material-symbols-outlined">Picture_As_Pdf</span>
                </a>
            </div>
            <?php } ?>
        </div>
    </section>
<script>
    //Mostrar notificacion si se grabó un pedido nuevo
    window.onload = function() {
        var ped_save = localStorage.getItem('ped_save');
        if (ped_save == 'true') {
            createToast('success', 'fa-solid fa-circle-check', 'Pedido Guardado', 'Se grabó el pedido con éxito!');
            localStorage.setItem('ped_save', false)
        }
    }
    
    $(document).ready(function() {
        cambiarModoLista();
        var serieDefault = null;
        
        //Consultar antes de borrar detalle temp
        var edicion = false;
        $('#serie,#moneda, #fec_ped,#ruc_cliente,#vendedor,#lista,#cond_pago,#ref_cliente').on('change',function(){
            edicion = true;
            $('#edit_active').val('1');
            cambioPedidoGlobal()
            console.log('glob');
        });

        // Función para cargar las opciones de SERIE
        $.ajax({
            url: '../api/config.php', 
            type: 'POST',
            dataType: 'json',
            data:{action:'consultaDocSeries',tdoc:3982},
            success: function(response) {
                // Llenar el select con las opciones obtenidas
                $.each(response.series, function(index, serie) {
                    $('#serie').append('<option value="' + serie.id + '">' + serie.des_item + '</option>');
                });
                
                // Establecer el valor inicial del número de serie
                $('#num_ped').val(response.seriePorDefecto);
                // $('#num_ped').attr('max', response.seriePorDefecto);
                serieDefault = response.seriePorDefecto;
                $('.span_numero').html(serieDefault);
                $('.span_serie').html($('#serie').find('option').first().text());
                
                realizarAccion();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
        // Función para actualizar el número de serie al cambiar la opción del select
        $('#serie').change(function() {
            var serieId = $(this).val();
            var serieText = $('#serie option:selected').text();
            $.ajax({
                url: '../api/config.php', 
                type: 'POST',
                data: {action:'consultaDocSeries',tdoc:3982, serieId: serieId},
                success: function(response) {console.log(response);
                    var info = JSON.parse(response);
                    //Almacenando la serie seleccionada
                    serieDefault = info.seriePorDefecto;
                    $('#num_ped').val(info.seriePorDefecto);
                    $('.span_numero').html(info.seriePorDefecto);
                    $('.span_serie').html(serieText);
                    $('#num_ped').trigger('click');
                    
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
        
        $('#num_ped').on('change', function() {
            $('.span_numero').html($(this).val());
            var numSerie = $(this).val();
            var originalValue = $(this).data('original-value');
            
            if (edicion == true) {
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger"
                    },
                });
                
                swalWithBootstrapButtons.fire({
                    title: "¿Estás seguro de cambiar a otro pedido?",
                    text: "Esto eliminará tus productos agregados y cambios realizados.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, cambiarlo",
                    cancelButtonText: "Cancelar",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        edicion = false;
                        realizarAccion();
                        createToast('info', 'fa-solid fa-circle-info', 'Info', 'Estas en el modo edicion de pedido.');
                    } else {
                        $('#num_ped').val(originalValue);
                        $('.span_numero').html(originalValue);
                        edicion = true;
                        return;
                    }
                });
            } else {
                realizarAccion();
            }
            
            
        });
        
        window.searchInput = $('#busqueda_dinamica');
        window.productList = $('.product-list');
        
        searchInput.on('focus', toggleProductList);
        searchInput.off('blur').on('blur', function() {
            setTimeout(toggleProductList, 200);
        });
        //buscador de productos
        busquedaDinamica(0);
        
        //=========Botones de navegacion=====
        $('#disminuir').on('click', function() {
            var num_ped = $('#num_ped');
            var valor_actual = parseInt(num_ped.val());
            if (valor_actual > 0) {
                num_ped.val(valor_actual - 1);
                $('#num_ped').trigger('change');
            }
        });
        $('#aumentar').on('click', function() {
            var num_ped = $('#num_ped');
            var valor_actual = parseInt(num_ped.val());
            num_ped.val(valor_actual + 1);
            $('#num_ped').trigger('change');
        });
        $('#consultar_ultimo').on('click', function() {
            const srr = $('#serie').val();
            $.ajax({
                url: '../api/config.php',
                type: 'POST',
                async: true,
                data: {action: 'getNewDoc', val : 0, srr : srr},
                success: function(response) {
                    console.log(response);
                    let info = JSON.parse(response);
                    if(info.status=='success'){
                        $('#num_ped').val(info.num);
                        $('#num_ped').trigger('change');
                    }
                },
                error: function(error) {
                    console.error(error);
                }
            });
        });
        $('#consultar_primero').on('click', function() {
            $('#num_ped').val(1);
            $('#num_ped').trigger('change');
        });
        
        $('#modo_cot').on('change',function(){
            if($(this).prop('checked')){
                $('.titulo_flotante').html('COTIZACION');
            }else{
                $('.titulo_flotante').html('PEDIDO');
            }
            updateTableDetail(1)
        });

        
        $('#checklista').change(cambiarModoLista);
        if ($('#ruc_cliente').val().trim() === '') {
            $('#ruc_cliente').focus();
        }
        
    });

function cambioPedidoGlobal(){
    // console.log('cambioPedidoGlobal');
    var srr = $('#serie').val();
    var num = $('#num_ped').val();
    var fec = $('#fec_ped').val();
    var cli = $('#idcliente').val();
    var mon = $('#moneda').val();
    var cpg = $('#cond_pago').val();
    var ven = $('#vendedor').val();
    var list = $('#lista').val();
    var ref = $('#ref_cliente').val();
    var swt_cot = $('#modo_cot').is(':checked') ? 1 : 0;
    //Id m pedido para solo modificar aquellos relacionados
    var idMped = (value = $('#id_pedido_update').val()) == null || value === '' || value === '0' ? 0 : value;
    $.ajax({
        url:'../api/config.php',
        type:'POST',
        async:true,
        data:{action:'cambioPedidoGlobal',srr:srr,num:num,fec:fec,cli:cli,mon:mon,cpg:cpg,ven:ven,list:list,ref:ref,swt_cot:swt_cot,idMped:idMped},
        success:function(response){
            console.log(response);
            //Pendiente enviar datos al php(tambien cuando no existen valores para enviar), y cuando se agregue nuevo producto, select por si existe datos grabados y terminar de enviar 
        },
        error:function(error){
            console.log(error);
        }
    });
}
function sendAjaxRequest(action, data, successCallback) {
    $.ajax({
        url: '../api/tables.php',
        type: 'POST',
        async: true,
        data: { action: action, ...data },
        success: successCallback,
        error: function(error) {
            console.error('Ajax request error:', error);
        }
    });
}

// Attach keyup event to inputs
function attachKeyupEvent() {
    // Se elimina el listener previo para evitar acumulación
    $('.form-desc_glob, .form-por_tigv, .form-imp_isc, .form-imp_iceberg, .select_pedido, .text_obs').off('blur').on('blur', function() {
        updateTableDetail();
    });
    
    $('.form-cantidad, .form-precio, .form-desc_item').off('blur').on('blur', function(event) {
        
        var cpg = $('#cond_pago').val();
        var ven = $('#vendedor').val();
        var inputId = this.id;
        var match = inputId.match(/(descripcion|cantidad|precio|descuento)-(\d+)/);
        var tipo = match ? match[1] : null;
        var correlativo = match ? match[2] : null;
        
        var des = $('#descripcion-' + correlativo).val();
        var can = $('#cantidad-' + correlativo).val();
        var pre = $('#precio-' + correlativo).val();
        var dsc = $('#descuento-' + correlativo).val();
        
        //totales
        var desc_glob = $('.form-desc_glob').val();
        var por_tigv = $('.form-por_tigv').val();
        var imp_isc = $('.form-imp_isc').val();
        var imp_iceberg = $('.form-imp_iceberg').val();
        var imp_total = $('#imp_total').text();
        var obs = ($('#text_obs').val() !== undefined && $('#text_obs').val() !== null) ? $('#text_obs').val() : '';
        
        
        // Validate quantity
        if (tipo === 'cantidad' && (can <= 0 || isNaN(can))) {
            can = 1;
            $('#cantidad-' + correlativo).val(can);
        }
        sendData(correlativo, des, can, pre, dsc,desc_glob,por_tigv,imp_isc,imp_iceberg,imp_total,obs,0, cpg, ven);
    });
    
    if ($('#detalle_venta tr').length > 0) {
        $('#btn_guardar_pedido').show();
        $('#btn_view_pdf').show();
    } else {
        $('#btn_guardar_pedido').hide();
        $('#btn_view_pdf').hide();
    }
    
    $('.form-descripcion').off('keyup').on('keyup', function() {
        var cpg = $('#cond_pago').val();
        var ven = $('#vendedor').val();
        var inputId = this.id;
        var match = inputId.match(/descripcion-(\d+)/);
        var correlativo = match ? match[1] : null;
        
        var des = $(this).val();
        var can = $('#cantidad-' + correlativo).val();
        var pre = $('#precio-' + correlativo).val();
        var dsc = $('#descuento-' + correlativo).val();
        
        //totales
        var desc_glob = $('.form-desc_glob').val();
        var por_tigv = $('.form-por_tigv').val();
        var imp_isc = $('.form-imp_isc').val();
        var imp_iceberg = $('.form-imp_iceberg').val();
        var imp_total = $('#imp_total').text();
        var obs = ($('#text_obs').val() !== undefined && $('#text_obs').val() !== null) ? $('#text_obs').val() : '';
        
        var swtIgv = $('#checkIgv').is(':checked')?1:0;
        
        var action = 'updateTemp';
        var data = { id:correlativo, des:des,can:can, pre:pre, dsc:dsc, desc_glob:desc_glob, por_tigv:por_tigv, imp_isc:imp_isc, imp_iceberg:imp_iceberg, imp_total:imp_total,obs:obs, cpg:cpg, ven:ven,swtIgv:swtIgv
        };
        
        handleAjaxRequest(action, data,function(info) {})
    });
    
    //acciones extra
    $('i.bonificacion').hover(
        function() {
            $(this).addClass('fa-fade');
        },
        function() {
            $(this).removeClass('fa-fade');
        }
    );
    $('i.fa-trash-can').hover(
        function() {
            $(this).addClass('fa-shake');
        },
        function() {
            $(this).removeClass('fa-shake');
        }
    );
    checkCodFormAfter();

    
}
function updateTableDetail(val){
    var cpg = $('#cond_pago').val();
    var ven = $('#vendedor').val();
    var desc_glob = $('.form-desc_glob').val();
    var por_tigv = $('.form-por_tigv').val();
    var imp_isc = $('.form-imp_isc').val();
    var imp_iceberg = $('.form-imp_iceberg').val();
    var imp_total = $('#imp_total').text();
    var obs = ($('#text_obs').val() !== undefined && $('#text_obs').val() !== null) ? $('#text_obs').val() : '';
    
    sendDataOrder(desc_glob,por_tigv,imp_isc,imp_iceberg,imp_total,obs, cpg, ven, val);
}
function realizarAccion() {
    var numSerie = $('#num_ped').val();
    var serieId = $('#serie').val();
    // Llamada AJAX para verificar si existe una modificación en detalle_temp
    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        async: true,
        data: { action: 'checkTempDetail', numSerie: numSerie, serieId: serieId },
        success: function(response) {
            console.log(response);
            if (response!=0) {
                $('.swal2-container.swal2-center.swal2-backdrop-show').on('click',function() {console.log('a')});
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger",
                        cancelButton: "btn btn-danger"
                    },
                });
                
                // Configuración del SweetAlert para seleccionar una opción
                swalWithBootstrapButtons.fire({
                    title: "Pedido con cambios temporales!!",
                    text: "Abrir:",
                    icon: "warning",
                    showCancelButton: true,
                    showDenyButton: true,
                    showConfirmButton: true,
                    confirmButtonText: "Original ",
                    denyButtonText: "Temporal",
                    cancelButtonText: "Salir-Nuevo",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        cargarPedidoOriginal()
                        deletePreviousDetail();
                        originalData()
                    }  else if (result.isDismissed) {
                        $('#consultar_ultimo').trigger('click');
                    } else {
                        searchForDetails(0,numSerie,serieId);
                        deletePreviousDetail();
                        originalData()
                    }
                });
            } else {
                // Si no hay modificaciones, cargar directamente el pedido original
                cargarPedidoOriginal();
                deletePreviousDetail();
                originalData()
            }
        },
        error: function(error) {
            console.error(error);
        }
    });
}

function cargarPedidoOriginal(){
    var numSerie = $('#num_ped').val();
    var serieId = $('#serie').val();
    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        data: {action:'consultar_pedido_ocom', serieId: serieId, numSerie: numSerie,swt:0},
        success: function(response) {
            console.log(response);
            var info = JSON.parse(response);
            // Si no hay resultados, limpiar todos los campos del HTML
            if (info.clientes.length === 0 || info.resultado_procedimiento.length === 0) {
                // console.log('length 0');
                $('#idcliente').val('');
                $('#ruc_cliente').val('');
                $('#nom_cliente').val('');
                $('#tel_cliente').val('');
                $('#dir_cliente').val('');
                $('#fec_ped').val('<?=$current_date?>');
                $('.btn_new_cliente').slideDown(); // Mostrar el botón para añadir nuevo cliente si estaba oculto
                $('#id_pedido_update').val('');
                // Limpiar cualquier otro campo que necesites
                $('.section_bottom-right').html(``);
                //Mandando adicionalmente los parametros 1,0 para que busque detalle_temp del pedido nuevo pero que igual cargue la pagina 
                searchForDetails(0,1,0);
                viewProcesar();
                
                disabledForStatus(1);
                
                
            } else {
                var cliente = info.clientes[0]; // Accede al primer cliente en el array de clientes
                var codPedido = info.resultado_procedimiento[0]; // Accede al codigo del pedido
                var swt_cot = (cliente.swt_cot == 1)?true:false;
                var swt_igv = (cliente.swt_igv == 1)?true:false;
                // console.log(cliente.swt_cot);
                // console.log('swt_igv');
                $('#idcliente').val(cliente.idcliente);
                $('#ruc_cliente').val(cliente.ruc);
                $('#nom_cliente').val(cliente.nombre);
                $('#tel_cliente').val(cliente.telefono);
                $('#dir_cliente').val(cliente.direccion);
                
                $('#id_pedido_update').val(codPedido.id_mpedido);
                // Insertar valor en el input fecha
                $('#fec_ped').val(cliente.fec_ped);
                // Seleccionar la opción existente en el select moneda
                $('#moneda').val(cliente.id_dtab_mon);
                // Seleccionar la opción existente en el select vendedor
                $('#vendedor').val(cliente.id);
                $('.section_bottom-right').html(`<p>Usuario:${cliente.des_usr}</p><p>Fecha:${(cliente.fec_usu||'- -').split(' ')[0]}</p>`);
                $('#modo_cot').prop('checked', swt_cot);
                if(swt_cot){
                    $('.titulo_flotante').html('COTIZACION');
                }
                $('#checkIgv').prop('checked', swt_igv);
                $('#ref_cliente').val(cliente.num_ocom);
                //Trayendo los detalles tal cual se encuentran en la tabla detalle_temp
                searchForDetails(0,$('#num_ped').val(),$('#serie').val());
                // $('#modo_cot').trigger('change');
                // $('#checkIgv').trigger('change');
                viewProcesar();
                disabledForStatus(codPedido.SWT_PED);
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
}

function originalData(){
    // Almacena el valor original en un atributo data
    $('#num_ped').data('original-value', $('#num_ped').val());
    $('#serie').data('original-value', $('#serie').val());
};
function deletePreviousDetail(){
    let srr = $('#serie').data('original-value');
    let crr = $('#num_ped').data('original-value');
    // if(!srr && !crr){
        //     return;
        // }
        $.ajax({
            url: '../api/config.php',
            type: 'POST',
            async: true,
            data: {action: 'deletePreviousDetail', srr : srr,crr:crr},
            success: function(response) {
                console.log(response,'Se eliminó un detalleTemp',srr,crr);
                // let info = JSON.parse(response);
        },
        error: function(error) {
            console.error(error);
        }
    });
}

</script>

<?php include "includes/footer.php" ?>
</body>
</html>