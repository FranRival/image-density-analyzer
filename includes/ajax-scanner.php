<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_ida_scan_batch','ida_scan_batch');

function ida_scan_batch(){

    $last_id = intval($_POST['last_id']);
    $batch   = intval($_POST['batch']);
    $year    = intval($_POST['year']);
    $month   = intval($_POST['month']);

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
        $total  = count($images);

        // 🔥 Peso estimado (rápido)
        $weight = round($total * 0.15, 2);

        // Conteo imgbox vs other
        $imgbox = 0;
        $other  = 0;

        foreach($images as $url){
            if(strpos($url,'imgbox') !== false){
                $imgbox++;
            } else {
                $other++;
            }
        }

        // Clasificaciones
        $density = ida_density_level($total);
        $risk    = ida_performance_risk($weight);

        // FIX clase CSS (SUPER CRITICAL → SUPER-CRITICAL)
        $density_class = str_replace(' ', '-', $density);

        // 
        $row  = "<tr>";
        //Columna de select
        $row .= "<td class='ida-select'>
        <input type='checkbox' class='ida-checkbox'>
        </td>";
        $row .= "<td>{$post->ID}</td>";
        $row .= "<td>{$post->post_title}</td>";
        $row .= "<td>{$total}</td>";
        $row .= "<td>{$imgbox}</td>";
        $row .= "<td>{$other}</td>";
        $row .= "<td>{$weight} MB</td>";
        $row .= "<td class='ida-weight-status' data-post='{$post->ID}'>Pending</td>";
        $row .= "<td class='ida-density ida-density-{$density_class}'>{$density}</td>";
        $row .= "<td>{$risk}</td>";
        $row .= "</tr>";

        $html .= $row;

        $new_last_id = $post_id;
    }

    $done = count($post_ids) < $batch;

    wp_send_json_success([
        'html'     => $html,
        'done'     => $done,
        'last_id'  => $new_last_id
    ]);
}