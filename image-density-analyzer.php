<?php
/*
Plugin Name: Image Density Analyzer
Description: Detecta posts con exceso de imágenes y estima su peso total.
Version: 2.0
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


function ida_admin_page(){
?>

<div class="wrap">

<h1>Image Density Analyzer</h1>

<button id="ida-start-scan" class="button button-primary">
Start Scan
</button>

<div id="ida-progress" style="margin-top:15px;"></div>

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



add_action('admin_enqueue_scripts','ida_load_scripts');

function ida_load_scripts($hook){

if($hook !== 'tools_page_image-density-analyzer'){
return;
}

wp_enqueue_script(
'ida-scanner',
plugin_dir_url(__FILE__) . 'assets/scanner.js',
['jquery'],
'1.0',
true
);

wp_localize_script('ida-scanner','ida_ajax',[
'ajax_url'=>admin_url('admin-ajax.php')
]);

}