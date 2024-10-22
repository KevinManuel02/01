<?php 
function convertirNumeroALetras($numero, $mon) {
    // Arreglos con las palabras correspondientes a cada grupo de cifras
    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
    $grupos = ['', ' MIL', ' MILLONES', ' MIL MILLONES', ' BILLONES'];

    // Parte decimal
    $parte_decimal = ($numero - floor($numero)) * 100;
    $parte_decimal = round($parte_decimal);
    $centavos = sprintf("%02d", $parte_decimal);

    // Parte entera
    $numero = (int) $numero;
    $cadena = '';

    // Función para convertir tres dígitos a letras
    function tresDigitosALetras($numero, $unidades, $decenas, $centenas) {
        $unidad = $numero % 10;
        $decena = ($numero % 100) - $unidad;
        $centena = ($numero % 1000) - $decena - $unidad;

        $decena /= 10;
        $centena /= 100;

        $letras = '';

        if ($centena > 0) {
            $letras .= $centenas[$centena] . ' ';
        }

        // Condición específica para los números entre 21 y 29
        if ($decena == 2 && $unidad >0) {
            $letras .= 'VEINTI' . strtoupper($unidades[$unidad]);
        } elseif ($decena > 1) {
            $letras .= $decenas[$decena] . ' ';
            if ($unidad > 0) {
                $letras .= 'Y ' . $unidades[$unidad] . ' ';
            }
        } else {
            $letras .= $unidades[$decena * 10 + $unidad] . ' ';
        }

        return trim($letras);
    }

    if (!$numero) {
        $cadena = 'CERO SOLES';
    } else {
        $contador = 0;
        while ($numero > 0) {
            $grupo = $numero % 1000;
            $numero = floor($numero / 1000);

            $grupo_letras = tresDigitosALetras($grupo, $unidades, $decenas, $centenas);

            if ($grupo_letras != '') {
                $cadena = $grupo_letras . $grupos[$contador] . ' ' . $cadena;
            }

            $contador++;
        }

        $cadena .= "CON $centavos/100 $mon";
    }

    return trim($cadena);
}

// Ejemplo de uso
// $numero = 1234567890123.89;
// echo convertirNumeroALetras($numero);
