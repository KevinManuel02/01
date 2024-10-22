<?php
	$subtotal 	= 0;
	$igv 	 	= 0;
	$impuesto 	= 0;
	$tl_snigv   = 0;
	$total 		= 0;
 //print_r($configuracion); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Pedido</title>
    <link rel="stylesheet" type="text/css" href="style.css">
	<style>
		@import url('fonts/BrixSansRegular.css');
		@import url('fonts/BrixSansBlack.css');

		*{
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			letter-spacing: .5px;
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
	.estado_pedido{
		text-align:center;
		position: fixed; 
		top: -100px; 
		left: 0; 
		z-index: 9999; 
		font-size: 11.5rem; 
		color: rgba(255, 0, 0, 0.5); 
		transform: rotate(45deg); 
		transform-origin: left bottom;
	}




	</style>
</head>
<body>
<?php echo $anulada; 
	$obs = '';
	if(isset($factura['obs_ped'])&&$factura['obs_ped']!=null&&$factura['obs_ped']!=''){
		$obs = '
		<div class="div_obs">
			<strong class="title_obs">Observaciones:</strong>
			<p class="observacion">'.$factura['obs_ped'].'</p>
		</div>';
	}
	$tipo=($factura['swt_cot']==1)?'Cotización':'Pedido';
	$symbol = $factura['abr_item']??'S/.';
	$swt_codform = $ttablas['swt_codform'];
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
				<?php
					if($result_config > 0){
						$igv = $factura['por_tigv'];
				 ?>
				<div>
					<span class="h2"><?php echo strtoupper($configuracion['nombre']); ?></span>
					<p><?php echo $configuracion['razon_social']; ?></p>
					<p><?php echo $configuracion['direccion']; ?></p>
					<p>RUC: <?php echo $configuracion['ruc']; ?></p>
					<p>Teléfono: <?php echo $configuracion['telefono']; ?></p>
					<p>Email: <?php echo $configuracion['email']; ?></p>
				</div>
				<?php
					}
				 ?>
			</td>
			<td class="info_factura">
				<div class="round">
					<span class="h3"><?=$tipo?></span>
					<p>No. Serie: <strong><?php echo $factura['id_dtab_srr']; ?></strong></p>
					<p>No. Pedido: <strong><?php echo $factura['num_ped']; ?></strong></p>
					<p>Fecha: <?php echo $factura['fecha']; ?></p>
					<p>Vendedor: <?php echo $factura['vendedor']; ?></p>
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Cliente</span>
					<table class="datos_cliente">
						<tr>
							<td><label>RUC:</label><p><?php echo $factura['ruc']; ?></p></td>
							<td><label>Teléfono:</label> <p><?php echo $factura['telefono']; ?></p></td>
						</tr>
						<tr>
							<td><label>Nombre:</label> <p><?php echo $factura['nombre']; ?></p></td>
							<td><label>Dirección:</label> <p><?php echo $factura['direccion']; ?></p></td>
						</tr>
					</table>
				</div>
			</td>

		</tr>
	</table>

	<table id="factura_detalle">
			<thead>
				<tr>
					<th width="50px">CANT.</th>
					<th class="textleft">CÓDIGO</th>
					<th class="textleft">DESCRIPCIÓN</th>
					<th class="textleft">MARCA</th>
					<th class="textright" width="150px">PRECIO UNITARIO</th>
					<th class="textright" width="150px">% DESC.</th>
					<th class="textright" width="150px"> PRECIO TOTAL</th>
				</tr>
			</thead>
			<tbody id="detalle_productos">

			<?php

				if($result_detalle > 0){
					$descuentoAfecto = 0;
					while ($row = mysqli_fetch_assoc($query_productos)){
?>
				<tr>
					<td class="textcenter"><?php echo number_format($row['can_pprd'],2); ?></td>
					<?php
						if($swt_codform=='1'){
					?>
					<td><?php echo $row['cdg_prod']; ?></td>
					<?php
						}else{
					?>
					<td><?php echo $row['cdg_eqv']; ?></td>
					<?php }?>
					<td><?php echo $row['obs_prod']; ?></td>
					<td><?php echo $row['marca']; ?></td>
					<td class="textright">
						<?php echo number_format($row['pre_pprd'],4); 
							if($row['swt_boni']){
						?> p.ref(<?=$row['pre_boni']?>)
						<?php
							}
						?>
					</td>
					<td class="textright"><?php echo number_format($row['por_tprd'],2); ?></td>
					<td class="textright"><?php echo number_format($row['precio_total'],2); ?></td>
				</tr>
			<?php 
						$precio_total = $row['precio_total'];
						$descuentoAfecto = round($descuentoAfecto + $row['can_pprd'] * $row['pre_pprd']*($row['por_tprd']/100), 2);;
						$subtotal = round($subtotal + $precio_total, 2);

						//Valores de m_pedido
						$descuentoTotal = $row['imp_tdct'];
						$igvPorcentaje = $row['por_tigv'];
						$globalPorcentaje = $row['por_tdct'];
						$igvTotal = $row['imp_tigv'];
						$importeIsc = $row['imp_isc'];
						$importeIceberg = $row['imp_iceberg'];
						$importeTotal = $row['imp_ttot'];
						$subTotal = $row['imp_stot'];
						$subInafecto = $row['val_f1'];
					}
				}
				$cantidad = convertirNumeroALetras($importeTotal,$factura['des_item']);
			?>
			</tbody>
			<tfoot id="detalle_totales">
				
				<?php echo number_format($subTotal,2); ?>
				<tr>
					<td colspan="6" class="textright"><span>Sub. Afecto <?=$symbol?></span></td>
					<td  class="textright"><?php echo number_format($subTotal,2); ?></td>
				</tr>
				<tr>
					<td colspan="6" class="textright">Sub. Inafecto </td>
					<td  class="textright"><?php echo number_format($subInafecto,2); ?> </td>
				</tr>
				<tr>
					<td colspan="6" class="textright"><span>Desc. Global (<?php echo $globalPorcentaje; ?> %)</span>
					</td>
					<td  class="textright" id="final_desc_globt"><span><?php echo number_format($descuentoTotal,2); ?></span></td>
				</tr>
				<tr>
					<td colspan="6" class="textright">IGV (<?php echo $igvPorcentaje; ?> %)
					<td  class="textright" ><span><?php echo number_format($igvTotal,2); ?></span></td>
				</tr>
				<tr>
					<td colspan="6" class="textright">ISC</td>
					<td  class="textright"><?php echo number_format($importeIsc,2); ?></td>
				</tr>
				<tr>
					<td colspan="6" class="textright">ICEBERG</td>
					<td  class="textright"><?php echo number_format($importeIceberg,2); ?></td>
				</tr>
				<tr>
					<td colspan="6" class="textright">TOTAL <?=$symbol?></td>
					<td  class="textright"><?php echo number_format($importeTotal,2); ?></td>
				</tr>
				<tr>
					<td colspan="6" style="text-align:center;"><?=$cantidad?></td>
				</tr>
		</tfoot>
	</table>

	<div><?=$obs?>
		<p class="nota">Si usted tiene dudas sobre este pedido, <br>pongase en contacto con nosotros en:  lzz.desarrollo@gmail.com</p>
		<h4 class="label_gracias">¡Gracias por su compra!</h4>
	</div>

</div>
</body>
</html>