<?php

function ida_export_csv(){

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=image-density-report.csv');

    $output = fopen('php://output', 'w');

    fputcsv($output, array(
        'Post ID',
        'Title',
        'Date',
        'Total Images',
        'ImgBox',
        'Other',
        'Density'
    ));

    foreach($query->posts as $post){

        preg_match_all('/<img[^>]+src="([^"]+)"/i', $post->post_content, $matches);

        $total = count($matches[1]);

        $imgbox = 0;
        $other = 0;

        foreach($matches[1] as $url){

            if(strpos($url,'imgbox') !== false){
                $imgbox++;
            } else {
                $other++;
            }

        }

        $density = ida_density_level($total);

        fputcsv($output, array(
            $post->ID,
            $post->post_title,
            $post->post_date,
            $total,
            $imgbox,
            $other,
            $density
        ));

    }

    fclose($output);
    exit;

}