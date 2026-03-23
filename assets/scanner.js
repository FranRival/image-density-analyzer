jQuery(document).ready(function($){

let last_id = 0
let batch = 5
let year = null
let month = null





$(document).on('click','#ida-start-weight',function(){
    processWeights();
});



function processWeights(){

    let rows = $('#ida-results tr');
    let index = 0;

    function processPost(row, postId){

        let offset = 0;
        let totalWeight = 0;

        function processBatch(){

            $.post(ida_ajax.ajax_url, {
                action: 'ida_calculate_weight',
                post_id: postId,
                offset: offset
            })
            .done(function(res){

                if(res.success){

                    totalWeight += parseFloat(res.data.weight);
                    offset = res.data.next_offset;

                    row.find('.ida-weight-status').text(
                        totalWeight.toFixed(2) + ' MB'
                    );

                    if(!res.data.done){
                        setTimeout(processBatch, 200);
                    } else {
                        // 🔥 terminado este post
                        index++;
                        processNext();
                    }

                } else {
                    row.find('.ida-weight-status').text('Error');
                    index++;
                    processNext();
                }

            })
            .fail(function(){
                row.find('.ida-weight-status').text('Fail');
                index++;
                processNext();
            });

        }

        processBatch();
    }

    function processNext(){

        if(index >= rows.length){
            $('#ida-progress').html('Weight analysis completed');
            return;
        }

        let row = $(rows[index]);
        let postId = row.find('td:first').text().trim();

        $('#ida-progress').html(
            'Processing post ' + (index + 1) + ' of ' + rows.length
        );

        processPost(row, postId);
    }

    processNext();
}






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
            scanBatch()
        }else{
            $('#ida-progress').append('<br>Scan completed');

            // 🔥 iniciar cálculo de peso

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