<?php include "includes/updatePermissions.php";?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "includes/scripts.php";?>
	
	<title>ERP NOW</title>
</head>
<body>
<?php include "includes/header.php";
	include "../conexion.php";
	//Datos empresa
	$ruc = '';
	$nombreEmpresa = '';
	$razonSocial = '';
	$telEmpresa = '';
	$emailEmpresa ='';
	$dirEmpresa = '';
	$igv='';

	$query_empresa = mysqli_query($conection,"SELECT * FROM configuracion");
	$row_empresa = mysqli_num_rows($query_empresa);
	if($row_empresa > 0){
		while($arrInfoEmpresa = mysqli_fetch_array($query_empresa)){
			$ruc = $arrInfoEmpresa['ruc'];
			$nombreEmpresa = $arrInfoEmpresa['nombre'];
			$razonSocial = $arrInfoEmpresa['razon_social'];
			$telEmpresa = $arrInfoEmpresa['telefono'];
			$emailEmpresa = $arrInfoEmpresa['email'];
			$dirEmpresa = $arrInfoEmpresa['direccion'];
			$igv = $arrInfoEmpresa['igv'];
		}
	}

	$query_dash = mysqli_query($conection,"CALL dataDashboard();");
	$result_dash = mysqli_num_rows($query_dash);
	if($result_dash > 0){
		$data_dash = mysqli_fetch_assoc( $query_dash );
		mysqli_close($conection);
	}
	

?>
	
	<section id="container" class="container">
		<section class="main-container">
		<div class="tab-nav-bar">
		<div class="tab-navigation">
			<ion-icon class="left-btn material-symbols-outlined" name="chevron-back-outline">Arrow_Back_iOs</ion-icon>
			<ion-icon class="right-btn material-symbols-outlined" name="chevron-forward-outline">Arrow_Forward_iOS</ion-icon>

			<ul class="tab-menu">
			<?php 
				include "../conexion.php";
						//Guardando la variable m_opcion
						//  $mOpcion = $data['id'];
						//  $query_dopcion = mysqli_query($conection, "select des_opc,id from m_opcion where swt_opc=1;");
						$idUser = $_SESSION[$_SESSION['db'].'idUser'];
						$query_dopcion = mysqli_query($conection, "SELECT * FROM 
	(
	SELECT DISTINCT(m.id) as id, m.des_opc, m.icon_opc FROM d_opcion p INNER JOIN d_dopcion dd ON p.num_item = dd.id_dopcion INNER JOIN d_usuari up ON up.id_dopcion = dd.id  INNER JOIN m_opcion m ON m.id = dd.id_mopcion  AND p.id_mopcion = m.id  WHERE up.id_musuari = $idUser AND m.swt_opc = 1 AND p.swt_item = 1
	
	UNION ALL
	SELECT 0 id, 'FAVORITOS' des_opc, '' icon_opc
		) A ORDER BY A.id;");
						mysqli_close($conection);
						$active='';
						while ($data = mysqli_fetch_array($query_dopcion)){
							
							$active='';
							//Mostrando sub modulos
							if ($data['id']==0)
							{
								$active=" active";
							}
							?>
							
							<li id="<?=$data['id']?>" class="tab-btn<?=$active?>">
								<?=$data['des_opc']?>
							</li>
						
						<?php } ?>
			</ul>
			</div>
		</div>

		<div class="tab-content" id="tab_content">
    <div id="ddopcion_list">
    </div>
	<button class="btn btn-fav material-symbols-outlined" id="btn-fav" href="#" title="Agregar favoritos">Star</button>
    <div class="tab active tab-fav">
        
    </div>
</div>

		</section>

		<div class="container_title">
			<h1>Panel de control</h1>
		</div>
		<div class="container_dashboard">

			<a href="buscar_cliente">
				<span class="material-symbols-outlined">Groups</span>
				<h3>Clientes</h3>
				<p><?= $data_dash['clientes'];  ?></p>
			</a>
			<a href="buscar_productos.php?busqueda=">
				<span class="material-symbols-outlined">Widgets</span>
				<h3>Productos</h3>
				<p><?= $data_dash['productos'];  ?></p>
			</a>
			<a href="buscar_usuario">
				<span class="material-symbols-outlined">Groups</span>
				<h3>Usuarios</h3>
				<p><?= $data_dash['usuarios'];  ?></p>
			</a>
		</div>
		<div class="container_title">
				<h2>Configuracion</h2>
			</div>
		<div class="infoSistem">
			
			<div class="containerData">
				<div class="logoUser">
					<img src="img/user.png" alt="logo_usuario">
				</div>
				<div class="data_user">
					<h3>Información personal</h3>
					<div>
						<p>Nombre:</p><span><?= $_SESSION[$_SESSION['db'].'nombre']; ?></span>
					</div>
					<div>
						<p>Correo:</p><span><?= $_SESSION[$_SESSION['db'].'correo']; ?></span>
					</div>
					<div>
						<p>Usuario:</p><span><?= $_SESSION[$_SESSION['db'].'usuario']; ?></span>
					</div>
					<h3>Cambiar contraseña</h3>
					<form action="" method="post" name="frmChangePass" id="frmChangePass"><div>
						<!-- <label style="display: none;" for="username">Nombre de Usuario</label> -->
						<!-- <input style="display: none;" type="text" id="username" name="username" autocomplete="username" required> -->
					</div>
					<div>
						<input type="password" name="txtPassUser" id="txtPassUser" placeholder="Contraseña actual" autocomplete="new-password">
						</div>
					<div>
						<input class="newPass" type="password" name="txtNewPassUser"   id="txtNewPassUser" placeholder="Nueva contraseña " required autocomplete="new-password">
					</div>
					<div>
						<input class="newPass" type="password" name="txtPassConfirm"   id="txtPassConfirm" placeholder="Confirmar contraseña" required autocomplete="new-password">
					</div>
					<div class="alertChangePass displaynone"></div>
					
						<button type="submit" class="btn btnChangePass" title="Cambiar contraseña"><p class="material-symbols-outlined">Key</p>Aceptar</button>
					

					</form>
				</div>
			</div>
			<?php if($_SESSION[$_SESSION['db'].'rol'] == 1){ ?>
			<div class="containerDataE">
				<div class="logoUser">
					<img src="img/enterprise.png" alt="logo_usuario">
				</div>
				<h3>
				<form action="" method="post" name="frmEmpresa" id="frmEmpresa">
					<input type="hidden" name="action" value="updateDataEmpresa">
					<div>
						<p>RUC: </p><input type="text" name="txtRuc" id="txtRuc" placeholder="RUC de la empresa" value="<?=$ruc;?>" required>
					</div>
					<div>
						<p>Nombre: </p><input type="text" name="txtNombre" id="txtNombre" placeholder="Nombre de la empresa" value="<?=$nombreEmpresa;?>" required>
					</div>
					<div>
						<p>Razon Social: </p><input type="text" name="txtRSocial" id="txtRSocial" placeholder="Razon social de la empresa" value="<?=$razonSocial;?>" >
					</div>
					<div>
						<p>Teléfono o celular: </p><input type="text" name="txtTelEmpresa" id="txtTelEmpresa" placeholder="Número de teléfono" value="<?=$telEmpresa;?>" required>
					</div>
					<div>
						<p>Correo electrónico: </p><input type="text" name="txtEmailEmpresa" id="txtEmailEmpresa" placeholder="lzzdesarrollo@gmail.com" value="<?=$emailEmpresa;?>" required>
					</div>
					<div>
						<p>Dirección: </p><input type="text" name="txtDirEmpresa" id="txtDirEmpresa" placeholder="275 Av. Brasil, Jesus María, Lima Perú" value="<?=$ruc;?>" required>
					</div>
					<div>
						<p>IGV(%): </p><input type="text" name="txtIgv" id="txtIgv" placeholder="18%" value="<?=$igv;?>" disabled required>
					</div>
					<div class="alertFormEmpresa displaynone"></div>
					<div>
						<button type="submit" class="btn btn_save btnChangePass" title="Guardar datos"><span class="material-symbols-outlined">Save</span></button>
					</div>
				</form>
				
			</div>
			<?php } ?>
		</div>
	</section>
	<div id="cookie-banner" class="cookie-banner">
    <p>
        Este sitio web utiliza cookies para mejorar la experiencia del usuario. Al utilizar nuestro sitio web, aceptas todas las cookies de acuerdo con nuestra <a href="politica-de-cookies.php" target="_blank">política de cookies</a>.
    </p>
    <button onclick="acceptCookies()" class="accept-cookies-btn">Aceptar</button>
</div>
	<?php include "includes/footer.php";?>
</body>
<script>
function acceptCookies() {
	// Ocultar el banner de cookies
	$("#cookie-banner").hide();
	// Establecer una cookie para registrar el consentimiento del usuario
	document.cookie = "cookies_accepted=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/";
}

// Función para verificar si el usuario ha aceptado las cookies al cargar la página
$(window).on("load", function() {
	var cookiesAccepted = document.cookie.split(';').some((item) => item.trim().startsWith('cookies_accepted=true'));
	if (cookiesAccepted) {
		// El usuario ha aceptado las cookies, ocultar el banner
		$("#cookie-banner").hide();
	}
});

$(document).ready(function() {
	fillFavorites();
	fillModule();
	// Botón favorito
	$('#btn-fav').click(function(event){
		event.stopPropagation();
		$('#btn').trigger('click');
		if(!$(this).hasClass('hovered')){
			$(this).addClass('hovered');
			handleFavButtonClick(event);
		}else{
			$(this).removeClass('hovered');
		}
		
	});

	

	$('.tab-btn').click(function(){
    // Guardar la opción seleccionada en el localStorage
    const selectedTabId = $(this).attr('id');
    localStorage.setItem('selectedTab', selectedTabId);
		// console.log(selectedTabId);
    // Cambiar el contenido según la pestaña seleccionada
    $('.card').removeClass('active');
    $('.card').show();
    $('#ddopcion_list').empty();
		$('#ddopcion_list').hide();
    $('.card .box .content a.btn_view_ddopcion').html('Visibility');
    
    if(selectedTabId != 0){
        $('#btn-fav').hide();
    } else {
        $('#btn-fav').show();
    }
	});
	
});
function fillFavorites() {
    $.ajax({
        url: '../api/config.php',
        type: "POST",
        data: { action: 'fillFavorites', idUser: 1 }, // Asume que el idUser se envía como parte de la solicitud
        dataType: 'json', // Asegura que la respuesta se interprete como JSON
        success: function(response) {
            if (response.error) {
                console.error(response.error);
                return;
            }

            var $tabFav = $('.tab-fav');
            $tabFav.empty(); // Vaciar contenido anterior

            response.forEach(function(module) {
                var $div = $(`
			<a class="card" href="${module.frm_item}">
				<div class="box">
					<i href="#" class="icon icon_delete material-symbols-outlined" data-idfav="${module.id}" onClick="event.preventDefault();deleteFav(this)">Delete</i>
					<div class="content">
						<div class="icon material-symbols-outlined">${module.icon_item}</div>
						<h3>${module.des_item}</h3>
					</div>
				</div>
			</a>
                `);
                $tabFav.append($div);
            });

			
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}

function fillModule() {
    var ids = $('.tab-menu li').map(function() { return this.id; }).get();
    if (ids.length > 0 && ids[0] === "0") {
        ids.shift();
    }
    $.ajax({
        url: '../api/config.php',
        type: "POST",
        data: { action: 'fillModules', ids: ids },
        success: function(response) {
            var data = JSON.parse(response);
            var modulesByMId = {};

            // Group modules by idM
            data.forEach(item => {
                if (!modulesByMId[item.idM]) {
                    modulesByMId[item.idM] = [];
                }
                modulesByMId[item.idM].push(item);
            });

            // Iterate over the grouped modules and display them on the page
            for (const idM in modulesByMId) {
                if (Object.prototype.hasOwnProperty.call(modulesByMId, idM)) {
                    const modules = modulesByMId[idM];

                    // Create a new tab for each idM
                    var $tab = $('<div class="tab"></div>');
                    $('#tab_content').append($tab);

                    // Iterate over the modules and add them to this tab
                    modules.forEach(module => {
                        // Create the card and box
                        var $card = $(`<a class="card ${module.id}"></a>`);
                        var $box = $(`<div class="box"></div>`);

                        // Add the module content to the card
                        $box.append(`
                            <div class="content">
                                <div class="icon material-symbols-outlined">${module.icon_item}</div>
                                <h3>${module.des_item}</h3>
                            </div>
                        `);

                        // Add the button depending on the counter in the card
                        if (module.contador == 1) {
                            $card.attr('href', module.frm_item);
                            $card.attr('title', 'Ir');
                        } else {
                            $card.attr('href', '#');
                            $card.attr('data-idcard', module.id);
                            $card.attr('data-mopcion', module.num_item);
                            $card.addClass('btn_view_ddopcion');
                            $card.attr('title', 'Ver');
                        }

                        // Add the box to the card and then add the card to the container
                        $card.append($box);
                        $tab.append($card);
                    });
                }
            }

            // Navigation
            const $btnLeft = $(".left-btn");
            const $btnRight = $(".right-btn");
            const $tabMenu = $(".tab-menu");

            const iconVisibility = () => {
                let scrollLeftValue = Math.ceil($tabMenu.scrollLeft());
                let scrollableWidth = $tabMenu[0].scrollWidth - $tabMenu[0].clientWidth;
                $btnLeft.toggle(scrollLeftValue > 0);
                $btnRight.toggle(scrollableWidth > scrollLeftValue);
            };

            $btnRight.click(() => {
                $tabMenu.scrollLeft($tabMenu.scrollLeft() + 150);
                setTimeout(iconVisibility, 50);
            });

            $btnLeft.click(() => {
                $tabMenu.scrollLeft($tabMenu.scrollLeft() - 150);
                setTimeout(iconVisibility, 50);
            });

            const updateButtonVisibility = () => {
                $btnRight.toggle($tabMenu[0].scrollWidth > $tabMenu[0].clientWidth);
                $btnLeft.toggle($tabMenu.scrollLeft() > 0);
            };

            $(window).on("load resize", updateButtonVisibility);

            // Draggable tab navigation
            let activeDrag = false;

            $tabMenu.on("mousedown", function() {
                activeDrag = true;
            });

            $(document).on("mouseup", function() {
                activeDrag = false;
                $tabMenu.removeClass("dragging");
            });

            // View tab contents on click tab buttons
            const $tabs = $(".tab");
            const $tabBtns = $(".tab-btn");

            const tab_Nav = function(tabBtnClick) {
                $tabBtns.removeClass("active");
                $tabs.removeClass("active");
                $tabBtns.eq(tabBtnClick).addClass("active");
                $tabs.eq(tabBtnClick).addClass("active");
            };

            $tabBtns.each(function(i) {
                $(this).on("click", function() {
                    tab_Nav(i);
                });
            });

            $('.btn_view_ddopcion').click(handleButtonClick);
						const storedTabId = localStorage.getItem('selectedTab');
    if (storedTabId) {
        $(`#${storedTabId}`).trigger('click');
				// console.log(`#${storedTabId}`);
    }
        },
        error: function(error) {
            console.error(error);
        }
    });
}

function deleteFav(element) {
    var idFav = $(element).data('idfav');
	$.ajax({
            url: '../api/config.php',
            type: "POST",
            async: true,
            data: {action: 'deleteFav', idFav: idFav},
			dataType:"json",
            success: function(response) {
                fillFavorites();
				createToast('info', 'fa-solid fa-circle-info', response.des_item, 'Fué eliminado correctamente de favoritos.');
            },
            error: function(error) {
                console.log('Error:', error);
            }
        }); 
}
// Define the reusable function
function handleButtonClick(event) {
    event.preventDefault();
	if($(this).hasClass('open')){
		$(this).removeClass('open');
		$('.card').removeClass('active');
		$('.card').show();
		$('#ddopcion_list').empty();
		$('#ddopcion_list').hide();
	
	}else{
	$(this).addClass('open')
    var idmodule = $('.tab-btn.active').attr('id');
    var idopcion = $(this).data('mopcion');
    var idCard = $(this).data('idcard');
    var action = 'viewDdopcion';
	$('.card').removeClass('active');
	$('.card').hide();
	$('.card.'+idCard).addClass('active');
	$('.card.'+idCard).show();
	$('#ddopcion_list').show();

    $.ajax({
        url: '../api/config.php',
        type: 'POST',
        async: true,
        data: { action: action, idmodule: idmodule, idopcion: idopcion },
        success: function(response) {
            var info = JSON.parse(response);
            var container = $('#ddopcion_list'); 
            container.empty(); 
            // Create a list container
            var listContainer = $('<ul>', { id: 'itemList' });

            // Loop through the response and create list items
            info.forEach(function(item) {
                var listItem = $('<li>');
                if (!item.frm_item) {
					item.frm_item = '#'
				}
				var link = $('<a>', {
					href: item.frm_item,
					text: item.des_item,
					click: function(e) {
						e.preventDefault();
						window.location.href = item.frm_item; // Redirect to frm_item link
					}
				});
				listItem.append(`<span class="material-symbols-outlined">${item.icon_item}</span>`);
				listItem.append(link);
			
                listContainer.append(listItem);
            });
            container.append(listContainer);
			//MODAL AND animation

        },
        error: function(error) {
            console.log(error);
        }
    });
	}
	
}


</script>

</html>