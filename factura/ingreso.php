<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Orden de compra</title>
    <link rel="stylesheet" type="text/css" href="style.css">
	<style>
		@import url('fonts/BrixSansRegular.css');
		@import url('fonts/BrixSansBlack.css');

		*{
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		p, label, span, table{
			font-family: 'BrixSansRegular';
			font-size: 9pt;
		}
		.h2{
			font-family: 'BrixSansBlack';
			font-size: 16pt;
		}
		.h3{
			font-family: 'BrixSansBlack';
			font-size: 12pt;
			display: block;
			background: #0a4661;
			color: #FFF;
			text-align: center;
			padding: 3px;
			margin-bottom: 5px;
		}
		#page_pdf{
			width: 95%;
			margin: 15px auto 10px auto;
		}

		#factura_head, #factura_cliente, #factura_detalle{
			width: 100%;
			margin-bottom: 10px;
		}
		.logo_factura{
			width: 25%;
		}
		.info_empresa{
			width: 50%;
			text-align: center;
		}
		.info_factura{
			width: 25%;
		}
		.info_cliente{
			width: 100%;
		}
		.datos_cliente{
			width: 100%;
		}
		.datos_cliente tr td{
			width: 50%;
		}
		.datos_cliente{
			padding: 10px 10px 0 10px;
		}
		.datos_cliente label{
			width: 75px;
			display: inline-block;
		}
		.datos_cliente p{
			display: inline-block;
		}

		.textright{
			text-align: right;
		}
		.textleft{
			text-align: left;
		}
		.textcenter{
			text-align: center;
		}
		.round{
			border-radius: 10px;
			border: 1px solid #0a4661;
			overflow: hidden;
			padding-bottom: 15px;
		}
		.round p{
			padding: 0 15px;
		}

		#factura_detalle{
			border-collapse: collapse;
		}
		#factura_detalle thead th{
			background: #058167;
			color: #FFF;
			padding: 5px;
		}
		#detalle_productos tr:nth-child(even) {
			background: #ededed;
		}
		#detalle_totales span{
			font-family: 'BrixSansBlack';
		}
		.nota{
			font-size: 8pt;
		}
		.label_gracias{
			font-family: verdana;
			font-weight: bold;
			font-style: italic;
			text-align: center;
			margin-top: 20px;
		}
		.anulada{
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translateX(-50%) translateY(-50%);
		}
		.logo_empresa {
    font-family: 'Arial Black', Gadget, sans-serif; /* Fuente negrita */
    font-size: 3.8rem; /* Tamaño grande */
    text-align: center;
    color: #dce0ff; /* Color del texto */
    padding:0 0px 0 80px; /* Espacio alrededor del texto */
    border-radius: 15px; /* Bordes redondeados */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Sombra externa */
    letter-spacing: 2px; /* Espaciado entre letras */
    text-shadow: 2px 2px 4px #333333, 0 0 5px #333333; /* Sombras para el texto */
}
	</style>
</head>
<body>
<?php echo $anulada; 
	$obs = '';
	if(isset($nota['obs_guia'])&&$nota['obs_guia']!=null&&$nota['obs_guia']!=''){
		$obs = '
		<div class="div_obs">
			<strong class="title_obs">Observaciones:</strong>
			<p class="observacion">'.$nota['obs_guia'].'</p>
		</div>';
	}
	$tipo='Nota de '.($nota['id_dtab_tpg']==3826?'Ingreso':'Salida de Almacén');
	$symbol = $nota['abr_item']??'S/.';
?>
<div id="page_pdf">
	<table id="factura_head">
		<tr>
			<td class="logo_factura">
				<div>
					<!-- <img class="logo_image" src="img/logo.png"> -->
					<h2 class="logo_empresa">ERP NOW</h2>
				</div>
			</td>
			<td class="info_empresa">
				<div>
					<span class="h2"><?php echo strtoupper($configuracion['nombre']); ?></span>
					<p><?php echo $configuracion['razon_social']; ?></p>
					<p><?php echo $configuracion['direccion']; ?></p>
					<p>RUC: <?php echo $configuracion['ruc']; ?></p>
					<p>Teléfono: <?php echo $configuracion['telefono']; ?></p>
					<p>Email: <?php echo $configuracion['email']; ?></p>
				</div>
			</td>
			<td class="info_factura">
				<div class="round">
					<span class="h3"><?=$tipo?></span>
					<p>No. Serie: <strong><?php echo $nota['id_dtab_srr']; ?></strong></p>
					<p>No. Pedido: <strong><?php echo $nota['num_guia']; ?></strong></p>
					<p>Fecha: <?php echo $nota['fecha']; ?></p>
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">INFORMACIÓN ADICIONAL</span>
					<table class="datos_cliente">
						<tr>
							<td><label>TIPO MOVIM.:</label><p><?php echo $nota['tmov']; ?></p></td>
							<?php if($nota['ref1']){ ?> 
							<td><label>N°DOCUM.REF.:</label> <p><?php echo $nota['ref1']; ?></p></td>
							<?php }?>
						</tr>
						<tr>
							<td><label><?php echo ($swt == 1)?'ÁREA':'ORIGEN' ?>:</label> 
								<p><?php if($swt==1){
									echo $nota['cdgarea'];
								}else{ echo $nota['oriarea'];} ?></p>
							</td>
							<?php if($nota['id_mordcom']){ ?> 
							<td><label>DOC.REFERENCIA:</label> <p><?php echo $nota['id_mordcom']; ?></p></td>
							<?php }?>
						</tr>
						<tr>
						<td>
							<?php if($swt==2 && $nota['cdgarea']){ ?> 
								<label>Destino:</label> <p><?php echo $nota['cdgarea']; ?></p></td>
							<?php }; ?> 
							<?php if($nota['nombre']){ ?> 
								<td><label>PROV/CLIE:</label> <p><?php echo $nota['nombre']; ?></p></td>
							<?php }; ?> 
						</tr>
					</table>
				</div>
			</td>

		</tr>
	</table>

	<table id="factura_detalle">
			<thead>
				<tr>
					<th class="textleft">CÓDIGO</th>
					<th class="textleft">DESCRIPCION</th>
					<th class="textright" width="50px">REF 1</th>
					<th class="textright" width="50px">REF 2</th>
					<th class="textright" width="50px">REF 3</th>
					<th class="textright" width="50px">REF 4</th>
					<th class="textright" width="50px">REF 5</th>
					<th class="textright" width="50px">MED.</th>
					<th width="150px">CANTIDAD</th>
				</tr>
			</thead>
			<tbody id="detalle_productos">

			<?php

				if($result_detalle > 0){
					$descuentoAfecto = 0;
					while ($row = mysqli_fetch_assoc($query_detalle)){
?>
				<tr>
					<td><?php echo $row['cdg_prod']; ?></td>
					<td><?php echo $row['OBS_GUIA']; ?></td>
					<td class="textright"></td>
					<td class="textright"></td>
					<td class="textright"></td>
					<td class="textright"></td>
					<td class="textright"></td>
					<td class="textright"><?=$row['umed']??''?></td>
					<td class="textcenter"><?php echo number_format($row['CAN_DGUI'],2); ?></td>
				</tr>
			<?php
						
					}
				}
			?>
			</tbody>
			<tfoot id="detalle_totales">
				<tr>
					<td colspan="9" style="text-align:right;"><?=$row['can_biene']??''?></td>
				</tr>
		</tfoot>
	</table>

	<div><?=$obs?>
		<p class="nota">Si usted tiene alguna duda, <br>pongase en contacto con nosotros en:  lzz.desarrollo@gmail.com</p>
		<h4 class="label_gracias">¡Gracias por su compra!</h4>
	</div>

</div>
</body>
</html>