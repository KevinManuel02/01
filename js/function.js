// import { isValid, parseISO, format } from 'date-fns'


//Actualizar ultima actividad
function updateLastActivity(){
    var action = 'actualizarActividad';

    $.ajax({
        url: 'ajax.php',
        type: "POST",
        async: true,
        data:{action:action},

        success: function(response){
            // console.log(response);
        },

        error: function(error){
            console.log(error);
        }

    });
}

