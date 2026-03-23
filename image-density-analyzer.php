<?php
/*
Plugin Name: Image Density Analyzer
Description: Detecta posts con exceso de imágenes y estima su peso total.
Version: 4.3
Author: Emmanuel
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/density-classifier.php';
require_once plugin_dir_path(__FILE__) . 'includes/weight-estimator.php';
require_once plugin_dir_path(__FILE__) . 'includes/export.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-scanner.php';


add_action('admin_menu','ida_add_menu');

function ida_add_menu(){

    add_management_page(
        'Image Density Analyzer',
        'Image Density Analyzer',
        'manage_options',
        'image-density-analyzer',
        'ida_admin_page'
    );

}




add_action('admin_enqueue_scripts', function($hook){

    // SOLO cargar en tu página
    if ($hook !== 'tools_page_image-density-analyzer') {
        return;
    }

    wp_enqueue_script(
        'ida-scanner',
        plugins_url('assets/scanner.js', __FILE__),
        ['jquery'],
        time(),
        true
    );

    // CSS
    wp_enqueue_style(
        'ida-styles',
        plugins_url('assets/styles.css', __FILE__),
        [],
        time()
    );

    wp_localize_script('ida-scanner','ida_ajax',[
        'ajax_url'=>admin_url('admin-ajax.php')
    ]);

});









function ida_admin_page(){
?>

<div class="wrap">

<h1>Image Density Analyzer</h1>

<?php

global $wpdb;

$results = $wpdb->get_results("
SELECT 
YEAR(post_date) as year,
MONTH(post_date) as month,
COUNT(ID) as total
FROM {$wpdb->posts}
WHERE post_type='post'
AND post_status='publish'
GROUP BY YEAR(post_date), MONTH(post_date)
ORDER BY year DESC, month DESC
");

echo "<h2>Scan by Month</h2>";

echo "<div class='ida-table-wrapper-small'>";

echo "<table class='widefat striped ida-table-small' style='max-width:600px'>";

echo "<thead>
<tr>
<th>Year</th>
<th>Month</th>
<th>Posts</th>
<th>Action</th>
</tr>
</thead>";

echo "<tbody>";


if(!empty($results)){
    foreach($results as $row){

        $y = intval($row->year);
        $m = str_pad(intval($row->month),2,'0',STR_PAD_LEFT);
        $total = intval($row->total);

        echo "<tr>
        <td>{$y}</td>
        <td>{$m}</td>
        <td>{$total}</td>
        <td>
        <button class='button button-primary ida-start-month-scan'
        data-year='{$y}'
        data-month='{$row->month}'>
        Scan
        </button>
        </td>
        </tr>";
    }
}


echo "</tbody>";
echo "</table>";
echo "</div>";

?>

<div id="ida-progress" style="margin-top:20px;"></div>
<button id="ida-start-weight" class="button button-secondary" style="margin-top:10px;">
    Analyze Real Weight
</button>

<div class="ida-table-wrapper">
    <table class="widefat striped ida-table">

        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Total</th>
            <th>ImgBox</th>
            <th>Other</th>
            <th>Weight</th>
            <th>Density</th>
            <th>Risk</th>
        </tr>
        </thead>

        <tbody id="ida-results"></tbody>

    </table>
</div>

<?php
}







register_activation_hook(__FILE__, 'ida_create_cache_table');

function ida_create_cache_table(){

    global $wpdb;

    $table = $wpdb->prefix . 'ida_image_cache';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        image_url VARCHAR(500) NOT NULL,
        size_bytes BIGINT,
        checked_at DATETIME,
        UNIQUE KEY image_url (image_url)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}




add_action('wp_ajax_ida_calculate_weight','ida_calculate_weight');

function ida_calculate_weight(){

$post_id = intval($_POST['post_id']);
$offset = intval($_POST['offset']);

$post = get_post($post_id);

if(!$post){
    wp_send_json_error();
}

preg_match_all('/<img[^>]+src="([^"]+)"/i',$post->post_content,$matches);

$images = isset($matches[1]) ? $matches[1] : [];

$batch_size = 3;

// 🔥 tomar bloque progresivo
$batch = array_slice($images, $offset, $batch_size);

$total_bytes = 0;

foreach($batch as $url){

    $size = ida_get_image_size($url);
    $total_bytes += $size;

}

$weight = round($total_bytes / (1024 * 1024), 2);

$next_offset = $offset + $batch_size;
$done = $next_offset >= count($images);

wp_send_json_success([
    'weight' => $weight,
    'next_offset' => $next_offset,
    'done' => $done,
    'total_images' => count($images)
]);

}
