<?php 
function calcTotal($sub_total, $igv, $descuentoGlobal, $imp_isc, $imp_iceberg, $swtIgv = 0, $sub_afecto = 0,$percep=0,$tributos=0,$financieros=0,$flete=0) {
    $alertIgv = false;
    $impuesto_diferencia = 0;
    $tot_percep =0;
    if ($swtIgv == 0) {
        // Caso 1: Precio sin IGV incluido
        $descuentoGlobalT = round(($sub_total + $sub_afecto) * ($descuentoGlobal / 100), 2);
        
        //Parte impuestos
        $sub_total_descuento = round($sub_total - $sub_total * ($descuentoGlobal / 100), 2);

        // Luego, se calcula el IGV sobre el subtotal con descuento
        $impuesto = round($sub_total_descuento * ($igv / 100), 2);

        // El total serían los subtotales más impuestos menos el descuento total
        $total = round($sub_total + $sub_afecto + $impuesto - $descuentoGlobalT, 2);
    } else {
        // Caso 2: Precio con IGV incluido
        if ($descuentoGlobal == 0) {
            $impuesto = round($sub_total * ($igv / (100 + $igv)), 2); // Separamos el IGV del subtotal
            $total = round($sub_total + $sub_afecto, 2);
            $descuentoGlobalT = 0;
        } else {
            // Modificar toda la lógica aquí

            // Remover el IGV del subtotal
            $sub_total_sinIgv = round($sub_total / (1 + $igv / 100), 2);
            // Calcular el total aplicando el descuento global al subtotal original + sub_afecto
            $descuentoGlobalT = round(($sub_total_sinIgv + $sub_afecto) * ($descuentoGlobal / 100), 2);
            $impuesto = round(($sub_total_sinIgv*(1-$descuentoGlobal/100 ))* ($igv/100 ), 2);
            // Aplicar el % de descuento al subtotal sin IGV junto con sub_afecto
            $total1 = round(($sub_total_sinIgv + $sub_afecto) * (1-$descuentoGlobal / 100)+$impuesto, 2);
            
            $total2 = round(($sub_total + $sub_afecto) * (1- $descuentoGlobal/100), 2);
            
            $impuesto_diferencia = $total1 - $total2;
            $sub_total = $sub_total_sinIgv;
            // Comparar los dos totales
            if ($impuesto_diferencia==0) {
                $total = $total2;
            } else {
                $total = $total2;
                //Ajustando el igv para los calculos finales
                $impuesto = $impuesto+ $impuesto_diferencia??0;
            }
            
        }
        if($impuesto_diferencia!=0 && abs($impuesto_diferencia)>=0.05){
            $alertIgv = $impuesto_diferencia;
        }

        $sub_total = $total - $impuesto + $descuentoGlobalT - $sub_afecto;
        $tot_percep = round($sub_total * ($percep / 100), 2);
        $total = $total + $imp_isc + $imp_iceberg + $tributos + $financieros + $flete + $tot_percep;
    }
    return [
        'sub_total' => $sub_total,
        'impuesto' => $impuesto,
        'total' => $total,
        'descuentoGlobalT' => $descuentoGlobalT,
        'alertIgv' => $alertIgv,
        'tot_percep' => $tot_percep
    ];
}


