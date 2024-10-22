document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.input-box input').forEach(input => {
        if (input.value.trim() !== '') {
            input.classList.add('active');
        }
    });

    function updateContent() {
        if ($(window).width() < 1000) {
            $('#t1').text('ERP');
            $('#t2').text('');
        } else {
            $('#t1').text('LAZZAR');
            $('#t2').text('SOLUTIONS');
        }
    }

    // Llamar a la función en la carga de la página
    updateContent();

    // Llamar a la función en el redimensionamiento de la ventana
    $(window).resize(function() {
        updateContent();
    });
    var swt =0;
    //Ver contraseña
    $('#view_pass_icon').click(function(){
        $('#view_pass_icon').toggleClass('fa-eye fa-eye-slash');
        if(swt==1){
            $('#clave').attr('type', 'password');
            $(this).attr('title', 'Ver contraseña');
            $(this).attr('style','color: #7a7a7a')
            swt = 0;
        }else{
            $('#clave').attr('type', 'text');
            $(this).attr('title', 'Ocultar contraseña');
            $(this).attr('style','color: #FFF')
            swt = 1;
        }
    });
});
