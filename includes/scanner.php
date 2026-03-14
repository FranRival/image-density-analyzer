<?php

function ida_scan_posts(){

$args = array(
'post_type'=>'post',
'posts_per_page'=>-1,
'post_status'=>'publish',
'fields'=>'ids'
);

$query = new WP_Query($args);

echo "<table class='widefat striped'>";

echo "<thead>
<tr>
<th>ID</th>
<th>Title</th>
<th>Date</th>
<th>Total Images</th>
<th>ImgBox</th>
<th>Other</th>
<th>Est. Weight</th>
<th>Density</th>
<th>Performance Risk</th>
</tr>
</thead>";

foreach($query->posts as $post_id){

$post = get_post($post_id);

$content = $post->post_content;

preg_match_all('/<img[^>]+src="([^"]+)"/i',$content,$matches);

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

$density = ida_density_level($total);

$weight = ida_calculate_real_weight($matches[1]);

$risk = ida_performance_risk($weight);

echo "<tr>
<td>{$post->ID}</td>
<td>{$post->post_title}</td>
<td>{$post->post_date}</td>
<td>{$total}</td>
<td>{$imgbox}</td>
<td>{$other}</td>
<td>{$weight} MB</td>
<td>{$density}</td>
<td>{$risk}</td>
</tr>";

}

echo "</table>";

}