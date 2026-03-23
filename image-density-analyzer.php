<?php
/*
Plugin Name: Image Density Analyzer
Description: Detecta posts con exceso de imágenes y estima su peso total.
Version: 3.8
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

echo "<table class='widefat striped' style='max-width:600px'>";
echo "<thead>
<tr>
<th>Year</th>
<th>Month</th>
<th>Posts</th>
<th>Action</th>
</tr>
</thead>";

foreach($results as $row){

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

}

echo "</table>";

?>

<div id="ida-progress" style="margin-top:20px;"></div>

<table class="widefat striped" style="margin-top:20px;">

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

