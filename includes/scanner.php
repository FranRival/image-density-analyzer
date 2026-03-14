<?php

function ida_scan_posts(){

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish'
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
    <th>Density</th>
    </tr>
    </thead>";

    foreach($query->posts as $post){

        $content = $post->post_content;

        preg_match_all('/<img[^>]+src="([^"]+)"/i', $content, $matches);

        $total = count($matches[1]);

        $imgbox = 0;
        $other = 0;

        foreach($matches[1] as $url){

            if(strpos($url, 'imgbox') !== false){
                $imgbox++;
            } else {
                $other++;
            }

        }

        $density = ida_density_level($total);

        echo "<tr>
        <td>{$post->ID}</td>
        <td>{$post->post_title}</td>
        <td>{$post->post_date}</td>
        <td>{$total}</td>
        <td>{$imgbox}</td>
        <td>{$other}</td>
        <td>{$density}</td>
        </tr>";

    }

    echo "</table>";

}

function ida_density_level($total){

    if($total <= 20){
        return 'NORMAL';
    }

    if($total <= 40){
        return 'MEDIUM';
    }

    if($total <= 80){
        return 'HIGH';
    }

    return 'CRITICAL';

}