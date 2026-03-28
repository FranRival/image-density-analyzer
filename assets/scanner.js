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


    // ✅ CORRECTO
    function processNext(){

        if(index >= rows.length){
            $('#ida-progress').html('Weight analysis completed');

            // ✅ CORRECTO: activar edit mode SOLO al final del análisis real
            $('#ida-edit-mode').prop('disabled', false);

            return;
        }

        let row = $(rows[index]);

        // 🔥 FIX IMPORTANTE:
        // antes: td:first ❌ (rompe por columna checkbox)
        // ahora: td[1] = ID ✅
        let postId = row.find('td').eq(1).text().trim();

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

    .done(function(response){

        console.log('AJAX RESPONSE FULL:', JSON.stringify(response, null, 2));

        if(!response || !response.success){
            console.log('Respuesta inválida o error en PHP');
            return;
        }

        if(response.data.html){
            $('#ida-results').append(response.data.html);

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

            // ✅ CORRECTO
            $('#ida-start-weight').prop('disabled', false);

            // ❌ ERROR CORREGIDO:
            // antes activabas edit mode aquí ❌
            // ahora SOLO después del weight analysis ✅
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

let formattedMonth = month.toString().padStart(2, '0');

$('#ida-current-month').text('(' + year + '-' + formattedMonth + ')');

console.log('YEAR:', year, 'MONTH:', month)

// 🔥 FIX UX: bloquear botón mientras escanea
$('#ida-start-weight').prop('disabled', true);

last_id = 0
$('#ida-results').html('')

$('#ida-progress').text(
'Scanning: ' + year + '-' + month
)

scanBatch()

})



/* =========================
   SORT TABLE (CORRECTO)
========================= */

$(document).on('click','.ida-table th',function(){

    let table = $(this).closest('table');
    let tbody = table.find('tbody');
    let rows = tbody.find('tr').toArray();

    let index = $(this).index();
    let type = $(this).data('sort');

    // 🔥 FIX: ignorar columna Select (sin data-sort)
    if(!type) return;

    let asc = $(this).hasClass('asc');

    table.find('th').removeClass('asc desc');
    $(this).addClass(asc ? 'desc' : 'asc');

    rows.sort(function(a,b){

        let A = $(a).children('td').eq(index).text().trim();
        let B = $(b).children('td').eq(index).text().trim();

        A = A.replace('MB','').trim();
        B = B.replace('MB','').trim();

        if(type === 'number'){
            A = parseFloat(A) || 0;
            B = parseFloat(B) || 0;
        }

        if(A < B) return asc ? 1 : -1;
        if(A > B) return asc ? -1 : 1;
        return 0;

    });

    $.each(rows,function(i,row){
        tbody.append(row);
    });

});



/* =========================
   EDIT MODE (FIXED)
========================= */

// 🔥 FIX: estaba fuera del ready ❌
let editMode = false;

$(document).on('click','#ida-edit-mode',function(){

    editMode = !editMode;

    if(editMode){
        $('.ida-select').show();
        $(this).text('Exit Edit Mode');

        // ❌ ERROR CORREGIDO:
        // NO mostrar botón aquí
    }else{
        $('.ida-select').hide();
        $('#ida-copy-selected').hide(); // limpiar
        $(this).text('Edit Mode');
    }

});



/* =========================
   COPY SELECTED (CORRECTO)
========================= */

$(document).on('click','#ida-copy-selected',function(){

    let rows = [];

    $('.ida-checkbox:checked').each(function(){

        let row = $(this).closest('tr');
        let cols = row.find('td');

        let data = [];

        for(let i = 1; i < cols.length; i++){

            let text = $(cols[i]).text().trim();
            text = text.replace('MB','').trim();

            data.push(text);
        }

        rows.push(data.join('\t'));
    });

    if(rows.length === 0){
        alert('No rows selected');
        return;
    }

    let finalText = rows.join('\n');

    navigator.clipboard.writeText(finalText);

    alert('Copied ' + rows.length + ' rows');

});



/* =========================
   CHECKBOX DETECTION (FIX)
========================= */

$(document).on('change','.ida-checkbox',function(){

    let totalChecked = $('.ida-checkbox:checked').length;

    if(totalChecked > 0){
        $('#ida-copy-selected').show(); // ✅ correcto
    }else{
        $('#ida-copy-selected').hide();
    }

});

}); // 🔥 TODO ahora dentro del ready