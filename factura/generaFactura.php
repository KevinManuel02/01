<?php

	//print_r($_REQUEST);
	//exit;
	//echo base64_encode('2');
	//exit;

	include "../../conexion.php";
	require_once '../dompdf/autoload.inc.php';
	include "../../api/calcTotal.php";
	include '../../api/convertirNumeroLetras.php';
	use Dompdf\Dompdf;
	use Dompdf\Options;
	if(!isset($_REQUEST['swt']) || empty($_REQUEST['f']))
	{
		echo "No es posible generar el pdf.";
	}else{
		//swt para reconocer el tipo de documento que se imprimirá
		$swt = $_REQUEST['swt'];

		
		//Datos generales de la empresa, en caso de usar otra tabla, agregar en el query: AS (lista de abajo) 
		//[id, ruc, nombre, razon_social, telefono, email, direccion, igv, MOD_LST]
		//Para ahorrar tener que modificar en cada uno los nombres nuevos
		$query_config   = mysqli_query($conection,"SELECT * FROM configuracion");
		$result_config  = mysqli_num_rows($query_config);
		if($result_config > 0){
			$configuracion = mysqli_fetch_assoc($query_config);
		}

		
		$query_ttablas   = mysqli_query($conection,"SELECT swt_codform FROM t_tablas");
		$result_ttablas  = mysqli_num_rows($query_ttablas);
		if($result_ttablas > 0){
			$ttablas = mysqli_fetch_assoc($query_ttablas);
		}



		if($swt==0){
			$noFactura = $_REQUEST['f'];
			$query = mysqli_query($conection,"SELECT p.id, p.num_ped, DATE_FORMAT(p.fec_ped, '%d/%m/%Y') as fecha,  p.id_mclient, p.swt_ped,ven.des_item as vendedor,cl.ruc_cli as ruc, cl.des_cli as nombre, cl.tel_cli as telefono,cl.dir_cli as direccion,
			(SELECT des_item FROM d_tablas where d_tablas.id = p.id_dtab_srr ) as id_dtab_srr,
			mon.des_item, mon.abr_item, 
			p.por_tigv,p.obs_ped,p.swt_cot,p.swt_igv
			FROM m_pedido p 
			LEFT JOIN d_tablas ven ON p.id_mvended = ven.id 
			LEFT JOIN m_client cl ON p.id_mclient = cl.id 
			LEFT JOIN d_tablas mon ON mon.id = p.id_dtab_mon
			WHERE p.id = $noFactura AND p.swt_ped != 10 ");

			$result = mysqli_num_rows($query);
			if($result > 0){

			$factura = mysqli_fetch_assoc($query);
			$no_factura = $factura['id'];
			$texto_estado = match ($factura['swt_ped']) {
				'0' => 'PEDIDO ANULADO',
				'1' => '',
				'2' => '',
				// '2' => 'PEDIDO TERMINADO',
				'3' => '',
				// '3' => 'PEDIDO MIGRADO',
				default => 'ESTADO DESCONOCIDO',
			};
		
			// Construimos el HTML del estado con la misma clase CSS
			$anulada = "<h2 class='estado_pedido'> $texto_estado </h2>";

				$query_productos = mysqli_query($conection,"SELECT dt.por_tprd ,dt.obs_prod,dt.can_pprd,dt.pre_pprd,
				dt.imp_tprd as precio_total, p.imp_tdct,p.imp_tigv,p.por_tigv,p.por_tdct, p.imp_ttot, p.imp_isc, p.imp_iceberg, p.imp_stot , p.val_f1, mp.cdg_prod, mp.cdg_eqv,marca.des_item as marca, dt.swt_boni, dt.pre_boni
				FROM m_pedido p 
				LEFT JOIN d_pedido dt ON p.id = dt.id_mpedido  
				LEFT JOIN m_produc mp ON mp.id = dt.id_mproduc
				LEFT JOIN d_tablas marca ON marca.id = mp.id_dtab_marca
				WHERE p.id = $no_factura ");
				$result_detalle = mysqli_num_rows($query_productos);

				//instantiate and use the dompdf class

				$options = new Options();
				$options->set('defaultFont', 'Courier');

				$dompdf = new Dompdf($options);
				$dompdf->setBasePath(dirname(__FILE__));
				
				ob_start();
				
				include dirname('__FILE__').'/factura.php';
				$html = ob_get_clean();
				$dompdf->loadHtml($html);
				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('letter', 'portrait');
				// Render the HTML as PDF
				$dompdf->render();
				// Output the generated PDF to Browser
				$dompdf->stream("factura_$noFactura.pdf",['Attachment'=>0]);
				exit;
			}
		}else if($swt==3){
			$id = $_REQUEST['f'];
	
			$query = mysqli_query($conection,"SELECT o.id, o.num_ocom, DATE_FORMAT(o.fec_ocom, '%d/%m/%Y') as fecha,  o.ID_MPROVEE, o.swt_est,ven.des_item as vendedor,pr.ruc_prv as ruc, pr.des_prv as nombre, pr.tel_prv as telefono, pr.dir_prv as direccion,
			(SELECT des_item FROM d_tablas where d_tablas.id = o.id_dtab_srr ) as id_dtab_srr,
			mon.des_item, mon.abr_item, 
			o.por_tigv,o.obs_ocom
			FROM m_ordcom o 
			LEFT JOIN d_tablas ven ON o.id_mvended = ven.id 
			LEFT JOIN m_provee pr ON o.ID_MPROVEE = pr.id 
			LEFT JOIN d_tablas mon ON mon.id = o.id_dtab_mon
			WHERE o.id = $id  AND o.swt_est != 10 ");
	
			$result = mysqli_num_rows($query);
			if($result > 0){
	
				$ocom = mysqli_fetch_assoc($query);
				$no_ocom = $ocom['id'];
	
				$anulada = $ocom['swt_est'] == 0?'<h2 style="text-align:center;position: fixed; top: -100px; left: 0; z-index: 9999; font-size: 11.5rem; color: rgba(255, 0, 0, 0.5); transform: rotate(45deg); transform-origin: left bottom;">ORDEN ANULADA</h2>':'';
	
				$query_detalle = mysqli_query($conection,"SELECT d.DCT_PPRD ,d.OBS_PROD,d.CAN_PPRD,d.PRE_PPRD,
				((d.CAN_PPRD * d.PRE_PPRD)*(1-d.DCT_PPRD/100)) as precio_total, 
				o.IMP_TDCT,o.imp_tigv,o.por_tigv,o.por_tdct, o.imp_ttot, o.imp_isc, o.imp_iceberg 
				FROM m_ordcom o 
				LEFT JOIN d_ordcom d ON o.id = d.id_mordcom 
				WHERE o.id = $no_ocom ");
				$result_detalle = mysqli_num_rows($query_detalle);
	
	
				//instantiate and use the dompdf class
	
				$options = new Options();
				$options->set('defaultFont', 'Courier');
	
				$dompdf = new Dompdf($options);
				$dompdf->setBasePath(dirname(__FILE__));
				
				ob_start();
				
					include dirname('__FILE__').'/ocom.php';
					$html = ob_get_clean();
				
				$dompdf->loadHtml($html);
				
				$dompdf->setPaper('letter', 'portrait');
				// Render the HTML as PDF
				$dompdf->render();
				// Output the generated PDF to Browser
				$dompdf->stream("orden_$no_ocom.pdf",['Attachment'=>0]);
				exit;
			}
		}else{
			$id = $_REQUEST['f'];
			$query = mysqli_query($conection,"SELECT mg.id, mg.num_guia, DATE_FORMAT(mg.fec_guia, '%d/%m/%Y') as fecha,  mg.id_mclient, mg.swt_est, mg.id_dtab_tpg, c.des_prv as nombre,
			COALESCE((SELECT num_item FROM d_tablas where d_tablas.id = mg.id_dtab_cdgarea),(SELECT num_item FROM d_tablas where d_tablas.id = mg.id_dtab_tpg)) as id_dtab_srr,
			mon.des_item, mon.abr_item, mg.obs_guia,mg.ref1,mg.id_mordcom,tmov.des_item as tmov,
			(SELECT des_item FROM d_tablas WHERE d_tablas.id = mg.id_dtab_cdgarea) as cdgarea,
			(SELECT des_item FROM d_tablas WHERE d_tablas.id = mg.id_dtab_oriarea) as oriarea
			FROM m_guia mg 
			LEFT JOIN m_provee c ON mg.ID_MCLIENT = c.id 
			LEFT JOIN d_tablas mon ON mon.id = mg.id_dtab_mon
			LEFT JOIN d_tablas tmov ON tmov.id = mg.id_dtab_tmov
			WHERE mg.id = $id  AND mg.swt_est != 10 ");
			$result = mysqli_num_rows($query);
			if($result > 0){
	
				$nota = mysqli_fetch_assoc($query);
				$no_nota = $nota['id'];

				$anulada = $nota['swt_est'] == 0?'<h2 style="text-align:center;position: fixed; top: -100px; left: 0; z-index: 9999; font-size: 11.5rem; color: rgba(255, 0, 0, 0.5); transform: rotate(45deg); transform-origin: left bottom;">NOTA ANULADA</h2>':'';

				$query_detalle = mysqli_query($conection,"SELECT d.OBS_GUIA,d.CAN_DGUI,mg.can_biene,p.cdg_prod,
				(SELECT abr_item FROM d_tablas WHERE d_tablas.id = d.id_dtab_umed) as umed
				FROM m_guia mg
				LEFT JOIN d_guia d ON mg.id = d.id_mguia 
				LEFT JOIN m_produc p ON p.id = d.id_mproduc
				WHERE mg.id = $no_nota ");
				$result_detalle = mysqli_num_rows($query_detalle);
				$options = new Options();
				$options->set('defaultFont', 'Courier');
				$dompdf = new Dompdf($options);
				$dompdf->setBasePath(dirname(__FILE__));
				ob_start();
				include dirname('__FILE__').'/ingreso.php';
				$html = ob_get_clean();
				$dompdf->loadHtml($html);
				$dompdf->setPaper('letter', 'portrait');
				$dompdf->render();
				$dompdf->stream("orden_$no_nota.pdf",['Attachment'=>0]);
				exit;
			}
		}
		
	}
