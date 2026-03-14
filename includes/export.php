<?php

function ida_export_csv(){

$args = array(
'post_type'=>'post',
'posts_per_page'=>-1,
'post_status'=>'publish',
'fields'=>'ids'
);

$query = new WP_Query($args);

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=image-density-report.csv');

$output = fopen('php://output','w');

fputcsv($output,[
'Post ID',
'Title',
'Date',
'Total Images',
'ImgBox',
'Other',
'Estimated Weight MB',
'Density',
'Performance Risk'
]);

foreach($query->posts as $post_id){

$post = get_post($post_id);

preg_match_all('/<img[^>]+src="([^"]+)"/i',$post->post_content,$matches);

$total = count($matches[1]);

$imgbox = 0;
$other = 0;

foreach($matches[1] as $url){

if(strpos($url,'imgbox') !== false){
$imgbox++;
}else{
$other++;
}

}

$weight = ida_calculate_real_weight($matches[1]);

$density = ida_density_level($total);

$risk = ida_performance_risk($weight);

fputcsv($output,[
$post->ID,
$post->post_title,
$post->post_date,
$total,
$imgbox,
$other,
$weight,
$density,
$risk
]);

}

fclose($output);
exit;

}