


/*sidebar automatico */
$(document).ready(function() {
    $('li.principal a.main,li.principal a.final').on('click', function(event) {
        event.preventDefault();
    })
    $('.principal').mouseenter(function() {
        $(this).find('.span_principal').addClass('visible');
    }).mouseleave(function() {
        $(this).find('.span_principal').removeClass('visible');
        $(this).find('a .span_secundario').removeClass('visible');
    });
    //Poder ver los submodulos
    $('nav ul li').click(function() {
        $(this).find('.ul_sec').addClass('visible');
        $(this).find('.secundario').addClass('visible');
        $('.span_principal').removeClass('visible');
        $(this).find('a .span_secundario').addClass('visible');
        $('nav ul li ul').mouseenter(function() {
            $(this).find('a .span_secundario').addClass('visible');
        }).mouseleave(function() {
            $(this).find('a .span_secundario').removeClass('visible');
            $('nav ul li ul').removeClass('visible');
        });
    });


    


    //btn-detalles
    $('.btn-detalles').click(function(event){
        event.preventDefault()
        $('.section4').slideToggle();

    });
    //Si el second sidebar tiene muchos 
    //--------------------- SELECCIONAR FOTO PRODUCTO ---------------------
    $("#foto").on("change",function(){
    	var uploadFoto = document.getElementById("foto").value;
        var foto       = document.getElementById("foto").files;
        var nav = window.URL || window.webkitURL;
        var contactAlert = document.getElementById('form_alert');
        
            if(uploadFoto !='')
            {
                var type = foto[0].type;
                var name = foto[0].name;
                if(type != 'image/jpeg' && type != 'image/jpg' && type != 'image/png')
                {
                    contactAlert.innerHTML = '<p class="errorArchivo">El archivo no es valido.</p>';                        
                    $("#img").remove();
                    $(".delPhoto").addClass('notBlock');
                    $('#foto').val('');
                    return false;
                }else{  
                        contactAlert.innerHTML='';
                        $("#img").remove();
                        $(".delPhoto").removeClass('notBlock');
                        var objeto_url = nav.createObjectURL(this.files[0]);
                        $(".prevPhoto").append("<img id='img' src="+objeto_url+">");
                        $(".upimg label").remove();
                        
                    }
              }else{
              	alert("No selecciono foto");
                $("#img").remove();
              }              
    });

    $('.delPhoto').click(function(){
    	$('#foto').val('');
    	$(".delPhoto").addClass('notBlock');
    	$("#img").remove();

        if($("foto_actual") && $("#foto_remove")){
            $("#foto_remove").val('img_producto.png');
        }

    });
    //Modal form add product
$('.add_product').click(function(e){
    e.preventDefault();
    var producto = $(this).attr('product');
    var action = 'infoProducto';

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data:{action:action,producto:producto},

        success: function(response){
            if(response != 'error'){
                var info = JSON.parse(response);
                // $('#producto_id').val(info.codproducto);
                // $('.nameProducto').html(info.descripcion);

                $('.bodyModal').html(
                    `<form class="form_add_product" action="" method="post" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); sendDataProduct();">
                        <h2><span class="material-symbols-outlined">Add</span>Agregar productos</h2>
                        <h3 class="nameProducto">${info.des_prod}</h3>
                        <input type="number" name="cantidad" id="txtCantidad" placeholder="Cantidad del producto" required  min="0">
                        <input type="number" name="precio" id="txtPrecio" placeholder="Precio del producto" required  min="0">
                        <input type="hidden" name="producto_id" id="producto_id" value="${info.id}" required>
                        <input type="hidden" name="action" value="addProduct" required>
                        <div class="alert alertAddProduct"></div>
                        <button type="submit" class="btn btn_new material-symbols-outlined" title="Agregar">Add</button>
                        <a onclick="closeModal();" href="#" class="btn btn_ok closeModal "><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                    </form>`);
            }
        },

        error: function(error){
            console.error(error);
        }

    });


    $('.modal').fadeIn();
    document.addEventListener('keydown', handleKeyPress);
});
    //Modal form delete product
    $('.del_product').click(function(e){
        e.preventDefault();
        var producto = $(this).attr('product');
        var action = 'infoProducto';
    
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data:{action:action,producto:producto},
    
            success: function(response){
                if(response != 'error'){
                    var info = JSON.parse(response);
                    // $('#producto_id').val(info.codproducto);
                    // $('.nameProducto').html(info.descripcion);
    
                    $('.bodyModal').html(
                        `<form class="form_del_product" action="" method="post" name="form_del_product" id="form_del_product" onsubmit="event.preventDefault(); delProduct();">
                            <h2><span class="material-symbols-outlined">Delete_Forever</span>Eliminar productos</h2>
                            <h4>¿Está seguro de querer eliminar el siguiente producto?</h4>
                            <h3 class="nameProducto">${info.des_prod}</h3>
                            
                            <input type="hidden" name="producto_id" id="producto_id" value="${info.id}" required>
                            <input type="hidden" name="action" value="delProduct" required>
                            <div class="alert alertAddProduct"></div>

                            <button type="submit" class="btn btn_ok material-symbols-outlined" title="Eliminar">Delete</button>
                            <a onclick="closeModal();" href="#" class="btn btn_cancel "><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                            
                            
                        </form>`);
                }
            },
    
            error: function(error){
                console.error(error);
            }
    
        });
    
    
        $('.modal').fadeIn();
        document.addEventListener('keydown', handleKeyPress);
    });

    


    $('#search_proveedor').change(function(e){
        e.preventDefault();

        var sistema = getUrl();
        location.href = sistema+'buscar_productos.php?proveedor='+$(this).val();
    });
    //Activar campos para registrar cliente
    $('.btn_new_cliente').click(function(e){
        e.preventDefault();
        $('#nom_cliente').removeAttr('disabled');
        $('#tel_cliente').removeAttr('disabled');
        $('#dir_cliente').removeAttr('disabled');

        $('#div_registro_cliente').slideDown();
    });

    
    

    


    //Buscar productos
    $('#txt_cod_producto').keyup(function(e){
        e.preventDefault();
        var busqueda = $(this).val();
        var action = 'infoProducto';

        if(busqueda != ''){
            $.ajax({
                url:'ajax.php',
                type:"POST",
                async: true,
                data:{action:action,busqueda:busqueda},
    
                success: function(response){
                },
                error: function(error){
                }
            });
        }
        
    });

    //Validar Cantidad del producto antes de agregar
    $('#txt_cant_producto').keyup(function(e){
        e.preventDefault();
        var precio_total = $(this).val() * $('#txt_precio').html();
        $('#txt_precio_total').html(precio_total);

        //Oculta el boton agregar si la cantidad es menor que 1 o mayor a la existencia
        if(  ($(this).val()<1||isNaN($(this).val())) ){
            $('#add_product_venta').slideUp();
        }else{
            $('#add_product_venta').slideDown();
        }
    });
    
    

    //Precio unitario - total
    // $(document).ready(function() {
    //     // Asocia el evento input al campo de precio
    //     $('.form-precio').on('input', function() {
    //         // Encuentra el padre <tr> de los inputs
    //         var $tr = $(this).closest('tr');
    //         // Encuentra los inputs de cantidad, precio y precio total dentro del <tr>
    //         var $cantidad = $tr.find('.form-cantidad');
    //         var $precio = $tr.find('.form-precio');
    //         var $precioTotal = $tr.find('.form-precio-total');
    
    //         // Obtiene los valores de cantidad y precio
    //         var cantidad = parseFloat($cantidad.val());
    //         var precio = parseFloat($(this).val());
    
    //         // Calcula el precio total multiplicando cantidad por precio
    //         var precioTotal = cantidad * precio;
    
    //         // Actualiza el valor del campo de precio total
    //         $precioTotal.text(precioTotal.toFixed(2));
    //     });
    // });
    
    
    $(document).on('click', '.guardar-actualizar-pedido', function(e) {
        e.preventDefault();
        
        var rows = $('#detalle_venta tr').length;
        if (rows > 0) {
            // Obtener el valor del id_pedido_update
            const id_ped = (value = $('#id_pedido_update').val()) == null || value === '' || value === '0' ? 0 : value;
            // console.log(id_ped);
            // Determinar la acción basada en el id del botón
            const action = (this.id === 'btn_guardar_pedido') ? 'procesarVenta' : 
            (this.id === 'btn_actualizar_pedido' && id_ped) ? 'actualizarPedido': 'procesarVenta';
    
            var codcliente = $('#idcliente').val() || 1;
            var serie = $('#serie').val();
            var ven = $('#vendedor').val();
            var mon = $('#moneda').val();
            var fec = $('#fec_ped').val();
            var ttot = $('#imp_total').text();
            var swt_cot = $('#modo_cot').prop('checked')?1:0;
            var ref = $('#ref_cliente').val();
            
            var pto_venta = $('#checkFacturarVenta').prop('checked')?1:0;
            var term_ped = $('#checkTerminarPedido').prop('checked')?2:1;

            // var igv = $('#final_imp_tigv').val();
            // var descGlob = $('#final_desc_globt').val();
            var data = {
                action: action,
                codcliente: codcliente,
                serie: serie,
                ven: ven,
                mon: mon,
                fec: fec,
                ttot: ttot,
                swt_cot: swt_cot,
                ref: ref,
                pto_venta: pto_venta,
                term_ped: term_ped,
                id_ped: id_ped
            };
    
            // console.log(data);
            $.ajax({
                url: 'ajax.php',
                type: "POST",
                async: true,
                data: data,
                success: function(response) {
                    console.log(response);
                    try {
                        var info = JSON.parse(response);
    
                        if (info.success === false) {
                            createToast('error', 'fa-solid fa-circle-exclamation', 'Error', info.message);
                        } else {
                            generarPDF(info.data['id'],0);
                            var ped_save = true;
                            localStorage.setItem('ped_save', ped_save);
                            location.reload();
                        }
                    } catch (e) {
                        console.error('Error de formato en la respuesta:', e);
                    }
                },
                error: function(error) {
                    console.error('Error en la solicitud AJAX:', error);
                }
            });
        }
    });

    //Modal form delete factura
    $('.anular_factura').click(function(e){
        e.preventDefault();
        var nofactura = $(this).attr('fac');
        var action = 'infoFactura';
    
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data:{action:action,nofactura:nofactura},
    
            success: function(response){
                if(response != 'error'){
                    var info = JSON.parse(response);
                    
                    $('.bodyModal').html(
                        `<form class="form_add_product" action="" method="post" name="form_anular_factura" id="form_anular_factura" onsubmit="event.preventDefault(); anularFactura();">
                            <h2><span class="material-symbols-outlined">Delete_Forever</span>Anular Pedido</h2>
                            <h4>¿Está seguro de querer anular el pedido??</h4>

                            <p><strong>N°: ${info.id}</strong></p>
                            <p><strong>Monto: S/. ${info.imp_ttot}</strong></p>
                            <p><strong>Fecha: ${info.fec_ped}</strong></p>

                            <input type="hidden" name="action" value="anularFactura">
                            <input type="hidden" name="no_factura" id="no_factura" value="${info.id}" required>
                            
                            <div class="alert alertAddProduct"></div>

                            <button type="submit" class="btn btn_ok material-symbols-outlined" title="Anular">Delete_Forever</button>

                            <a onclick="closeModal();" href="#" class="btn btn_cancel "><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                        </form>`);
                }
            },
    
            error: function(error){
                console.error(error);
            }
    
        });
    
    
        $('.modal').fadeIn();
        document.addEventListener('keydown', handleKeyPress);
    });

    //MODAL INFO PEDIDO
    $('.info_pedido').click(function(e){
        e.preventDefault();
        var nopedido = $(this).attr('ped');

        var action = 'infoPedido';
    
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data:{action:action,nopedido:nopedido},
    
            success: function(response){
                if(response != 'error'){
                    var info = JSON.parse(response);
                    $('.bodyModal').html(
                        `<form class="form_tramitar_pedido" action="" method="post" name="form_tramitar_pedido" id="form_tramitar_pedido" onsubmit="event.preventDefault(); tramitarPedido();">
                            <h2><span class="material-symbols-outlined">Task</span>Tramitar Pedido</h2>
                            <h4>¿Desea tramitar el siguiente pedido?</h4>

                            <p><strong>N°: ${info.id}</strong></p>
                            <p><strong>Monto: S/. ${info.imp_ttot}</strong></p>
                            <p><strong>Nombre del cliente: ${info.des_cli}</strong></p>
                            <p><strong>RUC: ${info.ruc_cli}</strong></p>

                            <input type="hidden" name="action" value="tramitarPedido">
                            <input type="hidden" name="no_pedido" id="no_pedido" value="${info.id}" required>
                            <input type="hidden" name="idcliente" id="idcliente" value="${info.idcliente}" required>
                            
                            <div class="alert alertTramitar"></div>

                            <button type="submit" class="btn btn_ok material-symbols-outlined" title="Tramitar pedido">Check_Circle</button>

                            <a onclick="closeModal();" href="#" class="btn btn_cancel "><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                        </form>`);
                }else{
                    console.error("data error");
                }
            },
    
            error: function(error){
                console.error(error);
            }
    
        });
    
    
        $('.modal').fadeIn();
        document.addEventListener('keydown', handleKeyPress);
    });

    //MODAL ANULAR TRAMITE
    $('.info_tramite').click(function(e){
        e.preventDefault();
        var nopedido = $(this).attr('fac');

        var action = 'infoTramite';
    
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data:{action:action,nopedido:nopedido},
    
            success: function(response){
                if(response != 'error'){
                    var info = JSON.parse(response);
                    $('.bodyModal').html(
                        `<form class="form_anular_tramite" action="" method="post" name="form_anular_tramite" id="form_anular_tramite" onsubmit="event.preventDefault(); anularTramite();">
                        <h2><span class="material-symbols-outlined">Delete</span>Anular Trámite</h2>
                        <h4>¿Realmente desea anular el siguiente trámite?</h4>

                        <p><strong>N°: ${info.id}</strong></p>
                        <p><strong>Monto: S/. ${info.imp_ttot}</strong></p>
                        <p><strong>Nombre del cliente: ${info.des_cli}</strong></p>
                        <p><strong>RUC: ${info.ruc_cli}</strong></p>

                        <input type="hidden" name="action" value="tramitarPedido">
                        <input type="hidden" name="no_pedido" id="no_pedido" value="${info.id}" required>
                        
                        <div class="alert alert_anular_tramite"></div>

                        <button type="submit" class="btn btn_ok material-symbols-outlined" title="Anular trámite">Delete_Forever</button>

                        <a onclick="closeModal();" href="#" class="btn btn_cancel "><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                        </form>`);
                }
                else{
                    console.error("data error");
                }
            },
    
            error: function(error){
                console.error(error);
            }
    
        });
    
    
        $('.modal').fadeIn();
        document.addEventListener('keydown', handleKeyPress);
    });

    //Ver factura
    $('.view_factura').click(function(e){
        e.preventDefault();
        var codcliente = $(this).attr('cl');
        var noFactura = $(this).attr('f');

        generarPDF(noFactura);
    });

    //cambiar password
    $('.newPass').keyup(function(){
        validPass();
    });

    //Form cambiar contraseña
    $('#frmChangePass').submit(function(e){
        e.preventDefault();

        var passActual = $('#txtPassUser').val();
        var passNuevo = $('#txtNewPassUser').val();
        var confirmPassNuevo = $('#txtPassConfirm').val();
        var action = "changePassword";

        if (passNuevo != confirmPassNuevo) {
            $('.alertChangePass').html('<p>Las contraseñas no son iguales.</p>').slideDown();
            return false;
        }
        if (passNuevo.length < 6) {
            $('.alertChangePass').html('<p>Debe contener como mínimo 6 caracteres.</p>').slideDown();
            return false;
        }


        $.ajax({
            url: 'ajax.php',
            type: "POST",
            data: {
                action: action,
                passActual: passActual,
                passNuevo: passNuevo
            },
            success: function(response){
                if(response != 'error'){
                    var info = JSON.parse(response);
                    if(info.code == '00'){
                        $('.alertChangePass').html('<p class="a_succes>'+info.msg+'</p>');
                        $('#frmChangePass')[0].reset();
                    }//Si fue error
                    else{
                        $('.alertChangePass').html('<p class="a_error">'+info.msg+'</p>');
                    }
                    $('.alertChangePass').slideDown();
                }
            },
            error: function(error){
            }
        });
    });

    //Actualizar datos empresa
    $('#frmEmpresa').submit(function(e){
        e.preventDefault();

        var intRuc = $('#txtRuc').val();
        var strNombreEmp = $('#txtNombre').val();
        var strRSocialEmp = $('#txtRSocial').val();
        var strTelEmp = $('#txtTelEmpresa').val();
        var strEmailEmp = $('#txtEmailEmpresa').val();
        var strDirEmp = $('#txtDirEmpresa').val();
        var intIgv = $('#txtIgv').val();

        if(intRuc == ''||strNombreEmp == ''||strTelEmp == ''||strEmailEmp == ''||strDirEmp == ''||intIgv == ''){
            createToast('warning', 'fa-solid fa-triangle-exclamation', 'Atención', 'Todos los campos son obligatorios.');
            return false;
        }
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: $('#frmEmpresa').serialize(),
            beforeSend: function(){
                $('.alertFormEmpresa').slideUp();
                $('.alertFormEmpresa').html('');
                $('#frmEmpresa input').attr('disabled','disabled');
            },
            success: function(response){
                var info = JSON.parse(response);
                if(info.cod == '00'){
                    createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Datos de empresa actualizados.');
                    $('#frmEmpresa input').removeAttr('disabled');
                }else{
                    createToast('error', 'fa-solid fa-circle-exclamation', 'Error', info.msg);
                }
                $('#frmEmpresa input').removeAttr('disabled');
            },
            error:function(error){

            }
        });
    });

    //CLIENTES
    //Crear cliente - Ventana ventas
    $('#form_new_cliente_venta').submit(function(e){
        e.preventDefault();

        $.ajax({
            url:'ajax.php',
            type:"POST",
            async: true,
            data:$('#form_new_cliente_venta').serialize(),

            success: function(response){
                if(response != 'error'){
                    //Agregando el id hidden
                    $('#idcliente').val(response);
                    //Agregando el id hidden
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');
                    //Ocultando botones
                    $('.btn_new_cliente').slideUp();
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function(error){
            }
        });
    });

    var clienteEncontrado = false;
    //Buscar Cliente
    $('#ruc_cliente').on('keyup',function(e){
        e.preventDefault();
        var cl = $(this).val();
        var action = 'searchcliente';

        $.ajax({
            url:'ajax.php',
            type:"POST",
            async: true,
            data:{action:action,cliente:cl},

            success: function(response){
                
                if(response == 0){
                    $('#idcliente').val('');
                    $('#nom_cliente').val('');
                    $('#tel_cliente').val('');
                    $('#dir_cliente').val('');
                    $('#cond_pago').val(705);
                    $('#vendedor').val(0);
                    clienteEncontrado = false;
                    $('.foundClient').html('Ruc no encontrado')
                }else{
                    $('.foundClient').html('')
                    var data = $.parseJSON(response);
                    // console.log(data);
                    $('#idcliente').val(data.ID);
                    $('#nom_cliente').val(data.DES_CLI);
                    $('#nom_cliente').trigger('change');


                    $('#tel_cliente').val(data.TEL_CLI);
                    $('#dir_cliente').val(data.DIR_CLI);
                    if(!$('#id_pedido_update').val() && !$('#id_mdoccli_update').val()){
                        $('#vendedor').val(data.ID_MVENDED??0);
                        $('#cond_pago').val(data.ID_DTAB_CPAG??705);
                        $('#moneda').val(data.ID_DTAB_MON??3503);
                        $('#lista').val(data.LIST??1052);
                    }

                    //Bloque campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');

                    // //Oculta boton guardar
                    // $('#div_registro_cliente').slideUp();
                    if(clienteEncontrado==false&&!$('#ruc_cliente').hasClass('despacho')){
                        
                    // createToast('success', 'fa-solid fa-circle-check', data.DES_CLI, 'Se encontró el cliente en la base de datos local.');
                    }
                    clienteEncontrado = true;
                    if (typeof cambioPedidoGlobal === 'function') {
                        cambioPedidoGlobal();
                    }
                    if (typeof searchNumRef === 'function' && !$('#id_mdoccli_update').val()) {
                        searchNumRef();;
                    }
                }
            },
            error: function(error){
                clienteEncontrado = false;
            }
        });
    });

    $('.ruc_cliente').off('blur').on('blur',function(){
        // console.log(clienteEncontrado);
        if(clienteEncontrado === true){
            return;
        }
        // var swt = $(this).data('swt_modal');
        // if(swt == "active"){
        //     $('.alert').empty();
        //     // clienteEncontrado = false;
        //     //Buscar los tipos de documento

        var ruc= $('#ruc_cliente').val();
        if(ruc!=''){
            modalClientInfo('Crear Nuevo',ruc)
        }
        // }
    })
    

    // Seleccionar todos los formularios cuyo ID comience con "updateForm"
    // Seleccionar todos los formularios cuyo ID comience con "updateForm"
    $('form[id^="updateForm"]').each(function() {
        var correlativo = this.id.replace('updateForm', '');
        var $cantidadInput = $('#cantidad-' + correlativo);
        var $precioUnitarioInput = $('#precio-unitario-' + correlativo);
        var $precioTotalInput = $('#precio-total-' + correlativo);

        // Función para actualizar el precio total
        function updatePrecioTotal() {
            var cantidad = parseFloat($cantidadInput.val());
            var precioUnitario = parseFloat($precioUnitarioInput.val());
            if (!isNaN(cantidad) && !isNaN(precioUnitario)) {
                $precioTotalInput.val((cantidad * precioUnitario).toFixed(2));
            }
        }

        // Función para actualizar el precio unitario
        function updatePrecioUnitario() {
            var cantidad = parseFloat($cantidadInput.val());
            var precioTotal = parseFloat($precioTotalInput.val());
            if (!isNaN(cantidad) && !isNaN(precioTotal) && cantidad !== 0) {
                $precioUnitarioInput.val((precioTotal / cantidad).toFixed(2));
            }
        }

        // Evento para actualizar el precio total cuando se modifica el precio unitario
        $precioUnitarioInput.on('keyup', function() {
            updatePrecioTotal();
        });

        // Evento para actualizar el precio unitario cuando se modifica el precio total
        $precioTotalInput.on('keyup', function() {
            updatePrecioUnitario();
        });

        // Evento para actualizar ambos cuando se modifica la cantidad
        $cantidadInput.on('keyup', function() {
            updatePrecioTotal();  // Primero actualizamos el precio total
            updatePrecioUnitario();  // Luego actualizamos el precio unitario si el total cambió
        });
    });

    //acomodar la paginacion al buscar
    $('input#busqueda,select#num_registros').click(function(){
        $('a.pagina').first().trigger('click');
        // $(this).focus();
    })
    

});//End Ready

function openModalClient(swt){
    $('.bodyModal').html(`
<div class="container-modal">
    <form class="formModalClient">
        <div class=" container-top_modal">
            <h2><span class="material-symbols-outlined">Groups</span>Clientes</h2>
            <input type="text" name="busqueda_client" id="busqueda" placeholder="" value="" title="Buscar cliente" width="200px">
            <div class="col-auto text-start">
                <label for="num_registros" class="num_registros" title="Número de registros"># registros</label>
                <select name="num_registros" id="num_registros" class="form-select" >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="cotizacion_container">
                <input type="checkbox" name="state_check" id="state_check" onchange="getDataClient(${swt})" class="modo-lista-checkbox">
                <label for="state_check">+ inactivos</label>
            </div>
            <i class="fa-regular fa-circle-xmark" style="color: #ff4757;cursor:pointer;margin: 10px;font-size: 29px;" onclick="closeModal();"></i>
        </div>
        <div class="table-responsive table-modal">
            <table class="table" id="results">
                <tr>
                    <th>ID</th>
                    <th>N° Documento</th>
                    <th>Nombre</th>
                    <th>Ubigeo</th>
                    <th>Direccion</th>
                </tr>

            </table>
        </div>
        <div class="paginador">
            <ul id="nav-paginacion"></ul>
        </div>
            <input type="hidden" id="pagina" value="1">
            <input type="hidden" id="orderCol" value="0">
            <input type="hidden" id="orderType" value="asc">
        <div class="alert">
            <?php echo isset($alert) ? $alert : ''; ?>
        </div>
    </form></div>`);
    getDataClient(swt);
    $('.modal').fadeIn();
    document.addEventListener('keydown', handleKeyPress);
    $('#busqueda').keyup(function(e) {
        getDataClient(swt)
        e.preventDefault();
    });

    //Cambio de registros
    $('#num_registros').change(function() {
        getDataClient(swt)
    });
    $('#busqueda').focus();
    
};


//Modal info client
function modalClientInfo(msg='Añadir',ruc='',des='',tel='',dir='',id=null,swtCli=1){
    // console.log(id);
    var swtEst = (swtCli == 1)?'checked':'';
    $.ajax({
        url: '../api/getTdocCli.php',
        type: 'POST',
        // dataType: 'json',
        data:{id:id},
        success: function(data) {
            //MODAL AND animation
            $('.bodyModal').html(
                `<form class="form_modal" action="" method="post">
                <h2>${msg} Cliente</h2>
                <div class="group_modal">
                </div></form>`);

            $('.group_modal').append(data.optionsTdoc);
            $('.group_modal').append(
                `<div class="modal_item">
                    <label for="new_ruc_cliente">Número de Documento:*</label>
                    <input type="text" name="ruc_cliente" id="new_ruc_cliente" class="new_ruc_cliente" placeholder="Ingrese número de documento" value="${ruc}" required>
                    <i class="fa-solid fa-cloud-arrow-down apiclient" title="Consultar API SUNAT" onclick="apiRucClient()"></i>
                </div>
                <div class="modal_item">
                <label for="new_razon_social">Razón Social o Nombre:*</label>
                <input type="text" name="razon_social" id="new_razon_social" value="${des}" placeholder="Nombre de la empresa o persona jurídica" required>
                </div>`);
                
            $('.group_modal').append(data.optionsUbi);
                
            $('.group_modal').append(
                `</div><div class="modal_item">
                <label for="new_tel_cliente">Teléfono/celular:</label>
                <input type="text" name="tel_cliente" id="new_tel_cliente" placeholder="+51 " required value="${tel}">
                </div></div><div class="modal_item">
                <label for="new_email_cliente">Correo electronico:*</label>
                <input type="email" name="email_cliente" id="new_email_cliente" placeholder="correo@ejemplo.com" value="${data.email}">
                </div><div class="modal_item">
                <label for="new_dir_cliente">Dirección:*</label>
                <input type="text" name="dir_cliente" id="new_dir_cliente" placeholder="Ingrese dirección" value="${dir}"></div>
                `);

            $('.group_modal').append(data.optionsTpc,data.optionsVen,data.optionsCpg,data.optionsMon);
            $('.group_modal').append(
                `<div class="group_modal">
                    <label for="c_checkbox">Cliente <span class="cliente_estado">activo</span>:</label>
                    <div class="c_background_box">
                        <label class="c_toggle_box">
                            <input type="checkbox" id="c_checkbox" ${swtEst}>
                            <div class="c_circle"></div>
                        </label>
                    </div> 
                </div>
                <div class="group_modal">
                    <input onclick="event.preventDefault();" type="submit" value="Save" class="btn btn_newClient_m material-symbols-outlined" title="Guardar Cliente" data-idcliente='${id}'>
                    <a onclick="closeModal();" href="#" class="btn closeModal "><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                </div>
            `);
            var mensaje = (id==null)?'creado':'actualizado';
            $('.modal').fadeIn();
            document.addEventListener('keydown', handleKeyPress);
            $('.btn_newClient_m').on('click', function(event) {
                addNewClient(mensaje);
                var rucN = $('#new_ruc_cliente').val();
                $('#ruc_cliente').text(rucN);
                $('#ruc_cliente').trigger('input');
                if ($('#pagina').length > 0 && $('#pagina').val() != null) {
                    getDataClient();
                }
            }); 
            //SELECT2
            $("#tipo_ubigeo").select2({
                matcher: matchCustom, 
                minimumResultsForSearch: 20,  
                maximumSelectionLength: 20, 
            });
            $(".c_toggle_box").click(function() {
                toggleClientButton()
            });
            toggleClientButton()
            function toggleClientButton(){
                var checkbox = $("#c_checkbox")[0]; // Obtener el elemento checkbox
                var circle = $(".c_circle");
                var toggle_box = $(".c_toggle_box");
    
                if (checkbox.checked) {
                    circle.css({"transform": "translateX(13px)","background-color": "var(--second-color)"});
                    toggle_box.css({"background-color": "#fff","border": "1px solid var(--nav-barra)"});
                    $('.cliente_estado').html('activo');
                } else {
                    circle.css({"transform": "translateX(0px)","background-color": "#fff"});
                    toggle_box.css("background-color", "var(--third-color)");
                    $('.cliente_estado').html('inactivo');
                }
            }
            $('#new_ruc_cliente').off('blur').trigger('blur');
        },
        error: function(error) {
            console.error('Error en la solicitud AJAX:', error);
        }
    });
    
}
//API CLIENTE
function apiRucClient(e){
    var action = "api_consulta";
    $.ajax({
        url:'ajax.php',
        type:"POST",
        async: true,
        data:{action:action,ruc:$('#new_ruc_cliente').val()},

        success: function(response){
            // console.log(response);
            var data = $.parseJSON(response);
            if(data.nombre){
                createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Ruc encontrado y completado por api SUNAT.');
                $('#new_razon_social').val(data.nombre);
                $('#new_dir_cliente').val(data.direccion);
                $('#tipo_documento').val(3721);
                $('#tipo_ubigeo').val(data.id_ubi).trigger('change');;
            }else{
                $('#new_razon_social').val('');
                $('#new_dir_cliente').val('');
                $('#tipo_documento').val('');
                $('#tipo_ubigeo').val('').trigger('change');
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'No se encontró en api SUNAT');
            }
        },
        error: function(error){}
    });
};

function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}
//Agregar y actualizar cliente
function addNewClient(msg){
    // Capturar los valores del formulario
    var tipo_documento = $('#tipo_documento').val();
    var ruc_cliente = $('.new_ruc_cliente').val();
    var razon_social = $('#new_razon_social').val();
    var ubigeo = $('#tipo_ubigeo').val();
    // var nom_cliente = $('#new_nom_cliente').val();
    var tel_cliente = $('#new_tel_cliente').val();
    var dir_cliente = $('#new_dir_cliente').val();

    var tipo = $('#select_tipo').val();
    var ven = $('#select_ven').val();
    var cpg = $('#select_cpg').val();
    var mon = $('#select_mon').val();
    var email = $('#new_email_cliente').val();
    var est = $('#c_checkbox').is(':checked')?1:0;
    email = validarEmail(email)?email:'';
    //Switch para captar estado de actualizar o crear nuevo cliente
    var swt_update = $('.btn_newClient_m').data('idcliente');
    // Enviar la información al servidor
    $.ajax({
        url: '../api/config.php',
        method: 'POST',
        data: {
            action:'addNewClient',
            tipo_documento: tipo_documento,
            ruc_cliente: ruc_cliente,
            razon_social: razon_social,
            ubigeo: ubigeo,
            tel_cliente: tel_cliente,
            dir_cliente: dir_cliente,
            tipo: tipo,
            ven: ven,
            cpg: cpg,
            mon: mon,
            mon: mon,
            email: email,
            swt_update: swt_update,
            est: est
        },
        success: function(response) {
            // console.log(response);
            var data = JSON.parse(response);
            if(data.success){
                createToast('success', 'fa-solid fa-circle-check', razon_social, `Cliente ${msg} con éxito!`);
                $('#ruc_cliente').val(ruc_cliente);
                $('#ruc_cliente').trigger('keyup');
                closeModal();
            }else{
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', data.message);
            }
            
        },
        error: function(error) {
            console.error("Error al enviar los datos al servidor:", error);
        }
    });
}
// Función para obtener los datos de los clientes
function getDataClient(swt) {
    var client = $('#busqueda').val();
    var action = 'searchClient';
    var pagina = $('#pagina').val();
    var registros = $('#num_registros').val(); // Obtener el número de registros por página
    var est = $('#state_check').is(':checked')?1:0;

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: {action:action, client:client, registros:registros, pagina:pagina, est:est},
        success: function(response) {
            var results = JSON.parse(response);
            if (results.error) {
                $('#results').html('<table class="table"><tr><th>ID</th><th>N° Doc.</th><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th></tr><tr><td colspan="6">No se encontraron usuarios.</td></tr>');
            } else {
                var output = '<table class="table"><tr class="clientTable"><th>ID</th><th>N° Doc.</th><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th></tr>';
                for (var key in results) {
                    if (results.hasOwnProperty(key) && typeof results[key] === 'object') {
                        var client = results[key];
                        output += '<tr class="row_select_client clientTable" data-ruc="'+client.RUC_CLI+'">';
                        output += '<td>' + client.ID + '</td>';
                        output += '<td>' + client.RUC_CLI + '</td>';
                        output += '<td>' + client.DES_CLI + '</td>';
                        output += '<td>' + client.TEL_CLI + '</td>';
                        output += '<td>' + client.DIR_CLI + '</td>';
                        output += '<td>';
                        if(!swt){
                            output += `<a href="#" onclick="event.preventDefault();modalClientInfo('Actualizar','${client.RUC_CLI}','${client.DES_CLI}','${client.TEL_CLI}','${client.DIR_CLI}','${client.ID}',${client.SWT_CLI});" class="material-symbols-outlined" title="Editar">Edit</a>`; 
                        }
                        output += '</td>';
                        output += '</tr>';
                    }
                }
                output += '</table>';
                $('#results').html(output);
            }

            // Actualizar el paginador con la información recibida
            var paginador = '';
            var paginaActual = parseInt($('#pagina').val()); // Obtener la página actual

            if (results.total_paginas > 1) {
                for (var i = 1; i <= results.total_paginas; i++) {
                    var clasePagina = (i === paginaActual) ? 'pagina page-selected' : 'pagina';
                    paginador += '<a href="#" class="' + clasePagina + '">' + i + '</a>';
                }
            }
            
            $('#nav-paginacion').html(paginador);

            // Event listener para los botones de paginación
            $('.pagina').click(function() {
                var pagina = $(this).text();
                $('#pagina').val(pagina);
                getDataClient(swt)
            });
            $('.row_select_client').click(function(){
                var ruc = $(this).data('ruc');
                $('#ruc_cliente').val(ruc);
                $('#ruc_cliente').trigger('keyup');
                clienteEncontrado = true;
                closeModal();

            });
            if(swt == 1){
                $('tr.clientTable').find('th:last, td:last').remove();
                
            };
        },
        error: function(error) {
            console.error(error.responseText);
        }
    });
}


// Función para obtener los datos de usuarios
function getData(swt) {
    var user = $('#busqueda').val();
    var action = 'searchuser';
    var pagina = $('#pagina').val();
    var registros = $('#num_registros').val(); // Obtener el número de registros por página

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { 
            action: action, 
            user: user, 
            registros: registros,
            pagina: pagina // Enviar el número de página actual
        },
        success: function(response) {
            var results = JSON.parse(response);
            if (results.error) {
                $('#results').html('<table class="table"><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr><tr><td colspan="6">No se encontraron usuarios.</td></tr>');
            } else {
                var output = '<table class="table"><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>';
                for (var key in results) {
                    if (results.hasOwnProperty(key) && typeof results[key] === 'object') {
                        var user = results[key];
                        output += '<tr>';
                        output += '<td>' + user.ID + '</td>';
                        output += '<td>' + user.CDG_USR + '</td>';
                        output += '<td>' + user.DES_USR + '</td>';
                        output += '<td>' + user.USR_EMAIL + '</td>';
                        output += '<td>';
                        output += `<a href="#" data-id="${user.ID}" data-des="${user.DES_USR}" data-cdg="${user.CDG_USR}" data-email="${user.USR_EMAIL}" class="material-symbols-outlined updateUserModal" title="Editar">Edit</a>`;
                        
                        output += '</td>';
                        output += '</tr>';
                    }
                }
                output += '</table>';
                $('#results').html(output);
            }

            // Actualizar el paginador con la información recibida
            var paginador = '';
            var paginaActual = parseInt($('#pagina').val()); // Obtener la página actual

            if (results.total_paginas > 1) {
                for (var i = 1; i <= results.total_paginas; i++) {
                    var clasePagina = (i === paginaActual) ? 'pagina page-selected' : 'pagina';
                    paginador += '<a href="#" class="' + clasePagina + '">' + i + '</a>';
                }
            }
            
            $('#nav-paginacion').html(paginador);

            // Event listener para los botones de paginación
            $('.pagina').click(function() {
                var pagina = $(this).text();
                $('#pagina').val(pagina);
                getData(swt); 
            });

            // Modal editar usuario
            $('.updateUserModal').click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var des = $(this).data('des');
                var cdg = $(this).data('cdg');
                var email = $(this).data('email');

                $.ajax({
                    url: '../api/series_almacenes.php',
                    type: 'POST',
                    async: true,
                    data: { user_id: id },
                    success: function(response) {
                        $('.bodyModal').html(`
                            <form action="" method="post">
                            <h2>Editar Usuario</h2>
                                <input type="hidden" name="id" id="id_user" value="${id}">
                                
                                <div class="form-group">
                                    <label for="usuario">Usuario</label>
                                    <input type="text" name="usuario" id="usuario" placeholder="--------" value="${cdg}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="clave">Contraseña</label>
                                    <input type="password" name="clave" id="clave" placeholder="**********" value="">
                                </div>

                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" name="nombre" id="nombre" placeholder="James Clear" value="${des}">
                                </div>

                                <div class="form-group">
                                    <label for="correo">E-mail</label>
                                    <input type="email" name="correo" id="correo" placeholder="tucorreo@ejemplo.com" value="${email}">
                                </div>

                                <div class="form-group">
                                    <label for="form-select-series">Series:</label>
                                    <a href="#" class="select-all series material-symbols-outlined">Done_All</a>
                                    <select class="form-select-series" name="form-select-series" id="form-select-series" multiple>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="select-serie-default">Serie por defecto:</label>
                                    <select class="select-serie-default" name="select-serie-default" id="select-serie-default">
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="form-select-almacen">Almacenes:</label>
                                    <a href="#" class="select-all almacen material-symbols-outlined">Done_All</a>
                                    <select class="form-select-almacen" name="form-select-almacen" id="form-select-almacen" multiple>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="select-almacen-default">Almacén por defecto:</label>
                                    <select class="select-almacen-default" name="select-almacen-default" id="select-almacen-default">
                                    </select>
                                </div>

                                <div class="alert"></div>
                                <input type="submit" onclick="event.preventDefault();addEditUser();" value="Save" class="btn btn_save material-symbols-outlined" title="Actualizar Usuario">
                                <a onclick="closeModal();" href="#" class="btn closeModal"><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                            </form>
                        `);

                        // Poblar select de series y almacenes
                        response.series.forEach(function(serie) {
                            $('.form-select-series').append(`<option value="${serie.id}">${serie.des_item}</option>`);
                        });
                        response.almacenes.forEach(function(almacen) {
                            $('.form-select-almacen').append(`<option value="${almacen.id}">${almacen.des_item}</option>`);
                        });

                        // Inicializar select2 y seleccionar opciones del usuario
                        $('#form-select-series').select2().val(response.user_series).trigger('change');
                        $('#form-select-almacen').select2().val(response.user_almacenes).trigger('change');

                        // Poblar select predeterminado con opciones seleccionadas en los selects múltiples
                        function populateDefaults() {
                            $('.select-serie-default').empty();
                            $('.select-almacen-default').empty();

                            $('#form-select-series option:selected').each(function() {
                                $('.select-serie-default').append(`<option value="${$(this).val()}">${$(this).text()}</option>`);
                            });

                            $('#form-select-almacen option:selected').each(function() {
                                $('.select-almacen-default').append(`<option value="${$(this).val()}">${$(this).text()}</option>`);
                            });

                            // Mantener seleccionadas las opciones predeterminadas actuales
                            $('.select-serie-default').val(response.user_default_series).trigger('change');
                            $('.select-almacen-default').val(response.user_default_almacen).trigger('change');
                        }

                        // Inicialmente poblar selects predeterminados
                        populateDefaults();

                        // Actualizar selects predeterminados cuando se cambian los selects múltiples
                        $('#form-select-series').on('change', populateDefaults);
                        $('#form-select-almacen').on('change', populateDefaults);

                        // Mostrar el modal
                        $('.modal').fadeIn();
                        document.addEventListener('keydown', handleKeyPress);
                        $('.select-all').click(function() {
                            var selectAll = $(this).data('selectAll');
                            
                            // Check which select element to toggle
                            if ($(this).hasClass('series')) {
                                var options = $('.form-select-series option');
                                var selectElement = '.form-select-series';
                            } else if ($(this).hasClass('almacen')) {
                                var options = $('.form-select-almacen option');
                                var selectElement = '.form-select-almacen';
                            }
                        
                            if (selectAll) {
                                // Deseleccionar todas las opciones
                                options.prop('selected', false);
                                $(this).text('Done_All');
                            } else {
                                // Seleccionar todas las opciones
                                options.prop('selected', true);
                                $(this).text('Remove_Done');
                            }
                            options.trigger('change');
                        
                            // Actualizar Select2 después de cambiar las selecciones programáticamente
                            $(selectElement).trigger('change.select2');
                        
                            // Alternar el estado de selección
                            $(this).data('selectAll', !selectAll);
                        });
                    },
                    error: function(error) {
                        console.error(error.responseText);
                    }
                });
            });

            //Delete user modal
            $('.deleteUserModallrdrii').click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var des = $(this).data('des');
                var cdg = $(this).data('cdg');
                var email = $(this).data('email');

                $('.bodyModal').html(`
                    <form>
                    <h2>Está seguro de querer borrar al siguiente usuario?</h2>
                        <input type="hidden" name="id" id="id_user" value="${id}">
                        <span >Usuario</span>
                        <p id="usuario">${cdg}</p>  
                        <span >Nombre</span>
                        <p id="nombre">${des}</p>
                        <span >E-mail</span>
                        <p id="correo">${email}</p>
                        <div class="alert"></div>
                        <input type="submit" onclick="event.preventDefault();deleteDisabledUserlrdrii();" value="Delete_Forever" class="btn btn_delete material-symbols-outlined" title="Actualizar Usuario">
                        <a onclick="closeModal();" href="#" class="btn closeModal"><span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span></a>
                    </form>
                `);
                // Mostrar el modal
                $('.modal').fadeIn();
                document.addEventListener('keydown', handleKeyPress);
            });
            if(swt == 1){
                $('tr').find('th:last, td:last').remove();
                
            };
        }
    });
}
//add edit user modal
function addEditUser(){
    var id = $('#id_user').val();
    var user = $('#usuario').val();
    var pass = $('#clave').val();
    var name = $('#nombre').val();
    var email = $('#correo').val();
    var series = $('#form-select-series').val();//array
    var almacenes = $('#form-select-almacen').val();//array
    var defaultSerie = $('#select-serie-default').val();
    var defaultAlm = $('#select-almacen-default').val();
    $.ajax({
        url: '../api/modifyUser.php',
        type: 'POST',
        data: {id:id,user:user,pass:pass,name:name,email:email,series:series,almacenes:almacenes,defaultSerie:defaultSerie,defaultAlm:defaultAlm},
        success: function(response) {
            getData();
            var results = JSON.parse(response);
            if(results['status'] =='success'){
                createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Usuario actualizado correctamente  .');
            }else{
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'No se pudo actualizar al usuario.');
            }
            closeModal();
        },error:function(error){
            console.log(error);
        }
    });
}

//Validar contraseña
function validPass(){
    var passNuevo = $('#txtNewPassUser').val();
    var confirmPassNuevo = $('#txtPassConfirm').val();

    if(passNuevo != confirmPassNuevo){
        $('.alertChangePass').html('<p>Las contraseñas no son iguales.</p>');
        $('.alertChangePass').slideDown();
        return false;
    }
    if(passNuevo.length < 6){
        $('.alertChangePass').html('<p>Debe contener como mínimo 6 caractéres</p>');
        $('.alertChangePass').slideDown();
        return false;
    }
        $('.alertChangePass').html('');
        $('.alertChangePass').slideUp();
    
}
//Anular factura
function anularFactura(){
    var noFactura = $('#no_factura').val();
    var action = 'anularFactura';

    $.ajax({
        url: 'ajax.php',
        type: "POST",
        async: true,
        data: {action:action,noFactura:noFactura},

        success: function(response){
            if(response == 'error'){
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'Error al anular el pedido.');
            }else{
                $('#row_'+noFactura+' .estado').html('<span class="anulada">Anulado</span>');
                $('#form_anular_factura .btn_ok').remove();
                $('#row_'+noFactura+' .div_factura').html('<a type="button" class="btn_anular inactive"><span class="material-symbols-outlined">Block</span></a>');
                createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Pedido Anulado.');
            }
        },
        error: function(error){

        }
    })
}

//Tramitar pedido
function tramitarPedido(){
    var noFactura = $('#no_pedido').val();
    var cliente = $('#idcliente').val();
    
    var action = 'tramitarPedido';

    $.ajax({
        url: 'ajax.php',
        type: "POST",
        async: true,
        data: {action:action,noFactura:noFactura,cliente:cliente},

        success: function(response){
            if(response == 'error'){
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'Error al tramitar el pedido.');
            }else{
                $('#row_'+noFactura+' .estado').html('<span class="tramitado">En trámite</span>');
                $('#form_tramitar_pedido .btn_ok').remove();
                $('#row_'+noFactura+' .div_tramitar').html('<a type="button" class="btn_tramite inactive material-symbols-outlined">Task</a>');
                createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Pedido tramitado.');
            }
        },
        error: function(error){

        }
    })
}


//Anular tramite de pedido
function anularTramite(){
    var noFactura = $('#no_pedido').val();
    
    var action = 'anulartramite';

    $.ajax({
        url: 'ajax.php',
        type: "POST",
        async: true,
        data: {action:action,noFactura:noFactura},

        success: function(response){
            if(response == 'error'){
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'No se pudo anular el pedido.');
            }else{
                $('#row_' + noFactura).hide();
                createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Pedido anulado.');
            }
        },
        error: function(error){
            console.error(error);
        }
    })
}

//PDF
function generarPDF(factura,swt=0){
    var ancho = 1000;
    var alto = 800;
    //Calcular posicion x,y para centrar la ventana 
    var x = parseInt((window.screen.width/2) - (ancho/2));
    var y = parseInt((window.screen.height/2) - (alto/2));

    $url = 'factura/generaFactura.php?f='+factura+'&swt='+swt;
    window.open($url,"Factura", "left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
}

function handleAjaxRequest(action, data, callback) {
    $.ajax({
        url: '../api/tables.php',
        type: 'POST',
        async: false,
        data: { action: action, ...data },
        success: function(response) {
            // console.log(response);
            try {
                var parsedResponse = JSON.parse(response);
                
                if (parsedResponse.status !== 'error') {
                    callback(parsedResponse);
                } else {
                    // Mostrar error detallado en el createToast
                    createToast('error', 'fa-solid fa-circle-exclamation', 'Error', parsedResponse.message);
                    callback(null);
                }
            } catch (e) {
                // En caso de error en la respuesta o parsing, mostrar mensaje general
                // createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'Error al procesar la respuesta del servidor.');
                callback(null);
            }
        },
        error: function(error) {
            console.error(error.responseText);
            createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'Error en la solicitud AJAX.');
            callback(null);
        }
    }); 
}
// Function to update table content update nota
function updateTableContent(info, val=0,od=0) {
    console.log(info);
    if (info) {
        console.log('11');
        $('#detalle_venta').html(info.detalle || '');
        $('#detalle_totales').html(info.totales || '');
        attachKeyupEvent();
    } else {
        console.log('22');
        $('#detalle_venta').html('');
        $('#detalle_totales').html('');
    }
    //Detener el bucle cuando solo se necesitan los cambios del detalle, mas no los globales
    if(od==1){
        console.log('od');

        if (typeof cambioPedidoGlobal === 'function') {
            cambioPedidoGlobal();
        }
        if (typeof sendChangesIn === 'function') {
            sendChangesIn();
        }
        viewProcesar();
        updateMonSymbol();
        modifyNumericInput()
        return;
    }
    if (info?.data != null) {
        if (val == 0) {
            // console.log('update pedido',info.data['ruc']);
            if (info.data['fec'] != null && info.data['fec'] !== '') {
                $('#fec_ped').val(info.data['fec'].split(' ')[0]);
            }
            if (info.data['ruc'] != null && info.data['ruc'] !== '') {
                $('#ruc_cliente').val(info.data['ruc']);
                $('#ruc_cliente').trigger('keyup');
            }
            if (info.data['mon'] != null && info.data['mon'] !== '') {
                $('#moneda').val(info.data['mon']);
            }
            if (info.data['cpg'] != null && info.data['cpg'] !== '') {
                $('#cond_pago').val(info.data['cpg']);
            }
            if (info.data['ven'] != null && info.data['ven'] !== '') {
                $('#vendedor').val(info.data['ven']);
            }
            if (info.data['list'] != null && info.data['list'] !== '') {
                $('#lista').val(info.data['list']);
            }
            if (info.data['ref'] != null && info.data['ref'] !== '') {
                $('#ref_cliente').val(info.data['ref']);
            }
            if (info.data['swt']==1) {
                $('#modo_cot').prop('checked', true);
                $('#modo_cot').trigger('change');
            }

            //alerta ajuste igv mayor a 0.05
            if(info.data['alertIgv']??0!=0){
                createToast('warning', 'fa-solid fa-triangle-exclamation', `Advertencia', 'El ajuste automatico del igv varió en: ${info.data['alertIgv']} .`);
            }
            if(info.data['id']??0!=0){
                $('#id_pedido_update').val(info.data['id']);
                $('#id_pedido_update').attr('data-crr',info.data['crr']);
            }
            
            if (info.data['swtIgv'] != null && info.data['swtIgv'] !== '' && info.data['swtIgv'] == 1) {
                $('#checkIgv').prop('checked', true);
                $('#checkIgv').trigger('change')
            }else{
                $('#checkIgv').prop('checked', false);
                $('#checkIgv').trigger('change')
            }  
            disabledForStatus(info.data['SWT_PED']);

        } else if(val==2 || val == 1){
            if (info.data['are'] != null && info.data['are'] !== '') {
                $('#almacen').val(info.data['are']);
            }
            if (info.data['tmov'] != null && info.data['tmov'] !== '') {
                $('#movimiento').val(info.data['tmov']);
            }
            if (info.data['fec'] != null && info.data['fec'] !== '') {
                $('#fec_in').val(info.data['fec'].split(' ')[0]);
            }
            if (info.data['ref'] != null && info.data['ref'] !== '') {
                $('#doc_ref').val(info.data['ref']);
            }
            if (info.data['srr'] != null && info.data['srr'] !== '') {
                $('#input_srr').val(info.data['srr']);
            }
            if (val == 2) {
                if (info.data['ori'] != null && info.data['ori'] !== '') {
                    $('#origen').val(info.data['ori']);
                }
                if (info.data['cct'] != null && info.data['cct'] !== '') {
                    $('#centro').val(info.data['cct']);
                }
                $('.tbl_venta').find('tr').each(function () {
                    $(this).children().eq(3).hide();
                    $(this).children().eq(4).hide();
                });
            }
        }else if(val==3){
            // console.log(info);
            if (info.data['fec'] != null && info.data['fec'] !== '') {
                $('#fec_ped').val(info.data['fec'].split(' ')[0]);
            }
            if (info.data['cli'] != null && info.data['cli'] !== '') {
                $('#idprovee').val(info.data['cli']);
            }
            if (info.data['ruc'] != null && info.data['ruc'] !== '') {
                $('#ruc_prv').val(info.data['ruc']);
            }
            if (info.data['mon'] != null && info.data['mon'] !== '') {
                $('#moneda').val(info.data['mon']);
            }
            if (info.data['cpg'] != null && info.data['cpg'] !== '') {
                $('#cond_pago').val(info.data['cpg']);
            }
            if (info.data['list'] != null && info.data['list'] !== '') {
                $('#lista').val(info.data['list']);
            }
            if (info.data['ven'] != null && info.data['ven'] !== '') {
                $('#vendedor').val(info.data['ven']);
            }
            if(info.data['crr']??0!=0){
                $('#id_orden_update').val(info.data['crr'])
                $('#num_ped').val(info.data['crr'])
            }
            $('#ruc_prv').trigger('keyup');
        }
    }
    $('#almacen').trigger('change');
    viewProcesar();
    updateMonSymbol();
    modifyNumericInput()
}


// Función para eliminar un producto del detalle
function del_product_detalle(correlativo,val) {
    // Mostrar confirmación antes de eliminar
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Quieres eliminar este producto de la lista?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, procedemos a eliminar el producto
            executeDelete(correlativo,val);
            createToast('info', 'fa-solid fa-circle-info', 'Info', 'Se eliminó un producto de la lista.');
        } else {
            // Si el usuario cancela, no hacemos nada
            return;
        }
    });

    // Función para ejecutar la eliminación del producto
    function executeDelete(correlativo,val) {
        var action = 'delProductoDetalle';
        var swtIgv = $('#checkIgv').is(':checked')?1:0;
        var idMped = (value = $('#id_pedido_update').val()) == null || value === '' || value === '0' ? 0 : value;
        var data = { id_detalle: correlativo,val:val,swtIgv:swtIgv,idMped:idMped};
        handleAjaxRequest(action, data, function(info) {
            searchForDetails(val)
            // updateTableContent(info,val);
            
        });
    }
}




// Send data to update temporary table
function sendData(correlativo, des, can, pre, dsc,desc_glob,por_tigv,imp_isc,imp_iceberg,imp_total,obs,val=0,cpg,ven) {
    console.log('change UPDATE');
    //Cada cambio se repite mas veces
    //Actualmnente solo se repite 2 veces
    //Se asigno esta logica por que lo cambios no se implementaban en una sola llamada, al hacerlo 2 veces siempre se modificaban correctamente todos los cambios
    var action = 'updateTemp';
    var swtIgv = $('#checkIgv').is(':checked')?1:0;
    var idMped = (value = $('#id_pedido_update').val()) == null || value === '' || value === '0' ? 0 : value;
    // console.log(ven);
    var data = { id: correlativo, des: des, can: can, pre: pre, dsc: dsc, desc_glob: desc_glob, por_tigv: por_tigv, imp_isc: imp_isc, imp_iceberg: imp_iceberg, imp_total:imp_total, obs:obs, val:val, cpg:cpg, ven:ven, swtIgv:swtIgv, idMped:idMped };
    // console.log(data);
    handleAjaxRequest(action, data, function(info) {
        // updateTableContent(info);
        searchForDetails(0);
    });
    //Bug de actualizacion tardia(despues del cambio siguiente actualiza el anterior) para pedidos
    // handleAjaxRequest(action, data, function(info){updateTableContent(info)});
}
// Send data to update temporary table
function sendDataOrder(desc_glob=0,por_tigv=0,imp_isc=0,imp_iceberg=0,imp_total,obs,cpg,ven, val) {
    var action = 'updateTemp';
    var swtIgv = $('#checkIgv').is(':checked')?1:0;
    var value = $('#id_pedido_update').val();
    var idMped = (!value || isNaN(value) || value <= 0) ? 0 : value;
    console.log(idMped);
    var data = {desc_glob: desc_glob, por_tigv: por_tigv, imp_isc: imp_isc, imp_iceberg: imp_iceberg,imp_total:imp_total,obs:obs,cpg:cpg,ven:ven,swtIgv:swtIgv,idMped:idMped };
    console.log(data);
    handleAjaxRequest(action, data, function(info) {
        if(val=1){
            // updateTableContent(info,0, val);
            searchForDetails(0);
        }
    });
}

function searchForDetails(val=0,crr=0,srr=0,correlativo=0) {
    console.log('search f details',val);
    var action = 'searchForDetails';
    var swtIgv = $('#checkIgv').is(':checked')?1:0;
    var value = $('#id_pedido_update').val();
    var idMped = (!value || isNaN(value) || value <= 0) ? 0 : value;
    var data = {val:val,swtIgv:swtIgv,idMped:idMped ,srr:srr ,crr:crr };
    let onlyDetails = (crr || srr) ?0:1;
    console.log('onlyDetails',onlyDetails,data);
    handleAjaxRequest(action, data, function(info) {
        updateTableContent(info,val,onlyDetails);
        $(`#cantidad-${correlativo}`).trigger('blur');
    });
}




//Mostrar/Ocultar boton procesar
function viewProcesar(){
    if($('#detalle_venta tr').length>0){
        $('#btn_actualizar_pedido,#btn_actualizar_orden,#btn_actualizar_documento').show();
    }else{
        $('#btn_actualizar_pedido,#btn_actualizar_orden,#btn_actualizar_documento').hide();
    }
}


function getUrl(){
    var loc = window.location;
    var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/')+ 1    );
    return loc.href.substring(0,loc.href.length - ((loc.pathname + loc.search + loc.hash).length - pathName.length));
}
function closeModal(){
//limpiar los datos
    $('#txtCantidad').val('');
    $('#txtPrecio').val('');
    $('.modal').fadeOut();
    $('.bodyModal').html('');
    $('#detra').prop('checked',false).trigger('blur');
}
function matchCustom(params, data) {
    // Verificar si params.term y data.text están definidos y no son nulos
    if (!params.term || !data || typeof data.text === 'undefined') {
        return 'ubigeo';
    }
    // Convertir ambos a minúsculas para comparar de manera insensible a mayúsculas y minúsculas
    var searchTerm = params.term.toLowerCase();
    var itemText = data.text.toLowerCase();

    // If there are no search terms, return all of the data
    if ($.trim(searchTerm) === '') {
        return null;
    }

    if (itemText.indexOf(searchTerm) > -1) {
        var modifiedData = $.extend({}, data, true);
        return modifiedData;
    }
    
    return null;
}




function busquedaDinamica(val,idMdoc=0,swtDoc=1){
    $('#busqueda_dinamica').off('keyup');
    //Comprobaciones antes de permitir la busqueda
    $('#busqueda_dinamica').keyup(function() {
        console.log('idtdoc = ',$('#select_tdoc').val());
        var busqueda = $(this).val();
        var action = 'updateProductList';
        var pagina = $('#pagina').val()??1;
        console.log('valores:', action,busqueda,  pagina,  idMdoc, swtDoc, $('#select_tdoc').val());
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: { action: action, busqueda: busqueda, pagina: pagina, idMdoc: idMdoc, swtDoc: swtDoc, idTdoc: $('#select_tdoc').val()},
            success: function(response) {
                // console.log(response);
                updateProductList(response);
            },
            error: function(error) {
                console.error(error);
            }
        });
    });
    $('#busqueda_dinamica').trigger('keyup');
    //Eliminando duplicidad de eventos 
    $('.busqueda_productos').off('click');
    if(idMdoc){
        $('.select_all_prod').html(`<i class="fa-solid fa-check-double" onclick="addAllProductDocument(${idMdoc})" onmouseover="$(this).addClass(\'fa-beat\')" onmouseout="$(this).removeClass(\'fa-beat\')" title="Añadir todos los productos"></i>`);
        //Forma de asignar un evento de click a un item que aun no existe
        $('.busqueda_productos').on('click', '.product-card', function() {
            edicion = true;
            var idTdoc = $('#select_tdoc').val()??0;
            var idProd = $(this).attr('id').split('-')[1];
            var doccli = $(this).data('doccli');
            var mon = $('#moneda').val();
            
            let srr = $('#serie').val();
            let crr = $('#num_ped').val();

            let fec = $('.exchange_date').val();
            console.log(idMdoc);
            $.ajax({
                url: '../api/docTables.php',
                type: 'POST',
                async: true,
                data: { action: 'addProductoDetalle', idProd:idProd, mon:mon, idMdoc:idMdoc, swtDoc:swtDoc, doccli:doccli, srr:srr, crr:crr, fec:fec, idTdoc:idTdoc},
                success: function(response){
                    console.log(response);
                    if (response !== 'error') {
                        searchDetailsDocument();
                        cambioDocGlobal()
                        viewProcesar();
                        modifyNumericInput()
                        $('#busqueda_dinamica').val('');
                        $('#busqueda_dinamica').trigger('keyup');
                        $('#busqueda_dinamica').focus();    
                        attachKeyupEvent();
                    }
                },
                error: function(error) {console.error('Ajax request error:', error);}
            });
        });
        
    }else{
        // $('.product-card').off('click').on('click',function() { //Eventos a cada elemento unico
        //Forma de asignar un evento de click a un item que aun no existe
        $('.busqueda_productos').on('click', '.product-card', function() {
            edicion = true;

            var codproducto = $(this).attr('id').split('-')[1];
            var list = $('#lista').val();
            var mon = $('#moneda').val();
            var cli = $('#idcliente').val();
            
            var swtIgv = $('#checkIgv').is(':checked')?1:0;
            var idMped = $('#id_pedido_update').val()??0;
            // console.log(idMped);
            var action = 'addProductoDetalle';
            sendAjaxRequest(action, { codproducto: codproducto,val:val,list:list,cli:cli,mon:mon,swtIgv:swtIgv,idMped:idMped }, function(response) {
                console.log(response);
                if (response !== 'error') {
                    // updateProductDetails(response,val);
                    searchForDetails(val);
                    window.searchInput.val('');
                    viewProcesar();
                    modifyNumericInput()
                    $('#busqueda_dinamica').trigger('keyup');
                    $('#busqueda_dinamica').focus();

                    updateProductList(''); //Se podria agregar logica adicional else

                    if (typeof cambioPedidoGlobal === 'function') {
                        cambioPedidoGlobal();
                    }
                    if (typeof cambioOcomGlobal === 'function') {
                        cambioOcomGlobal();
                    }
                    attachKeyupEvent();
                    if(val && (typeof sendChangesIn=== 'function')){
                        sendChangesIn();
                    }

                    if(val==2){
                        //Ocultando campos de precio
                        $('.tbl_venta').find('tr').each(function() {
                            $(this).children().eq(3).hide();
                            $(this).children().eq(4).hide();
                        });
                    }
                }
            });
        });
    }
    
}

function seeAllStocks(id) {
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data: {action: 'seeAllStocks', id: id},
        success: function(response) {
            console.log(response);
            let info = JSON.parse(response);
            
            // Grab the first product description
            let desProd = info.length > 0 ? info[0].des_prod : 'No product name available';
            
            // Prepare the stock listing
            let stockList = '';
            info.forEach(function(item) {
                stockList += `
                    <div class="group_modal">
                        <span title="${item.des_item}">${item.abr_item}</span> - 
                        <span>${item.stock}</span>
                    </div>
                `;
            });
            
            // Populate the modal with the product name and stock details
            $('.bodyModal').html(`
                <form class="form_modal">
                    <h2>${desProd}</h2>
                    <div class="group_modal">
                        <strong>ALMACÉN</strong> - <strong>STOCK</strong>
                    </div>
                    ${stockList}
                    <div class="group_modal">
                        <a onclick="closeModal();" href="#" class="btn closeModal">
                            <span class="material-symbols-outlined margin-auto" title="Cerrar">Close</span>
                        </a>
                    </div>
                </form>
            `);
            
            // Show the modal
            $('.modal').fadeIn();
            document.addEventListener('keydown', handleKeyPress);
        },
        error: function(error) {
            console.error(error);
        }
    });
}

function updateProductDetails(response,val) {
    // console.log(response,val)
    var info = JSON.parse(response);
    console.log(info);
    $('#detalle_venta').html(info.detalle);
    $('#detalle_totales').html(info.totales);
    if(val==2){
        //Ocultando campos de precio
        $('.tbl_venta').find('tr').each(function() {
            $(this).children().eq(3).hide();
            $(this).children().eq(4).hide();
        });
    }
    window.searchInput.val('');
    attachKeyupEvent();
    viewProcesar();
    modifyNumericInput()
    $('#busqueda_dinamica').trigger('keyup');
    $('#busqueda_dinamica').off('blur').trigger('blur');
}

// Function to update product list and append navigation
function updateProductList(response) {
    if (response != '') {
        var info = JSON.parse(response);
        $('#product-list').html(info.lista);
        viewProcesar();
        cambiarModoLista();

        attachKeyupEvent();

        // console.log(info.total_paginas, info.pagina_actual);
        appendProductNavigation(info.total_paginas, info.pagina_actual);
        
        if($('#checklista').is(':checked') && ($('#busqueda_dinamica').is(':valid')||$('#busqueda_dinamica').is(':focus'))){
            $('.header_product_list').show()
        }else{
            $('.header_product_list').hide()
        }
    }else{
        $('.paginador').html('');
    }
}
// Function to append navigation at the bottom of the product list
function appendProductNavigation(totalPaginas, paginaActual) {
    var paginador = '';

    if (totalPaginas > 1) {
        paginador += '<input type="hidden" id="pagina"><ul>';

        // First and Previous buttons
        if (paginaActual > 1) {
            paginador += `<a href="#" class="pagina" data-pagina="1">|«</a>`;
            paginador += `<a href="#" class="pagina" data-pagina="${paginaActual - 1}">«</a>`;
        }

        // Page buttons
        for (var i = Math.max(1, paginaActual - 5); i <= Math.min(totalPaginas, paginaActual + 5); i++) {
            var clasePagina = (i === paginaActual) ? 'pagina page-selected' : 'pagina';
            paginador += `<a href="#" class="${clasePagina}" data-pagina="${i}">${i}</a>`;
        }

        // Next and Last buttons
        if (paginaActual < totalPaginas) {
            paginador += `<a href="#" class="pagina" data-pagina="${paginaActual + 1}">»</a>`;
            paginador += `<a href="#" class="pagina" data-pagina="${totalPaginas}">»|</a>`;
        }

        paginador += '</ul>';

        // Append the navigation to the bottom of the product list
        $('.paginador').html(paginador);

        // Attach event listener for navigation buttons
        $('.pagina').click(function(event) {
            event.preventDefault();
            var newPage = Math.max(1, $(this).data('pagina'));
            $('#pagina').val(newPage);
            $('#busqueda_dinamica').trigger('keyup'); // Trigger search to update the list
        });
    }else{
        $('.paginador').html('');
    }
}



function cambiarModoLista() {
    var isChecked = $('#checklista').is(':checked');
    $('.product-list,.product-card, .product-info, .product-info h2, .product-info p').toggleClass('list', isChecked);
    $('.product-card img').toggle(!isChecked);
    
    if($('#checklista').is(':checked') && $('#busqueda_dinamica').is(':valid')){
        $('.header_product_list').show()
    }else{
        $('.header_product_list').hide()
    }
}

function toggleProductList() {
    var hasFocusOrValue = window.searchInput.is(':focus') || window.searchInput.val().length > 0;
    window.productList.slideToggle(150).toggleClass('position', hasFocusOrValue);
}

function enDesarrollo(){
    createToast("info", "fa-solid fa-circle-info", "Info", "En desarrollo.");
}


function anular_documento(swt){
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "swal2-confirm ",
                cancelButton: "swal2-cancel "
            },
        });

        swalWithBootstrapButtons.fire({
            html: "<h2>¿Estás seguro de <strong>cancelar</strong> el documento actual?</h2><p>Esto eliminará tus datos registrados</p>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if(swt==4){
                    realizarAccionAnularDoc(swt)
                }else{
                    realizarAccionAnular(swt);
                }
                
            } else {
                return;
            }
        });
}
function realizarAccionAnular(swt=0,frc=0){//Forzar accion
    var rows = $('#detalle_venta tr').length;
    var idMped = (value = $('#id_pedido_update').val()) == null || value === '' || value === '0' ? 0 : value;
    console.log(swt);
    if(rows > 0 || frc){
        var action = 'anularDocumento';

        $.ajax({
            url: '../api/config.php',
            type: "POST",
            async: false,
            data:{action:action,swt:swt,idMped:idMped},

            success: function(response){
                if(response != 'error'){
                    createToast('info', 'fa-solid fa-circle-info', 'Info', 'Se eliminó el pedido actual.');
                }
                // location.reload();
            },
            error: function(error){

            }
        });
    }
}
function modifyCoin(swt, element) {
    var mon = $(element).val();
    var simbolo = (mon == 3503) ? 'S/.' : '$';
    var fec = $('.exchange_date').val().split(' ')[0];
    var lista = $('#lista').val();
    var idMped = (value = $('#id_pedido_update').val()) == null || value === '' || value === '0' ? 0 : value;
    let tcambio = 0;
    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        async: false,
        data: { action: 'getChangeRate', fec: fec },  // Enviar los datos
        success: function(response) {
            try {
                // Parsear la respuesta JSON
                tcambio = JSON.parse(response)??1;
                console.log(response);
            } catch (e) {
                console.error('Error al parsear la respuesta:', e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud:', status, error);
        }
    });
    
    if($('#detalle_venta tr').length>0){
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "swal2-confirm ",
                cancelButton: "swal2-cancel "
            },
        });

        swalWithBootstrapButtons.fire({
            title: "¿Deseas realizar el cambio de moneda a los precios?",
            html: `Esto actualizará los precios de todos los items a una tasa de: <strong>${tcambio}</strong>.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, actualizar",
            cancelButtonText: "Cancelar",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                let action = 'changeCoinDetails';
                if(swt == 4){
                    action = 'changeCoinDocument';
                }
                let tdoc = $('#select_tdoc').val();
                let srr = $('#serie').val();
                let crr = $('#num_ped').val();
                $.ajax({
                    url: '../api/config.php',
                    type: "POST",
                    async: true,
                    data: {action:action , mon:mon, fec:fec, idMped:idMped, lista:lista, tdoc:tdoc, srr:srr, crr:crr, tcambio:tcambio},
                    success: function(response) {
                        let info = JSON.parse(response);
                        if(info.success==true){
                            createToast('success', 'fa-solid fa-circle-check', 'Éxito', 'Precios modificados correctamente.');
                            if(swt==4){
                                searchDetailsDocument();
                            }else{
                                searchForDetails(0,$('#num_ped').val(),$('#serie').val());
                            }
                        }else{
                            createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'Error en el cambio de moneda.');
                        }
                        $('.simbolo_mon').html(simbolo);
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            } else {
                return;
            }
        });
    }  
} 


function updateMonSymbol(){
    var mon = $('#moneda').val();
    var simbolo = (mon == 3503) ? 'S/.' : '$';
    $('.simbolo_mon').html(simbolo);
}


//Numeracion modificada 
function modifyNumericInput() {
    const $input = $('.numericInput'); 
    let allSelected = false;
    let nextInteger = null;
    let prevVal = ''; 
    let prevPoint = -1; 
    $input.each(function() {
        var decDigits = $(this).data('decimales')??2;
        var val = $(this).val();
        if (val !== '' && !isNaN(val)) {
            var formattedVal = parseFloat(val).toFixed(decDigits);
            $(this).val(formattedVal);
        }
    });
    //Eliminar anteriores manejadores de eventos
    $input.off('focus paste mouseup keyup select input keydown focus'); 
    
    // On focus, store the current value
    $input.on('focus', function() {
        var decDigits = $(this).data('decimales')??2;
        prevVal = $(this).val();
        prevPoint = prevVal.indexOf('.');
    });
    
    $input.on('paste', function(e) {
        var decDigits = $(this).data('decimales')??2;
        e.preventDefault();        
        const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        let cleanPasteData = pasteData.replace(/[^0-9.]/g, '');
        
        // Si hay más de un punto decimal, toma solo el primer punto
        if (cleanPasteData.split('.').length > 2) {
            cleanPasteData = cleanPasteData.split('.').slice(0, 2).join('.');
        }
        // Convierte a flotante
        let number = parseFloat(cleanPasteData);
        
        if (!isNaN(number)) {
            // Formatea el número con los dígitos decimales especificados
            let formattedVal = number.toFixed(decDigits);
            $(this).val(formattedVal);
        } else {
            $(this).val('');
        }
    });

    $input.on('mouseup keyup select', function(event) {
        var decDigits = $(this).data('decimales')??2;
        // console.log(allSelected);
        let val = $(this).val();
        let selectionStart = this.selectionStart;
        let selectionEnd = this.selectionEnd;
        let valueLength = val.length;
    
        // Se seleccionó todo el texto
        if (selectionStart === 0 && selectionEnd === valueLength) {
            allSelected = true;
        }
    
        if (allSelected) {
            allSelected = false;
    
            // Capturar el siguiente número entero
            $(this).one('keydown', function(e) {
                // Capturar la tecla presionada
                let key = e.key;
                
                // Verificar si la tecla presionada es un número
                if (/^\d$/.test(key)) {
                    nextInteger = parseInt(key);
                } else {
                    e.preventDefault();
                    return; 
                }
    
                let newValue = `${nextInteger}.${Array(decDigits).fill('0').join('')}`;
                console.log(newValue);
                $(this).val(newValue);
                e.preventDefault();
                this.setSelectionRange(newValue.indexOf('.') , newValue.indexOf('.'));
            });
        }
    });

    // Handle the input event
    $input.on('input', function(e) {
        var decDigits = $(this).data('decimales')??2;
        let val = $(this).val();
        let curPos = this.selectionStart;

        // Filter the value to allow only digits and a single decimal point
        let cleanVal = val.replace(/[^0-9.]/g, '');
        let firstPoint = cleanVal.indexOf('.');
        let lastPoint = cleanVal.lastIndexOf('.');

        // If there are multiple points, keep the first one and remove others
        if (firstPoint !== lastPoint) {
            cleanVal = cleanVal.substring(0, firstPoint) + cleanVal.substring(lastPoint);
            curPos = firstPoint + 1;
        }

        // Handle the part before and after the decimal
        let intPart = cleanVal.slice(0, firstPoint);
        let decPart = cleanVal.slice(firstPoint + 1);

        // if(decPart.length = 0){
        //     decPart=repeat('0',decDigits);
        // }
        // If no integer part, set it to '0'
        if (!intPart) {
            intPart = '0';
            curPos = curPos + 1;
        }

        // Restrict the decimal part length
        if (decPart.length > decDigits) {
            decPart = decPart.substring(0, decDigits);
        }

        cleanVal = firstPoint >= 0 ? `${intPart}.${decPart}` : intPart;

        $(this).val(cleanVal); // Update the input value

        // Adjust cursor position after formatting
        if (curPos > cleanVal.length) {
            curPos = cleanVal.length;
        }
        if (curPos >= cleanVal.length) {
            if (firstPoint >= 0) {
                curPos = firstPoint + 1; // Coloca el cursor justo después del punto decimal
            } else {
                curPos = cleanVal.length; // Si no hay punto decimal, coloca el cursor al final
            }
        }
        this.setSelectionRange(curPos, curPos);

        prevVal = cleanVal;
        prevPoint = cleanVal.indexOf('.');
    });

    // Prevent deletion of the decimal point
    $input.on('keydown', function(e) {
        let val = $(this).val();
        let curPos = this.selectionStart;
        let pointIdx = val.indexOf('.');
    
        // Si el cursor está al final, y está después del punto decimal, evita mover el cursor
        if (curPos >= val.length) {
            e.preventDefault();
            this.setSelectionRange(pointIdx + 1, pointIdx + 1);
        }
    
        // Evita que se elimine el punto decimal si está siendo presionada la tecla 'Delete' o 'Backspace' justo antes o después del punto decimal
        if ((e.key === 'Delete' && val[curPos] === '.') || (e.key === 'Backspace' && val[curPos - 1] === '.')) {
            e.preventDefault();
        }
    
        // Si se presiona 'Delete' o 'Backspace' en la parte decimal, reemplaza el carácter eliminado por un '0'
        if (pointIdx !== -1 && curPos > pointIdx) {
            if (e.key === 'Delete') {
                e.preventDefault();
                if (val[curPos] !== '.' && curPos < val.length) {
                    $(this).val(val.slice(0, curPos) + '0' + val.slice(curPos + 1));
                    this.setSelectionRange(curPos + 1, curPos + 1);
                }
            }
            if (e.key === 'Backspace') {
                e.preventDefault();
                if (val[curPos - 1] !== '.') {
                    $(this).val(val.slice(0, curPos - 1) + '0' + val.slice(curPos));
                    this.setSelectionRange(curPos - 1, curPos - 1);
                }
            }
        }
    
        // Maneja el escenario en que todos los dígitos enteros son eliminados y queda un '0.' al inicio 
        if (e.key === 'Backspace' && curPos === 1 && val[0] === '0' && val[1] === '.') {
            this.setSelectionRange(curPos - 1, curPos - 1);
            e.preventDefault();
        }
    
        // Maneja el caso cuando se presiona el punto decimal
        if (e.key === '.' && pointIdx !== -1 && curPos > pointIdx) {
            e.preventDefault(); // Evita agregar un nuevo punto decimal si ya hay uno y el cursor está después de él
            this.setSelectionRange(pointIdx + 1, pointIdx + 1); // Mueve el cursor justo después del punto decimal
        }
    
        // Maneja la entrada de caracteres permitidos
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
            // Permite solo números y el punto decimal
            if (/[\d]/.test(e.key)) {
                // Si está en la parte decimal y el cursor no está al final, permite la entrada
                if (pointIdx !== -1 && curPos > pointIdx && curPos < val.length) {
                    const textInput = $(this)[0];
                    const cursorPosition = textInput.selectionStart;
    
                    // Inserta el carácter en la posición del cursor
                    e.preventDefault(); // Previene el comportamiento por defecto
    
                    const textBefore = textInput.value.substring(0, cursorPosition);
                    const textAfter = textInput.value.substring(cursorPosition + 1);
                    textInput.value = textBefore + e.key + textAfter;
                    textInput.selectionStart = textInput.selectionEnd = cursorPosition + 1;
                }
            }
        }
    });
    

    // Additional handling on focus, dblclick, etc.
    $input.on('focus', function() {
        let val = $(this).val();
        let pointIdx = val.indexOf('.');
        if (pointIdx === -1) {
            this.setSelectionRange(val.length, val.length); // No point, move cursor to end
        } else {
            this.setSelectionRange(0, 0); // Move cursor to start if there's a decimal
        }
    });

    
}

function incluidoIgv(){
    //Ejecutar change en detalle tabla, para actualizar precios finales
    updateTableDetail(1);
}

function btnAnularDocumento(val){
    var tdoc = $('#select_tdoc').val();
    var srr = $('#tserie').val();
    var crr = $('#correlativo').val();
    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        async: true,
        data: {action: 'anularDocumentoVal', val : val, tdoc : tdoc, srr : srr, crr : crr},
        success: function(response) {
            console.log(response);
            var data = JSON.parse(response);
            if(data.success){
                createToast('success', 'fa-solid fa-circle-check', 'Éxito', data.message);
            }else{
                createToast('error', 'fa-solid fa-circle-exclamation', 'Error', data.message);
            }
        },
        error: function(error) {
            console.error(error);
        }
    });
}
function btnVerDocumento(val){
    var tdoc = $('#select_tdoc').val();
    var srr = $('#tserie').val();
    var crr = $('#correlativo').val();
    // console.log('tdoc',tdoc,'srr',srr,'crr',crr);
    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        async: true,
        data: {action: 'idDocumentoSrrCrr', val : val, tdoc : tdoc, srr : srr, crr : crr},
        success: function(response) {
            console.log(response);
            var info = JSON.parse(response)
            if(info.success){
                generarPDF(info.id,info.tipo)
            }else{
                //Mandar mensaje de error si no se encuentra el documento
                createToast('info', 'fa-solid fa-circle-info', 'Info', 'No se encontro el documento.');
            }
        },
        error: function(error) {
            console.error(error);
        }
    });
}
function bonusChange(element,crr){
    let precio = parseFloat($(`#precio-${crr}`).val());
    var swt = $(element).hasClass('disabled')?1:0;
    if(!precio && swt){
        createToast('warning', 'fa-solid fa-triangle-exclamation', 'Atención', 'Colocar precio de referencia.');
        return;
    }
    // console.log(swt);
    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        async: true,
        data: {action: 'bonusChange', crr : crr, swt : swt},
        success: function(response) {
            // console.log(response);
            searchForDetails(0,0,0,crr);
            // $(`#cantidad-${crr}`).trigger('blur');
        },
        error: function(error) {
            console.error(error);
        }
    });
}

function disabledForStatus(swt){
    if(swt!=1){
        $('.busqueda_productos').hide();
        $('.datos_venta.finales').hide();
        $('select, input').attr('disabled',true)
        let est = swt == 0 ? 'ANULADO':swt==2?'TERMINADO':'MIGRADO';
        $('.group_checks.estado_ped').html(`<strong class="strong_estado">PEDIDO ${est}</strong>`)
        
        // $('.table-responsive,#detalle_totales').css('pointer-events','none')
        $('#detalle_totales').css('pointer-events','none')
    }else{
        $('.busqueda_productos').show();
        $('.datos_venta.finales').show();
        $('select, input').attr('disabled',false)
        $('.group_checks.estado_ped').html('')
        // $('.table-responsive,#detalle_totales').css('pointer-events','auto')
        $('#detalle_totales').css('pointer-events','auto')
    }
    $('#nom_cliente').attr('disabled','disabled');
    $('#tel_cliente').attr('disabled','disabled');
    $('#dir_cliente').attr('disabled','disabled');
}

function checkCodFormAfter() {
    const $codEqv = $('#check_cod_eqv');
    const $codProd = $('#check_cod_prod');

    if ($codEqv.length || $codProd.length) {
        function toggleSections(swt_codform) {
            if (swt_codform === 1) {
                $('.cdg_prod').show();
                $('.eqv_prod').hide();
            } else {
                $('.cdg_prod').hide();
                $('.eqv_prod').show();
            }
        }

        $codEqv.add($codProd).off('change').on('change', function () {
            const swt_codform = $codEqv.is(':checked') ? 2 : 1;

            $.ajax({
                url: '../api/config.php',
                type: 'POST',
                data: { action: 'updateCodForm', swt_codform: swt_codform },
                success: function () {
                    console.log(`swt_codform actualizado a ${swt_codform}`);
                },
                error: function (error) {
                    console.error(`Error al actualizar swt_codform a ${swt_codform}`, error);
                }
            });

            toggleSections(swt_codform);
        });

        $.ajax({
            url: '../api/config.php',
            type: 'POST',
            async: true,
            data: { action: 'consultarCodForm' },
            success: function (response) {
                const swt_codform = (response === '2') ? 2 : 1;
                $codEqv.prop('checked', swt_codform === 2);
                $codProd.prop('checked', swt_codform === 1);

                toggleSections(swt_codform);
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
}

//cerrar modal con tecla escape
function handleKeyPress(event) {
    // Verificar si la tecla presionada es Escape (keyCode 27 o key 'Escape')
    if (event.keyCode === 27 || event.key === 'Escape') {
        closeModal(); // Llamar a la función closeModal() cuando se presiona Escape
    }
}