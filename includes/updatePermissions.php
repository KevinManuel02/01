<?php 
	session_start();
	include "../conexion.php";
	$idusuario = $_SESSION[$_SESSION['db'].'idUser'];
	$query = "SELECT GROUP_CONCAT(CONCAT(d.id_dopcion, '-', d.lec_item)) AS combined_array 
						FROM m_usuari m 
						LEFT JOIN d_usuari d ON m.id = d.id_musuari 
						WHERE m.id = ?";
	$stmt = mysqli_prepare($conection, $query);
	mysqli_stmt_bind_param($stmt, "i", $idusuario);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	// Verificar si se encontraron resultados
	if(mysqli_num_rows($result) > 0) {
		$data = mysqli_fetch_array($result);
		$combinedArray = [];
		$pairs = explode(',', $data['combined_array']);
		if($data['combined_array']){
				foreach ($pairs as $pair) {
						[$id_dopcion, $lec_item] = explode('-', $pair);
						$combinedArray[$id_dopcion] = $lec_item;
				}
		}
		mysqli_stmt_close($stmt);
		$_SESSION[$_SESSION['db'].'perms'] = $combinedArray??'';
	}