jQuery(document).ready(function($){

let last_id = 0
let batch = 50
let year = null
let month = null

function scanBatch(){

$.post(ida_ajax.ajax_url,{

action:'ida_scan_batch',
last_id:last_id,
batch:batch,
year:year,
month:month

},function(response){

if(!response.success){
return
}

$('#ida-results').append(response.data.html)

last_id = response.data.last_id

$('#ida-progress').text(
'Last processed ID: ' + last_id
)

if(response.data.done === false){
scanBatch()
}else{
$('#ida-progress').append('<br>Scan completed')
}

})

}

$(document).on('click','.ida-start-month-scan',function(){

year = $(this).data('year')
month = $(this).data('month')

last_id = 0
$('#ida-results').html('')

$('#ida-progress').text(
'Scanning: ' + year + '-' + month
)

scanBatch()

})

});