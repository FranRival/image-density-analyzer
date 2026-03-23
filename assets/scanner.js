jQuery(document).ready(function($){

let last_id = 0
let batch = 5
let year = null
let month = null

function scanBatch(){

    console.log("SCAN BATCH START");

    $.post(ida_ajax.ajax_url, {

        action: 'ida_scan_batch',
        last_id: last_id,
        batch: batch,
        year: year,
        month: month

    })

    //a este punto el PHP funciona
    //AJAX funciona
    //Datos funcionan y llegan bien
    // document.querySelectorAll('#ida-results tr').length -entrega 26 -esta es la tabla: 2024 / 12 / 26 - Scan.
    .done(function(response){

        console.log('AJAX RESPONSE FULL:', JSON.stringify(response, null, 2));

        if(!response || !response.success){
            console.log('Respuesta inválida o error en PHP');
            return;
        }

        // Si no hay HTML, lo mostramos también
        if(response.data.html){
            $('#ida-results').append(response.data.html);

        // SCROLL automático
        $('#ida-results tr:last')[0].scrollIntoView({
            behavior: "smooth",
            block: "end"
        });
        }

        last_id = response.data.last_id || last_id;

        let total = $('#ida-results tr').length;

        $('#ida-progress').html(
        'Processed: ' + total + ' posts | Last ID: ' + last_id
        );

        if(response.data.done === false){
            scanBatch(); // siguiente batch
        }else{
            $('#ida-progress').append('<br>Scan completed');
        }

    })

    .fail(function(xhr){

        console.log('AJAX ERROR:', xhr);
        console.log('RESPONSE TEXT:', xhr.responseText);

        $('#ida-progress').append('<br>Error en AJAX');

    });

}

$(document).on('click','.ida-start-month-scan',function(){

year = $(this).attr('data-year')
month = $(this).attr('data-month')

console.log('YEAR:', year, 'MONTH:', month)

last_id = 0
$('#ida-results').html('')

$('#ida-progress').text(
'Scanning: ' + year + '-' + month
)

scanBatch()

})

});