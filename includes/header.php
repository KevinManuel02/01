<?php 
if (empty($_SESSION[$_SESSION['db'].'active'])) {// Verifica si la sesión está activa
	header('location: ../index');
	session_destroy();

	exit;
}
// Verifica si la cookie 'user_key' está seteada
date_default_timezone_set('America/Lima'); 

if (!empty($_COOKIE['user_key'])) {
	// Obtiene el valor de la cookie 'user_key'
	$user_key_cookie = $_COOKIE['user_key'];
	// Conecta a tu base de datos
	include "../conexion.php";

	// Escapa el valor de la cookie para evitar inyecciones SQL
	$user_key_cookie = mysqli_real_escape_string($conection, $user_key_cookie);
	// Consulta tu base de datos para encontrar un usuario con el mismo valor de 'user_key'
	$query = mysqli_query($conection, "SELECT * FROM m_usuari WHERE user_key = '$user_key_cookie'");

	// Verifica si se encontró un usuario con el mismo valor de 'user_key'
	if (mysqli_num_rows($query) > 0) {
		// El usuario es válido, puedes realizar las acciones que desees aquí
	} else {
		// El usuario no es válido, realiza alguna acción como redirigirlo a la página de inicio de sesión
		setcookie('user_key', '', time() - 3600, '/');
		unset($_COOKIE['user_key']);
		session_destroy();
		header('location: ../');
		exit;
	}
	// Cierra la conexión a la base de datos
	mysqli_close($conection);
} else {
	session_destroy();
	header('location: ../');
	exit;
}

// Conexión a la base de datos
	require_once "../conexion.php";
	include "../conexion.php";
	$user = $_SESSION[$_SESSION['db'].'idUser'];
	$query = mysqli_query($conection,"SELECT is_logged_in FROM m_usuari WHERE id = $user");
    $data = mysqli_fetch_assoc($query);
	$log = $data['is_logged_in'];


// Verifica si is_logged_in es 0 o 1
if ( $log == 0) {
    // Cierra la conexión
	session_destroy();
	header('location: ../');
}

// Obtener la última parte de la URL después del último '/'
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);
$ultimaParte = basename($path);
$ultimaParte = explode('?', $ultimaParte)[0];
// Preparar la consulta para obtener el título del header desde d_dopcion
$query_title_header = "SELECT des_item FROM d_dopcion WHERE frm_item LIKE ?";
$stmt = mysqli_prepare($conection, $query_title_header);
$searchTerm = "%$ultimaParte%";
mysqli_stmt_bind_param($stmt, 's', $searchTerm);
mysqli_stmt_execute($stmt);
$resultado_query = mysqli_stmt_get_result($stmt);

if ($resultado_query) {
    $num_rows = mysqli_num_rows($resultado_query);

    if ($num_rows > 0) {
        $fila = mysqli_fetch_assoc($resultado_query);
				
        if ($num_rows == 1) {
            // Si solo se encontró una coincidencia, tomar el des_item completo
            $header_title = $fila['des_item'];
        } else {
            // Si se encontraron múltiples coincidencias, tomar solo la primera palabra de la primera coincidencia
            $header_title = explode(' ', $fila['des_item'])[0];
        }
    } else {
        // Si no se encontraron resultados en d_dopcion, realizar la segunda consulta
        $query_alternativa = "SELECT d.des_item FROM d_opcion d INNER JOIN d_dopcion dd ON d.id = dd.id_dopci WHERE d.frm_item LIKE ?";
        $stmt_alternativa = mysqli_prepare($conection, $query_alternativa);
        mysqli_stmt_bind_param($stmt_alternativa, 's', $searchTerm);
        mysqli_stmt_execute($stmt_alternativa);
        $resultado_alternativo = mysqli_stmt_get_result($stmt_alternativa);

        if ($resultado_alternativo && mysqli_num_rows($resultado_alternativo) > 0) {
            $fila_alternativa = mysqli_fetch_assoc($resultado_alternativo);
            // Obtener solo la primera palabra de la primera coincidencia
            $header_title = explode(' ', $fila_alternativa['des_item'])[0];
        } else {
            // Manejar el caso donde tampoco se encontraron resultados en la segunda consulta
            $header_title = "Inicio"; // Puedes establecer un valor por defecto o manejarlo según tu lógica
        }
    }
} else {
    // Manejar el error en la consulta inicial
    $header_title = "Inicio";
}

// Liberar recursos
mysqli_stmt_close($stmt);
if (isset($stmt_alternativa)) {
    mysqli_stmt_close($stmt_alternativa);
}

?>

<header>
	<script>updateLastActivity();</script>
	<!-- <form action="check_activity.php" method="post">
    <input type="submit" name="submit" value="check_activity">
	</form> -->
		<div class="header">
			<aside class="sidebar">
			<form class="sidebar__form">
				<!-- <input checked type="checkbox" id="open-menu"> -->
				<!-- <label id="close" for="open-menu" class="material-symbols-outlined">close</label>
				<label id="open" for="open-menu" class="material-symbols-outlined open-menu">double_arrow</label> -->
			</form>
			<?php include "nav.php";?>
			</aside>

			<a href="index" class="header_title"><h2>ERP</h2> <h6>NOW</h6></a>
			<!-- <a class="material-symbols-outlined" href="index">Home</a> -->
			

			<div class="optionsBar">
				<!-- <p>Perú, <?php echo fechaC(); ?> </p> -->
				<p class="app_header" > APP : <?=strtoupper($header_title)?> </p>
				<img class="photouser" src="img/user.png" alt="Usuario">								
				<span class="user"><?php echo $_SESSION[$_SESSION['db'].'usuario'].' - '.$_SESSION[$_SESSION['db'].'rol']; ?></span>
				<a href="salir.php" title="Salir"><span class="material-symbols-outlined open-menu">Logout</span></a>
				<div class="back_button" onclick="history.back();"><i class="fa-solid fa-left-long"></i></div>
			</div>
		</div>
		
	</header>
	<div class="modal">
		<div class="bodyModal">
		</div>
	</div>
	<div class="notifications"></div>
	<div class="modal_window"><div class="windowModal"></div></div>
	
	<script>
		//NOTIFICACIONES
		var $notifications = $('.notifications');
    
    function createToast(type, icon, title, text) {
        var $newToast = $(`
            <div class="toast ${type}">
                <i class="${icon}"></i>
                <div class="content">
                    <div class="title">${title}</div>
                    <span>${text}</span>
                </div>
                <i class="fa-solid fa-xmark close-toast"></i>
            </div>
        `);
        
        $notifications.append($newToast);
        
        setTimeout(function() {
            $newToast.remove();
        }, 1000);
    }
    //Ejemplo de como llamar a la funcion createToast para las notificaciones emergentes
    // $('#success').click(function() {
    //     createToast('success', 'fa-solid fa-circle-check', 'Success', 'This is a success toast.');
    // });
    
    // $('#error').click(function() {
    //     createToast('error', 'fa-solid fa-circle-exclamation', 'Error', 'This is an error toast.');
    // });
    
    // $('#warning').click(function() {
    //     createToast('warning', 'fa-solid fa-triangle-exclamation', 'Warning', 'This is a warning toast.');
    // });
    
    // $('#info').click(function() {
    //     createToast('info', 'fa-solid fa-circle-info', 'Info', 'This is an info toast.');
    // });
    
    // Event delegation for dynamically added close button
    $notifications.on('click', '.close-toast', function() {
        $(this).parent().remove();
    });
    window.onload = function() {
        var texto = document.querySelector('.app_header');
        var longitudTexto = texto.textContent.length;
        
        // Ajustar el tamaño de fuente en base a la longitud del texto
        if (longitudTexto > 20) {
            texto.style.fontSize = '0.7em'; // Reducir el tamaño de fuente si el texto es largo
        }
        // Puedes añadir más condiciones según la longitud del texto
        
    };
	</script>