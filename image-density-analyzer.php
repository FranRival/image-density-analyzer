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

require_once plugin_dir_path(__FILE__) . 'includes/scanner.php';
require_once plugin_dir_path(__FILE__) . 'includes/density-classifier.php';
require_once plugin_dir_path(__FILE__) . 'includes/weight-estimator.php';
require_once plugin_dir_path(__FILE__) . 'includes/export.php';

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

<form method="post">

<input type="submit" name="scan_posts" class="button button-primary" value="Scan Posts">

<input type="submit" name="export_csv" class="button" value="Export CSV">

</form>

<br>

<div id="ida-progress"></div>

<?php

if(isset($_POST['scan_posts'])){
    ida_scan_posts();
}

if(isset($_POST['export_csv'])){
    ida_export_csv();
}

?>

</div>

register_activation_hook(__FILE__, 'ida_create_cache_table');

function ida_create_cache_table(){

global $wpdb;

$table = $wpdb->prefix . 'ida_image_cache';

$charset = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table (

id BIGINT AUTO_INCREMENT PRIMARY KEY,
image_url TEXT,
size_bytes BIGINT,
checked_at DATETIME

) $charset;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($sql);

}

<?php

}