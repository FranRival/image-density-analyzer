<?php

function ida_estimate_weight($total_images){

$avg_image_kb = 180;

$total_kb = $total_images * $avg_image_kb;

$total_mb = $total_kb / 1024;

return round($total_mb,2);

}