<?php

function ida_get_image_size($url){

    $response = wp_remote_head($url, [
        'timeout' => 10
    ]);

    if(is_wp_error($response)){
        return 0;
    }

    $headers = wp_remote_retrieve_headers($response);

    if(isset($headers['content-length'])){
        return intval($headers['content-length']);
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