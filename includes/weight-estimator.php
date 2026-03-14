<?php

function ida_get_cached_size($url){

global $wpdb;

$table = $wpdb->prefix . 'ida_image_cache';

$result = $wpdb->get_var(
$wpdb->prepare(
"SELECT size_bytes FROM $table WHERE image_url=%s LIMIT 1",
$url
)
);

return $result;

}



function ida_cache_image_size($url,$size){

global $wpdb;

$table = $wpdb->prefix . 'ida_image_cache';

$wpdb->insert($table,[
'image_url'=>$url,
'size_bytes'=>$size,
'checked_at'=>current_time('mysql')
]);

}



function ida_get_image_size($url){

$cached = ida_get_cached_size($url);

if($cached !== null){
return intval($cached);
}

$response = wp_remote_head($url,['timeout'=>10]);

if(is_wp_error($response)){
return 0;
}

$headers = wp_remote_retrieve_headers($response);

if(isset($headers['content-length'])){

$size = intval($headers['content-length']);

ida_cache_image_size($url,$size);

return $size;

}

return 0;

}



function ida_calculate_real_weight($images){

$total_bytes = 0;

foreach($images as $url){

$size = ida_get_image_size($url);

$total_bytes += $size;

}

$total_mb = $total_bytes / (1024 * 1024);

return round($total_mb,2);

}