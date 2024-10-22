<?php 
include "includes/updatePermissions.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config. Series</title>
    <?php include "includes/scripts.php"; ?>
<script>
    $(document).ready(function() {
        $('#btn_pdf').on('click',function() {
            generarPDF($('#correlativo').val(),$('#documento').val())
        });
    });

</script>
</head>
<body>
<?php include "includes/header.php"; ?>
    <section class="container">
        <div class="container-top">
            <h1>Impresiones</h1>
        </div>
        <div class="form_div">
            <label for="documento">Documento:</label>
            <select class="documento" name="documento" id="documento" >
                <option value="0">Pedido</option>
                <option value="3">Orden de Compra</option>
                <option value="1">Nota de Salida</option>
                <option value="2">Nota de Ingreso</option>
            </select>
        </div>
        <div class="form_div">
            <label for="correlativo">Correlativo:</label>
            <input type="text" class="correlativo" name="correlativo" id="correlativo">
        </div>
        <p>Pedido: 5679749</p>
        <p>Orden de compra: 3504</p>
        <p>Nota de Ingreso: 191</p>
        <p>Nota de Salida: 211</p>
        <div class="datos">  
            <a href="#" id="btn_pdf" class="btn_pdf" title="Ver PDF">
                <span class="material-symbols-outlined" style="font-size: 3em;">Picture_As_Pdf</span>
            </a>
        </div>
    </section>
</body>
</html>
