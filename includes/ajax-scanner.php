<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_ida_scan_batch','ida_scan_batch');

function ida_scan_batch(){

error_log('---- IDA START ----');
error_log(print_r($_POST, true));

$last_id = intval($_POST['last_id']);
$batch = intval($_POST['batch']);
$year = intval($_POST['year']);
$month = intval($_POST['month']);

global $wpdb;

$table = $wpdb->posts;

$sql = $wpdb->prepare(
"SELECT ID FROM $table
WHERE post_type='post'
AND post_status='publish'
AND ID > %d
AND YEAR(post_date) = %d
AND MONTH(post_date) = %d
ORDER BY ID ASC
LIMIT %d",
$last_id,
$year,
$month,
$batch
);

$post_ids = $wpdb->get_col($sql);

$html = '';
$new_last_id = $last_id;



foreach($post_ids as $post_id){

$post = get_post($post_id);

if(!$post){
    continue;
}

preg_match_all('/<img[^>]+src="([^"]+)"/i',$post->post_content,$matches);

$images = isset($matches[1]) ? $matches[1] : [];

// LIMITADOR TEMPORAL
$limited_images = array_slice($images, 0, 5);

$total = count($images);


$weight = round($total * 0.15, 2);

if(!empty($limited_images)){
    try{
        //$weight = ida_calculate_real_weight($limited_images);
    } catch(Throwable $e){
        error_log($e->getMessage());
    }
}

$imgbox = 0;
$other = 0;

foreach($images as $url){

if(strpos($url,'imgbox') !== false){
$imgbox++;
}else{
$other++;
}

}


$density = ida_density_level($total);
$risk = ida_performance_risk($weight);

$html .= "<tr>
<td>{$post->ID}</td>
<td>{$post->post_title}</td>
<td>{$total}</td>
<td>{$imgbox}</td>
<td>{$other}</td>
<td>{$weight} MB</td>
<td class='ida-weight-status' data-post='{$post->ID}'>Pending</td>
<td>{$density}</td>
<td>{$risk}</td>
</tr>";

$new_last_id = $post_id;

}





$done = count($post_ids) < $batch;

wp_send_json_success([
'html'=>$html,
'done'=>$done,
'last_id'=>$new_last_id
]);

}