<?php
/*
Plugin Name: Image Density Analyzer
Description: Analiza la densidad de imágenes por post para detectar posts sobrecargados.
Version: 1.0
Author: Emmanuel
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/scanner.php';
require_once plugin_dir_path(__FILE__) . 'includes/export.php';

add_action('admin_menu', 'ida_add_menu');

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

        <?php

        if(isset($_POST['scan_posts'])){
            ida_scan_posts();
        }

        if(isset($_POST['export_csv'])){
            ida_export_csv();
        }

        ?>

    </div>
    <?php

}