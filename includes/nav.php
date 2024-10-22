<nav class="sidebar__nav" id="sidebar">
    <ul>
        <div class="nav_E">
            <?php 
                include "../conexion.php"; 
                $query = mysqli_query($conection,"SELECT ruc, nombre FROM configuracion");
                $row = mysqli_fetch_assoc($query);
            ?>
            <h2 class="iniciales"><?php 
                $nombre = $row['nombre'];
                $iniciales = '';
                $palabras = explode(' ', $nombre);
                foreach ($palabras as $palabra) {
                    $iniciales .= strtoupper(substr($palabra, 0, 1));
                }
                echo $iniciales; 
            ?></h2>
            <div class="logo-details">
                <div class="logo_name"><?= $row['nombre']?></div>
            </div>
            <i class="bx bx-menu" id="btn"></i>
        </div>
        <div class="line"></div> 
        <li class="principal sidebar__item">
            <a class="material-symbols-outlined" id="a_search" href="#">Search
                <span class="span_principal" id="span_buscar">Buscar</span>
            </a>
            <input id="input_search" type="text" placeholder="Aplicación...">
            <div class="search_modules">
                <ul id="modules_list">
                    <?php 
                        $idUser = $_SESSION[$_SESSION['db'].'idUser'];
                        $query = mysqli_query($conection, "SELECT p.id, p.des_item, p.frm_item, p.icon_item FROM d_dopcion p INNER JOIN d_usuari u ON u.id_dopcion = p.id WHERE p.id_mopcion IN (SELECT m.id  FROM m_opcion m WHERE swt_opc = 1) AND p.id_dopcion IN (SELECT num_item FROM d_opcion WHERE id_mopcion = p.id_mopcion AND num_item = p.id_dopcion AND swt_item = 1)  AND p.swt_opc = 1 AND u.id_musuari = $idUser ORDER BY p.des_item LIMIT 8;");
                        $result = mysqli_num_rows($query);
                        if($result>0){
                            while ($data = mysqli_fetch_array($query)){
                                ?>
                                <li class="principal sidebar__item">
                                    <a class="material-symbols-outlined" data-iddopcion="<?=$data['id']?>" href="<?=$data['frm_item']?>"><?=$data['icon_item']?><span class="span_principal"><?=$data['des_item']?></span></a>
                                </li>
                                <?php
                            }
                        }
                    ?>
                </ul>
            </div>
        </li>
        <li class="principal sidebar__item">
            <a class="material-symbols-outlined" href="index">Home<span class="span_principal">Inicio</span></a>
        </li>

<?php 
    $idUser = $_SESSION[$_SESSION['db'].'idUser'];
    $query_module = mysqli_query($conection, "SELECT DISTINCT(m.id) as id, m.des_opc, m.icon_opc FROM d_opcion p INNER JOIN d_dopcion dd ON p.num_item = dd.id_dopcion INNER JOIN d_usuari up ON up.id_dopcion = dd.id  INNER JOIN m_opcion m ON m.id = dd.id_mopcion AND p.id_mopcion = m.id WHERE up.id_musuari = $idUser AND m.swt_opc = 1 AND p.swt_item = 1 ORDER BY id;");
    $result = mysqli_num_rows($query_module);
    if($result>0){
        while ($data = mysqli_fetch_array($query_module)){
            ?>
            <li class="principal sidebar__item">
                <a class="material-symbols-outlined main" href="#"><?=$data['icon_opc']?>
                    <span class="span_principal"><?=$data['des_opc']?></span>
                    <!-- <p class="material-symbols-outlined">Arrow_Right</p> -->
                </a>
                <ul class="ul_sec">
                    <?php 
                        $mOpcion = $data['id'];
                        // $query_dopcion = mysqli_query($conection, "SELECT DISTINCT p.id,p.num_item, p.des_item, p.icon_item FROM d_opcion p INNER JOIN m_opcion m ON m.id = p.id_mopcion INNER JOIN d_dopcion dd ON dd.id_mopcion = m.id AND dd.id_dopcion = p.num_item INNER JOIN d_usuari u ON u.id_dopcion = dd.id WHERE p.id_mopcion = $mOpcion AND p.swt_item = 1 AND u.id_musuari = $idUser;");
                        $query_dopcion = mysqli_query($conection, "SELECT p.id, p.num_item, p.des_item, p.icon_item,
                                                  MIN(dd.frm_item) AS frm_item,
                                                  COUNT(dd.id) AS suma_opcion
                                           FROM d_opcion p
                                           INNER JOIN m_opcion m ON m.id = p.id_mopcion
                                           INNER JOIN d_dopcion dd ON dd.id_dopci = p.id
                                           INNER JOIN d_usuari u ON u.id_dopcion = dd.id
                                           WHERE p.id_mopcion = $mOpcion AND p.swt_item = 1 AND dd.swt_opc = 1 AND u.id_musuari = $idUser
                                           GROUP BY p.id, p.num_item, p.des_item, p.icon_item
                                           ORDER BY m.id;");

                while ($data_dopcion = mysqli_fetch_array($query_dopcion)) {
                    $dOpcion = $data_dopcion['num_item'];
                ?>
                    <li class="sidebar__item secundario principal">
                        <?php if ($data_dopcion['suma_opcion'] > 1) { ?>
                            <a class="material-symbols-outlined sec final" href="#">
                                <p class="material-symbols-outlined">Arrow_Right</p>
                                <span class="span_secundario"><?=$data_dopcion['des_item']?></span>
                                <ul class="ul_terc">
                                    <?php 
                                    $query_ddopcion = mysqli_query($conection, "SELECT p.num_cor, p.des_item, p.frm_item, p.icon_item 
                                                                                FROM d_dopcion p 
                                                                                INNER JOIN d_usuari u ON u.id_dopcion = p.id 
                                                                                WHERE p.id_mopcion = $mOpcion AND p.id_dopcion = $dOpcion AND p.swt_opc = 1 AND u.id_musuari = $idUser 
                                                                                ORDER BY p.num_cor");
                                    
                                    while ($data_ddopcion = mysqli_fetch_array($query_ddopcion)) { ?>
                                        <li class="sidebar__item terciario">
                                            <a class="material-symbols-outlined" href="<?=$data_ddopcion['frm_item']?>"><?=$data_ddopcion['icon_item']?>
                                                <span class="span_terciario"><?=$data_ddopcion['des_item']?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </a>
                        <?php } else { ?>
                            <a class="material-symbols-outlined sec" href="<?=$data_dopcion['frm_item']?>">
                                <p class="material-symbols-outlined"></p>
                                <span class="span_secundario"><?=$data_dopcion['des_item']?></span>
                            </a>
                        <?php } ?>
                    </li>
                <?php } ?>
                </ul>
            </li>
            <?php
        }
    }
?>
    </ul>
    <div class="toggle"><span class="material-symbols-outlined dark_mode">Dark_Mode</span><span class="material-symbols-outlined light_mode">Light_Mode</span></div>
</nav>

<script>
    const body = document.querySelector('body');
    const toggleButton = document.querySelector('.toggle');
    const darkModeSpan = document.querySelector('.dark_mode');
    const lightModeSpan = document.querySelector('.light_mode');

    // Aplica la preferencia almacenada al cargar la página
    if (localStorage.getItem('dark-mode') === 'enabled') {
        body.classList.add('dark');
        darkModeSpan.style.display = 'none'; // Oculta el texto "Dark Mode"
    } else {
        lightModeSpan.style.display = 'none'; // Oculta el texto "Light Mode"
    }

    toggleButton.onclick = function() {
        body.classList.toggle('dark');

        if (body.classList.contains('dark')) {
            localStorage.setItem('dark-mode', 'enabled');
            darkModeSpan.style.display = 'none';
            lightModeSpan.style.display = 'inline';
        } else {
            localStorage.setItem('dark-mode', 'disabled');
            darkModeSpan.style.display = 'inline';
            lightModeSpan.style.display = 'none';
        }
    }


    let isHovering = false;

// Agregar evento al botón de cerrar y al botón de búsqueda
$("#btn, #a_search").click(function() {
    $(".sidebar").toggleClass("open");
    $(".sidebar__item").toggleClass("open");

    // if ($(".span_principal").hasClass("menu")) {
    //     $(".span_principal").removeClass("menu");
    //     $(".span_secundario").removeClass("menu");
    //     $(".span_terciario").removeClass("menu");
    //     $(".ul_sec").removeClass("menu");
    //     $("#span_buscar").show();
    //     $("#input_search").hide();
    //     $("#a_search").show();
    // } else {
        $(".span_principal").addClass("menu");
        $(".span_secundario").addClass("menu");
        $(".span_terciario").addClass("menu");
        $(".ul_sec").addClass("menu");
        $("#span_buscar").hide();
        $("#input_search").show();
        $("#a_search").hide();
    // }

    // Mover la definición del click handler fuera de la función click del botón
    $(".sidebar__item").off('click').on('click', function() {
        if (!isHovering) {
            $(this).find('.ul_sec').toggle(); // Aplica toggle al hijo `.ul_sec`
        }
    });

    // Opacidad al h2 LS
    if ($("h2.iniciales").hasClass("opacity")) {
        $("h2.iniciales").removeClass("opacity");
    } else {
        $("h2.iniciales").addClass("opacity");
    }
    menuBtnChange(); // Llamar a la función para cambiar el botón del menú (opcional)
});

// Cambiar el botón del menú (opcional) sidebar menu open btn 
function menuBtnChange() {
var closeBtn = $("#btn");
if ($(".sidebar").hasClass("open")) {
    closeBtn.removeClass("bx-menu").addClass("bx-menu-alt-right");
    $('#container, .container').click(function(){
            // Mover la verificación dentro del evento click del contenedor
            if ($(".sidebar").hasClass("open")) {
                closeBtn.trigger('click');
            }
        });
} else {
    closeBtn.removeClass("bx-menu-alt-right").addClass("bx-menu");
}
}


// Manejar el evento hover
$(".ul_sec li.secundario").hover(
    function(event) {
        isHovering = true; // Cuando el mouse está sobre el elemento
    },
    function(event) {
        isHovering = false; // Cuando el mouse deja el elemento
    }
);

// Corrected event handler for .ul_sec li.secundario a click
$(".ul_sec li.secundario a.sec").click(function(event) {
    // event.preventDefault();

    // Search for the next .ul_terc sibling instead of using .find()
    const ulTerc = $(this).next(".ul_terc");
    if (ulTerc.hasClass("menu")) {
        ulTerc.removeClass("menu");
    } else {
        ulTerc.addClass("menu");
    }
});





//input buscar navegacion

$(document).ready(function() {
    
    
    var $searchInput = $('#input_search');
    var $modules = $('.search_modules');

    function toggleProductList() {
        var query = $searchInput.val().trim();
        if ($searchInput.is(':focus') || query !== '') {
            $modules.slideDown(100);
            $modules.slideDown(100).addClass('position');
        } else {
            $modules.slideUp(0).removeClass('position');
            $modules.slideUp(0);
        }
    }
    $searchInput.on('focus', toggleProductList);
    $searchInput.off('blur').on('blur', function() {
        setTimeout(toggleProductList, 200); // Timeout to wait for click events on products
        }
    );

    // Búsqueda dinámica
    $('#input_search').keyup(function(e) {
        dynamicSearch();
    });


    

});

function handleFavButtonClick(event) {
    if (!$('#btn-fav').hasClass('hovered')) {
        return; // No hacer nada si #btn-fav no tiene la clase 'hovered'
    }
    var cancel = false;

    $('#input_search').focus();
    $('#input_search').addClass('fav');

    $('#container, .container').click(function() {
        $('#btn').trigger('click');
        $('#input_search').removeClass('fav');
        $('#btn-fav').removeClass('hovered');
        cancel = true;
    });

    if (cancel === true) {
        return;
    }

    $('.search_modules #modules_list a').off('click').click(function(event) {
        if (cancel === true) {
            return;
        }
        var text = $(this).find('span').text();
        var oldHref = $(this).attr('href');
        $(this).attr('href', '#');
        event.preventDefault();
        var idDopc = $(this).data('iddopcion');

        var link = $(this); // Guardar el contexto de `this`

        $.ajax({
            url: '../api/config.php',
            type: "POST",
            async: true,
            data: {action: 'addNewFav', idDopc: idDopc},
            success: function(response) {
                fillFavorites();
                link.attr('href', oldHref); // Usar el contexto guardado
                createToast('success', 'fa-solid fa-circle-check', text, 'Se añadió a la lista de favoritos.');
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    });
}

function dynamicSearch() {
    var busqueda = $('#input_search').val();
    var action = 'updateModuleList';
    $.ajax({
        url: 'ajax.php',
        type: "POST",
        async: true,
        data: {action: action, busqueda: busqueda},
        success: function(response) {
            if(response != 'error') {
                var info = JSON.parse(response);
                // Actualizar tabla
                $('#modules_list').html(info.lista);
                $('#modules_list li').show();

                // Llamar a handleFavButtonClick si #btn-fav tiene la clase 'hovered'
                handleFavButtonClick();
            }
        },
        error: function(error) {
            console.log('Error:', error);
        }
    });
}




</script>
