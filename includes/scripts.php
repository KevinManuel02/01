<!-- Redireccion www, 301 -->
<?php
// Verificar si la solicitud llega a la versión con www
if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
    // Redireccionar a la misma URL sin www
    $url = "http://" . substr($_SERVER['HTTP_HOST'], 4) . $_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $url");
    exit();
}

?>
<!-- sweet alert -->
<link href="css/sweetalert2.css" rel="stylesheet">
<script src="js/sweetalert2.all.min.js"></script>

<!-- google font + c/internet-->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="css/sec.css">

<link rel="icon" href="img/dollar.ico">
<script type="text/javascript" src="js/jquery.min.js" ></script>
<script type="text/javascript" src="js/function.js"></script>
<script type="text/javascript" src="js/functionDefer.js" defer></script>
<!--para guardar o no cache-->
<!--<meta name="robots" content="noarchive">-->

<!-- select2 -->
<link href="css/select2.min.css" rel="stylesheet" />
<script src="js/select2.min.js"></script>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php include "functions.php"; ?>