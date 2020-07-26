<?php
/*
Plugin Name: new-plugin
Description: This is my first attempt on writing a custom plugin!
Version: 1.0.0 
 */

function add_admin_page()
{
    add_menu_page('NTS-Plugin', 'Nts-Plugin Menu', 'manage_options', 'nts_plugin', 'submitForm', '', '99');

}

function admin_index()
    {

        if(array_key_exists('submit_values', $_POST)){
            update_option( 'first_name_option', $_POST['fname']);
            update_option( 'last_name_option', $_POST['lname']);
            
        }

        $first_name = get_option( 'first_name_option', 'none' );
        $last_name = get_option( 'last_name_option', 'none' );

        ?>
            <form method="post" action="">
            <label for="fname">First name:</label><br>
            <input type="text" id="fname" name="fname" value=""><br>
            <label for="lname">Last name:</label><br>
            <input type="text" id="lname" name="lname" value=""><br><br>
            <input type="submit" name="submit_values" value="Submit">
            </form> 
        <?php
        
    }

function settings_link($links)
{
    $settings_link = '<a href="admin.php?page=nts_plugin">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

function updateItems(){

    global $wpdb;
    $userPosts = $wpdb->get_results( 
        "
        SELECT ID, post_title 
        FROM $wpdb->posts
        WHERE post_status = 'publish' 
            AND post_author = 1
        "
    );
    echo "<table style=width:100%><tbody>";
    echo '<tr><th>Post_ID</th><th>Posts</th></tr>';
    foreach ($userPosts as $userPost) {
    echo "<tr><td>$userPost->ID</td><td>$userPost->post_title</td></tr>";
    
    }
    echo "</table></tbody>";
   
    

}

function submitForm(){

    if(isset($_POST['xml_update_btn'])){
        update_SQL_from_XML();
        imagesTransfer();
    }

    if(isset($_POST['xml_import_btn'])){
        xmlToSql();
    }

    if(isset($_POST['test_btn'])){
        //imagesTransfer();
        //replaceurl();
        test_function();
    }

    ?>
        <form method="post" action="">
        <input type="submit" name="xml_update_btn" value="Update Database">
        </form> 
    <?php

    ?>
    <br>
    <?php

    ?>
        <form method="post" action="">
        <input type="submit" name="xml_import_btn" value="Import xml"><br><br>
        </form> 
    <?php
    
    ?>
        <form method="post" action="">
        <input type="submit" name="test_btn" value="Test">
        </form> 
    <?php
}


function test_function(){
    $image_url = 'https://placeadvisor.aserver.gr/wp-content/uploads/2020/06/3700x-img.jpg';
    $image_meta = wp_get_attachment_metadata( 285 );
    $image = basename($image_url);
    echo $image_path = pathinfo($image_url, PATHINFO_DIRNAME);
    echo '<br>';
    echo $path = str_replace('https://placeadvisor.aserver.gr', '', $image_url);
    echo '<br>';
    echo pathinfo($path, PATHINFO_DIRNAME);
    
}

function imagesTransfer(){
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    if(!$xmlFile = $wp_filesystem->get_contents('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') ) {
        echo 'Failed to read XML file for getting images';
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($xmlFile);
    $images = $dom->getElementsByTagName('img');
    $url = 'https://placeadvisor.aserver.gr';
    foreach($images as $image){
        $image_id = $image->getElementsByTagName('ID')->item(0)->nodeValue;
        $image_url = $image->getElementsByTagName('guid')->item(0)->nodeValue;
        $image_path = str_replace($url, '', $image_url);
        if(!file_exists(ABSPATH . $image_path)){
            $image_file = $wp_filesystem->get_contents($image_url);
            $wp_filesystem->put_contents(ABSPATH . $image_path, $image_file, 0644);

            $image_meta = wp_get_attachment_metadata($image_id);
        if($image_meta){
                $woocommerce_thumbnail_file = $image_meta['sizes']['woocommerce_thumbnail']['file'];
                $woocommerce_thumbnail_width = $image_meta['sizes']['woocommerce_thumbnail']['width'];
                $woocommerce_thumbnail_height = $image_meta['sizes']['woocommerce_thumbnail']['height'];
                $woocommerce_thumbnail_image = wp_get_image_editor(ABSPATH . $image_path );
                if(!is_wp_error( $woocommerce_thumbnail_image )){
                    $woocommerce_thumbnail_image->resize($woocommerce_thumbnail_width, $woocommerce_thumbnail_height);
                    $saved = $woocommerce_thumbnail_image->save(ABSPATH . pathinfo($image_path, PATHINFO_DIRNAME) . '/' . $woocommerce_thumbnail_file);
                }else{
                    print_r($saved);
                }
                $gallery_thumbnail_file = $image_meta['sizes']['woocommerce_gallery_thumbnail']['file'];
                $gallery_thumbnail_width = $image_meta['sizes']['woocommerce_gallery_thumbnail']['width'];
                $gallery_thumbnail_height = $image_meta['sizes']['woocommerce_gallery_thumbnail']['height'];
                $gallery_thumbnail_image = wp_get_image_editor(ABSPATH . $image_path );
                if(!is_wp_error( $gallery_thumbnail_image )){
                    $gallery_thumbnail_image->resize($gallery_thumbnail_width, $gallery_thumbnail_height);
                    $saved = $gallery_thumbnail_image->save(ABSPATH . pathinfo($image_path, PATHINFO_DIRNAME) . '/' . $gallery_thumbnail_file);
                }else{
                    print_r($saved);
                }
                $shop_catalog_file = $image_meta['sizes']['shop_catalog']['file'];
                $shop_catalog_width = $image_meta['sizes']['shop_catalog']['width'];
                $shop_catalog_height = $image_meta['sizes']['shop_catalog']['height'];
                $shop_catalog_image = wp_get_image_editor(ABSPATH . $image_path );
                if(!is_wp_error( $shop_catalog_image )){
                    $shop_catalog_image->resize($shop_catalog_width, $shop_catalog_height);
                    $saved = $shop_catalog_image->save(ABSPATH . pathinfo($image_path, PATHINFO_DIRNAME) . '/' . $shop_catalog_file);
                }else{
                    print_r($saved);
                }
                $thumbnail_file = $image_meta['sizes']['thumbnail']['file'];
                $thumbnail_width = $image_meta['sizes']['thumbnail']['width'];
                $thumbnail_height = $image_meta['sizes']['thumbnail']['height'];
                $thumbnail_image = wp_get_image_editor(ABSPATH . $image_path );
                if(!is_wp_error( $thumbnail_image )){
                    $thumbnail_image->resize($thumbnail_width, $thumbnail_height);
                    $saved = $thumbnail_image->save(ABSPATH . pathinfo($image_path, PATHINFO_DIRNAME) . '/' . $thumbnail_file);
                }else{
                    print_r($saved);
                }
                $shop_thumbnail_file = $image_meta['sizes']['shop_thumbnail']['file'];
                $shop_thumbnail_width = $image_meta['sizes']['shop_thumbnail']['width'];
                $shop_thumbnail_height = $image_meta['sizes']['shop_thumbnail']['height'];
                $shop_thumbnail_image = wp_get_image_editor(ABSPATH . $image_path );
                if(!is_wp_error( $shop_thumbnail_image )){
                    $shop_thumbnail_image->resize($shop_thumbnail_width, $shop_thumbnail_height);
                    $saved = $shop_thumbnail_image->save(ABSPATH . pathinfo($image_path, PATHINFO_DIRNAME) . '/' . $shop_thumbnail_file);
                }else{
                    print_r($saved);
                }
            }
        }
    }
}

function xmlToSql(){
    
    global $wpdb;
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->load('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') or die("Cant load xml file");
    $posts_elements = $dom->getElementsByTagName('product');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'ID' => $post_element->getElementsByTagName('ID')->item(0)->nodeValue,
            'post_author' => $post_element->getElementsByTagName('post_author')->item(0)->nodeValue,
            'post_date' => $post_element->getElementsByTagName('post_date')->item(0)->nodeValue,
            'post_date_gmt' => $post_element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
            'post_content' => $post_element->getElementsByTagName('post_content')->item(0)->nodeValue,
            'post_title' => $post_element->getElementsByTagName('post_title')->item(0)->nodeValue,
            'post_excerpt' => $post_element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
            'post_status' => $post_element->getElementsByTagName('post_status')->item(0)->nodeValue,
            'comment_status' => $post_element->getElementsByTagName('comment_status')->item(0)->nodeValue,
            'ping_status' => $post_element->getElementsByTagName('ping_status')->item(0)->nodeValue,
            'post_password' => $post_element->getElementsByTagName('post_password')->item(0)->nodeValue,
            'post_name' => $post_element->getElementsByTagName('post_name')->item(0)->nodeValue,
            'to_ping' => $post_element->getElementsByTagName('to_ping')->item(0)->nodeValue,
            'pinged' => $post_element->getElementsByTagName('pinged')->item(0)->nodeValue,
            'post_modified' => $post_element->getElementsByTagName('post_modified')->item(0)->nodeValue,
            'post_modified_gmt' => $post_element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
            'post_content_filtered' => $post_element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
            'post_parent' => $post_element->getElementsByTagName('post_parent')->item(0)->nodeValue,
            'guid' => str_replace('https://placeadvisor.aserver.gr', 'https://ecolife.aserver.gr', $post_element->getElementsByTagName('guid')->item(0)->nodeValue),
            'menu_order' => $post_element->getElementsByTagName('menu_order')->item(0)->nodeValue,
            'post_type' => $post_element->getElementsByTagName('post_type')->item(0)->nodeValue,
            'post_mime_type' => $post_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
            'comment_count' => $post_element->getElementsByTagName('comment_count')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_posts', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('img');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'ID' => $post_element->getElementsByTagName('ID')->item(0)->nodeValue,
            'post_author' => $post_element->getElementsByTagName('post_author')->item(0)->nodeValue,
            'post_date' => $post_element->getElementsByTagName('post_date')->item(0)->nodeValue,
            'post_date_gmt' => $post_element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
            'post_content' => $post_element->getElementsByTagName('post_content')->item(0)->nodeValue,
            'post_title' => $post_element->getElementsByTagName('post_title')->item(0)->nodeValue,
            'post_excerpt' => $post_element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
            'post_status' => $post_element->getElementsByTagName('post_status')->item(0)->nodeValue,
            'comment_status' => $post_element->getElementsByTagName('comment_status')->item(0)->nodeValue,
            'ping_status' => $post_element->getElementsByTagName('ping_status')->item(0)->nodeValue,
            'post_password' => $post_element->getElementsByTagName('post_password')->item(0)->nodeValue,
            'post_name' => $post_element->getElementsByTagName('post_name')->item(0)->nodeValue,
            'to_ping' => $post_element->getElementsByTagName('to_ping')->item(0)->nodeValue,
            'pinged' => $post_element->getElementsByTagName('pinged')->item(0)->nodeValue,
            'post_modified' => $post_element->getElementsByTagName('post_modified')->item(0)->nodeValue,
            'post_modified_gmt' => $post_element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
            'post_content_filtered' => $post_element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
            'post_parent' => $post_element->getElementsByTagName('post_parent')->item(0)->nodeValue,
            'guid' => str_replace('https://placeadvisor.aserver.gr', 'https://ecolife.aserver.gr', $post_element->getElementsByTagName('guid')->item(0)->nodeValue),
            'menu_order' => $post_element->getElementsByTagName('menu_order')->item(0)->nodeValue,
            'post_type' => $post_element->getElementsByTagName('post_type')->item(0)->nodeValue,
            'post_mime_type' => $post_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
            'comment_count' => $post_element->getElementsByTagName('comment_count')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_posts', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('product_meta');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'product_id' => $post_element->getElementsByTagName('product_id')->item(0)->nodeValue,
            'sku' => $post_element->getElementsByTagName('sku')->item(0)->nodeValue,
            'virtual' => $post_element->getElementsByTagName('virtual')->item(0)->nodeValue,
            'downloadable' => $post_element->getElementsByTagName('downloadable')->item(0)->nodeValue,
            'min_price' => $post_element->getElementsByTagName('min_price')->item(0)->nodeValue,
            'max_price' => $post_element->getElementsByTagName('max_price')->item(0)->nodeValue,
            'onsale' => $post_element->getElementsByTagName('onsale')->item(0)->nodeValue,
            'stock_quantity' => $post_element->getElementsByTagName('stock_quantity')->item(0)->nodeValue,
            'stock_status' => $post_element->getElementsByTagName('stock_status')->item(0)->nodeValue,
            'rating_count' => $post_element->getElementsByTagName('rating_count')->item(0)->nodeValue,
            'average_rating' => $post_element->getElementsByTagName('average_rating')->item(0)->nodeValue,
            'total_sales' => $post_element->getElementsByTagName('total_sales')->item(0)->nodeValue,
            'tax_status' => $post_element->getElementsByTagName('tax_status')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_wc_product_meta_lookup', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('term');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'term_id' => $post_element->getElementsByTagName('term_id')->item(0)->nodeValue,
            'name' => $post_element->getElementsByTagName('name')->item(0)->nodeValue,
            'slug' => $post_element->getElementsByTagName('slug')->item(0)->nodeValue,
            'term_group' => $post_element->getElementsByTagName('term_group')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_terms', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('term_relationship');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'object_id' => $post_element->getElementsByTagName('object_id')->item(0)->nodeValue,
            'term_taxonomy_id' => $post_element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue,
            'term_order' => $post_element->getElementsByTagName('term_order')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_term_relationships', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('term_taxonomy');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'term_taxonomy_id' => $post_element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue,
            'term_id' => $post_element->getElementsByTagName('term_id')->item(0)->nodeValue,
            'taxonomy' => $post_element->getElementsByTagName('taxonomy')->item(0)->nodeValue,
            'description' => $post_element->getElementsByTagName('description')->item(0)->nodeValue,
            'parent' => $post_element->getElementsByTagName('parent')->item(0)->nodeValue,
            'count' => $post_element->getElementsByTagName('count')->item(0)->nodeValue,
        );
        $wpdb->insert('wpij_term_taxonomy', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('post_meta');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'meta_id' => $post_element->getElementsByTagName('meta_id')->item(0)->nodeValue,
            'post_id' => $post_element->getElementsByTagName('post_id')->item(0)->nodeValue,
            'meta_key' => $post_element->getElementsByTagName('meta_key')->item(0)->nodeValue,
            'meta_value' => $post_element->getElementsByTagName('meta_value')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_postmeta', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('term_meta');
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'meta_id' => $post_element->getElementsByTagName('meta_id')->item(0)->nodeValue,
            'term_id' => $post_element->getElementsByTagName('term_id')->item(0)->nodeValue,
            'meta_key' => $post_element->getElementsByTagName('meta_key')->item(0)->nodeValue,
            'meta_value' => $post_element->getElementsByTagName('meta_value')->item(0)->nodeValue
        );
        $wpdb->insert('wpij_postmeta', $new_insert);
    }

    $posts_elements = $dom->getElementsByTagName('attribute_taxonomy');
    $options = array();
    foreach($posts_elements as $post_element){
        $new_insert = array(
            'attribute_id' => $post_element->getElementsByTagName('attribute_id')->item(0)->nodeValue,
            'attribute_name' => $post_element->getElementsByTagName('attribute_name')->item(0)->nodeValue,
            'attribute_label' => $post_element->getElementsByTagName('attribute_label')->item(0)->nodeValue,
            'attribute_type' => $post_element->getElementsByTagName('attribute_type')->item(0)->nodeValue,
            'attribute_orderby' => $post_element->getElementsByTagName('attribute_orderby')->item(0)->nodeValue,
            'attribute_public' => $post_element->getElementsByTagName('attribute_public')->item(0)->nodeValue
        );
        array_push($options, (object)$new_insert);
        $wpdb->insert('wpij_woocommerce_attribute_taxonomies', $new_insert);
    }
    update_option( '_transient_wc_attribute_taxonomies', $options, yes );
}
// Checks for new inserts and updates from XML file to mySQL DB
function xmlToSqlNewInserts(){
    global $wpdb; 
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read xml file for checking inserts';
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($xmlFile);
    $elements = $dom->getElementsByTagName('product');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('ID')->item(0)->nodeValue;
        if($db_timestamp = $wpdb->get_results("SELECT ID, post_modified FROM `wpij_posts` WHERE ID = $element_id")){
            $xml_timestamp = $element->getElementsByTagName('post_modified')->item(0)->nodeValue;
            if(new DateTime($xml_timestamp) > new DateTime($db_timestamp[0]->post_modified)){
                $updates = array(
                'ID' => $element->getElementsByTagName('ID')->item(0)->nodeValue,
                'post_author' => $element->getElementsByTagName('post_author')->item(0)->nodeValue,
                'post_date' => $element->getElementsByTagName('post_date')->item(0)->nodeValue,
                'post_date_gmt' => $element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
                'post_content' => $element->getElementsByTagName('post_content')->item(0)->nodeValue,
                'post_title' => $element->getElementsByTagName('post_title')->item(0)->nodeValue,
                'post_excerpt' => $element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
                'post_status' => $element->getElementsByTagName('post_status')->item(0)->nodeValue,
                'comment_status' => $element->getElementsByTagName('comment_status')->item(0)->nodeValue,
                'ping_status' => $element->getElementsByTagName('ping_status')->item(0)->nodeValue,
                'post_password' => $element->getElementsByTagName('post_password')->item(0)->nodeValue,
                'post_name' => $element->getElementsByTagName('post_name')->item(0)->nodeValue,
                'to_ping' => $element->getElementsByTagName('to_ping')->item(0)->nodeValue,
                'pinged' => $element->getElementsByTagName('pinged')->item(0)->nodeValue,
                'post_modified' => $element->getElementsByTagName('post_modified')->item(0)->nodeValue,
                'post_modified_gmt' => $element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
                'post_content_filtered' => $element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
                'post_parent' => $element->getElementsByTagName('post_parent')->item(0)->nodeValue,
                'guid' => str_replace('https://placeadvisor.aserver.gr', 'https://ecolife.aserver.gr', $element->getElementsByTagName('guid')->item(0)->nodeValue),
                'menu_order' => $element->getElementsByTagName('menu_order')->item(0)->nodeValue,
                'post_type' => $element->getElementsByTagName('post_type')->item(0)->nodeValue,
                'post_mime_type' => $element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
                'comment_count' => $element->getElementsByTagName('comment_count')->item(0)->nodeValue
                );
                $wpdb->update('wpij_posts', $updates, array('ID' => $element_id));
            }
        }else{
                $new_insert = array(
                'ID' => $element->getElementsByTagName('ID')->item(0)->nodeValue,
                'post_author' => $element->getElementsByTagName('post_author')->item(0)->nodeValue,
                'post_date' => $element->getElementsByTagName('post_date')->item(0)->nodeValue,
                'post_date_gmt' => $element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
                'post_content' => $element->getElementsByTagName('post_content')->item(0)->nodeValue,
                'post_title' => $element->getElementsByTagName('post_title')->item(0)->nodeValue,
                'post_excerpt' => $element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
                'post_status' => $element->getElementsByTagName('post_status')->item(0)->nodeValue,
                'comment_status' => $element->getElementsByTagName('comment_status')->item(0)->nodeValue,
                'ping_status' => $element->getElementsByTagName('ping_status')->item(0)->nodeValue,
                'post_password' => $element->getElementsByTagName('post_password')->item(0)->nodeValue,
                'post_name' => $element->getElementsByTagName('post_name')->item(0)->nodeValue,
                'to_ping' => $element->getElementsByTagName('to_ping')->item(0)->nodeValue,
                'pinged' => $element->getElementsByTagName('pinged')->item(0)->nodeValue,
                'post_modified' => $element->getElementsByTagName('post_modified')->item(0)->nodeValue,
                'post_modified_gmt' => $element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
                'post_content_filtered' => $element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
                'post_parent' => $element->getElementsByTagName('post_parent')->item(0)->nodeValue,
                'guid' => str_replace('https://placeadvisor.aserver.gr', 'https://ecolife.aserver.gr', $element->getElementsByTagName('guid')->item(0)->nodeValue),
                'menu_order' => $element->getElementsByTagName('menu_order')->item(0)->nodeValue,
                'post_type' => $element->getElementsByTagName('post_type')->item(0)->nodeValue,
                'post_mime_type' => $element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
                'comment_count' => $element->getElementsByTagName('comment_count')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_posts', $new_insert);
        }
        
    }

    $elements = $dom->getElementsByTagName('img');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('ID')->item(0)->nodeValue;
        if($db_timestamp = $wpdb->get_results("SELECT ID, post_modified FROM `wpij_posts` WHERE ID = $element_id")){
            $xml_timestamp = $element->getElementsByTagName('post_modified')->item(0)->nodeValue;
            if(new DateTime($xml_timestamp) > new DateTime($db_timestamp[0]->post_modified)){
                $updates = array(
                'ID' => $element->getElementsByTagName('ID')->item(0)->nodeValue,
                'post_author' => $element->getElementsByTagName('post_author')->item(0)->nodeValue,
                'post_date' => $element->getElementsByTagName('post_date')->item(0)->nodeValue,
                'post_date_gmt' => $element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
                'post_content' => $element->getElementsByTagName('post_content')->item(0)->nodeValue,
                'post_title' => $element->getElementsByTagName('post_title')->item(0)->nodeValue,
                'post_excerpt' => $element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
                'post_status' => $element->getElementsByTagName('post_status')->item(0)->nodeValue,
                'comment_status' => $element->getElementsByTagName('comment_status')->item(0)->nodeValue,
                'ping_status' => $element->getElementsByTagName('ping_status')->item(0)->nodeValue,
                'post_password' => $element->getElementsByTagName('post_password')->item(0)->nodeValue,
                'post_name' => $element->getElementsByTagName('post_name')->item(0)->nodeValue,
                'to_ping' => $element->getElementsByTagName('to_ping')->item(0)->nodeValue,
                'pinged' => $element->getElementsByTagName('pinged')->item(0)->nodeValue,
                'post_modified' => $element->getElementsByTagName('post_modified')->item(0)->nodeValue,
                'post_modified_gmt' => $element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
                'post_content_filtered' => $element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
                'post_parent' => $element->getElementsByTagName('post_parent')->item(0)->nodeValue,
                'guid' => str_replace('https://placeadvisor.aserver.gr', 'https://ecolife.aserver.gr', $element->getElementsByTagName('guid')->item(0)->nodeValue),
                'menu_order' => $element->getElementsByTagName('menu_order')->item(0)->nodeValue,
                'post_type' => $element->getElementsByTagName('post_type')->item(0)->nodeValue,
                'post_mime_type' => $element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
                'comment_count' => $element->getElementsByTagName('comment_count')->item(0)->nodeValue
                );
                $wpdb->update('wpij_posts', $updates, array('ID' => $element_id));
            }
        }else{
                $new_insert = array(
                'ID' => $element->getElementsByTagName('ID')->item(0)->nodeValue,
                'post_author' => $element->getElementsByTagName('post_author')->item(0)->nodeValue,
                'post_date' => $element->getElementsByTagName('post_date')->item(0)->nodeValue,
                'post_date_gmt' => $element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
                'post_content' => $element->getElementsByTagName('post_content')->item(0)->nodeValue,
                'post_title' => $element->getElementsByTagName('post_title')->item(0)->nodeValue,
                'post_excerpt' => $element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
                'post_status' => $element->getElementsByTagName('post_status')->item(0)->nodeValue,
                'comment_status' => $element->getElementsByTagName('comment_status')->item(0)->nodeValue,
                'ping_status' => $element->getElementsByTagName('ping_status')->item(0)->nodeValue,
                'post_password' => $element->getElementsByTagName('post_password')->item(0)->nodeValue,
                'post_name' => $element->getElementsByTagName('post_name')->item(0)->nodeValue,
                'to_ping' => $element->getElementsByTagName('to_ping')->item(0)->nodeValue,
                'pinged' => $element->getElementsByTagName('pinged')->item(0)->nodeValue,
                'post_modified' => $element->getElementsByTagName('post_modified')->item(0)->nodeValue,
                'post_modified_gmt' => $element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
                'post_content_filtered' => $element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
                'post_parent' => $element->getElementsByTagName('post_parent')->item(0)->nodeValue,
                'guid' => str_replace('https://placeadvisor.aserver.gr', 'https://ecolife.aserver.gr', $element->getElementsByTagName('guid')->item(0)->nodeValue),
                'menu_order' => $element->getElementsByTagName('menu_order')->item(0)->nodeValue,
                'post_type' => $element->getElementsByTagName('post_type')->item(0)->nodeValue,
                'post_mime_type' => $element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
                'comment_count' => $element->getElementsByTagName('comment_count')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_posts', $new_insert);
        }
    }

    $elements = $dom->getElementsByTagName('product_meta');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('product_id')->item(0)->nodeValue;
        if($wpdb->get_results("SELECT product_id  FROM `wpij_wc_product_meta_lookup` WHERE product_id = $element_id")){
                $updates = array(
                'product_id' => $element->getElementsByTagName('product_id')->item(0)->nodeValue,
                'sku' => $element->getElementsByTagName('sku')->item(0)->nodeValue,
                'virtual' => $element->getElementsByTagName('virtual')->item(0)->nodeValue,
                'downloadable' => $element->getElementsByTagName('downloadable')->item(0)->nodeValue,
                'min_price' => $element->getElementsByTagName('min_price')->item(0)->nodeValue,
                'max_price' => $element->getElementsByTagName('max_price')->item(0)->nodeValue,
                'onsale' => $element->getElementsByTagName('onsale')->item(0)->nodeValue,
                'stock_quantity' => $element->getElementsByTagName('stock_quantity')->item(0)->nodeValue,
                'stock_status' => $element->getElementsByTagName('stock_status')->item(0)->nodeValue,
                'rating_count' => $element->getElementsByTagName('rating_count')->item(0)->nodeValue,
                'average_rating' => $element->getElementsByTagName('average_rating')->item(0)->nodeValue,
                'total_sales' => $element->getElementsByTagName('total_sales')->item(0)->nodeValue,
                'tax_status' => $element->getElementsByTagName('tax_status')->item(0)->nodeValue,
                'tax_class' => $element->getElementsByTagName('tax_class')->item(0)->nodeValue
                );
                $wpdb->update('wpij_wc_product_meta_lookup', $updates, array('product_id' => $element_id));
            
        }else{
                $new_insert = array(
                    'product_id' => $element->getElementsByTagName('product_id')->item(0)->nodeValue,
                    'sku' => $element->getElementsByTagName('sku')->item(0)->nodeValue,
                    'virtual' => $element->getElementsByTagName('virtual')->item(0)->nodeValue,
                    'downloadable' => $element->getElementsByTagName('downloadable')->item(0)->nodeValue,
                    'min_price' => $element->getElementsByTagName('min_price')->item(0)->nodeValue,
                    'max_price' => $element->getElementsByTagName('max_price')->item(0)->nodeValue,
                    'onsale' => $element->getElementsByTagName('onsale')->item(0)->nodeValue,
                    'stock_quantity' => $element->getElementsByTagName('stock_quantity')->item(0)->nodeValue,
                    'stock_status' => $element->getElementsByTagName('stock_status')->item(0)->nodeValue,
                    'rating_count' => $element->getElementsByTagName('rating_count')->item(0)->nodeValue,
                    'average_rating' => $element->getElementsByTagName('average_rating')->item(0)->nodeValue,
                    'total_sales' => $element->getElementsByTagName('total_sales')->item(0)->nodeValue,
                    'tax_status' => $element->getElementsByTagName('tax_status')->item(0)->nodeValue,
                    'tax_class' => $element->getElementsByTagName('tax_class')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_wc_product_meta_lookup', $new_insert);
        }
        
    }

    $elements = $dom->getElementsByTagName('term');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('term_id')->item(0)->nodeValue;
        if($wpdb->get_results("SELECT term_id  FROM `wpij_terms` WHERE term_id = $element_id")){
            
                $updates = array(
                'name' => $element->getElementsByTagName('name')->item(0)->nodeValue,
                'slug' => $element->getElementsByTagName('slug')->item(0)->nodeValue,
                'term_group' => $element->getElementsByTagName('term_group')->item(0)->nodeValue
                );
                $wpdb->update('wpij_terms', $updates, array('term_id' => $element_id));
            
        }else{
                $new_insert = array(
                'term_id' => $element->getElementsByTagName('term_id')->item(0)->nodeValue,
                'name' => $element->getElementsByTagName('name')->item(0)->nodeValue,
                'slug' => $element->getElementsByTagName('slug')->item(0)->nodeValue,
                'term_group' => $element->getElementsByTagName('term_group')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_terms', $new_insert);
        }
        
    }

    $elements = $dom->getElementsByTagName('term_relationship');
    foreach($elements as $element){
        $object_id = $element->getElementsByTagName('object_id')->item(0)->nodeValue;
        $term_taxonomy_id = $element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue;
        if(!($wpdb->get_results("SELECT object_id, term_taxonomy_id FROM `wpij_term_relationships` WHERE object_id = $object_id AND term_taxonomy_id = $term_taxonomy_id"))){
                $new_insert = array(
                'object_id' => $element->getElementsByTagName('object_id')->item(0)->nodeValue,
                'term_taxonomy_id' => $element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue,
                'term_order' => $element->getElementsByTagName('term_order')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_term_relationships', $new_insert);
        }
        
    }

    $elements = $dom->getElementsByTagName('term_taxonomy');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue;
        if($wpdb->get_results("SELECT term_taxonomy_id  FROM `wpij_term_taxonomy` WHERE term_taxonomy_id = $element_id")){
            
                $updates = array(
                'term_taxonomy_id' => $element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue,
                'term_id' => $element->getElementsByTagName('term_id')->item(0)->nodeValue,
                'taxonomy' => $element->getElementsByTagName('taxonomy')->item(0)->nodeValue,
                'description' => $element->getElementsByTagName('description')->item(0)->nodeValue,
                'parent' => $element->getElementsByTagName('parent')->item(0)->nodeValue,
                'count' => $element->getElementsByTagName('count')->item(0)->nodeValue
                );
                $wpdb->update('wpij_term_taxonomy', $updates, array('term_taxonomy_id' => $element_id));
            
        }else{
                $new_insert = array(
                    'term_taxonomy_id' => $element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue,
                    'term_id' => $element->getElementsByTagName('term_id')->item(0)->nodeValue,
                    'taxonomy' => $element->getElementsByTagName('taxonomy')->item(0)->nodeValue,
                    'description' => $element->getElementsByTagName('description')->item(0)->nodeValue,
                    'parent' => $element->getElementsByTagName('parent')->item(0)->nodeValue,
                    'count' => $element->getElementsByTagName('count')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_term_taxonomy', $new_insert);
        }
        
    }

    $elements = $dom->getElementsByTagName('post_meta');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('meta_id')->item(0)->nodeValue;
        if($wpdb->get_results("SELECT meta_id  FROM `wpij_postmeta` WHERE meta_id = $element_id")){
            
                $updates = array(
                'meta_id' => $element->getElementsByTagName('meta_id')->item(0)->nodeValue,
                'post_id' => $element->getElementsByTagName('post_id')->item(0)->nodeValue,
                'meta_key' => $element->getElementsByTagName('meta_key')->item(0)->nodeValue,
                'meta_value' => $element->getElementsByTagName('meta_value')->item(0)->nodeValue
                );
                $wpdb->update('wpij_postmeta', $updates, array('meta_id' => $element_id));
            
        }else{
                $new_insert = array(
                    'meta_id' => $element->getElementsByTagName('meta_id')->item(0)->nodeValue,
                    'post_id' => $element->getElementsByTagName('post_id')->item(0)->nodeValue,
                    'meta_key' => $element->getElementsByTagName('meta_key')->item(0)->nodeValue,
                    'meta_value' => $element->getElementsByTagName('meta_value')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_postmeta', $new_insert);
        }
        
    }
    
    $elements = $dom->getElementsByTagName('term_meta');
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('meta_id')->item(0)->nodeValue;
        if($wpdb->get_results("SELECT meta_id  FROM `wpij_termmeta` WHERE meta_id = $element_id")){
            
                $updates = array(
                'meta_id' => $element->getElementsByTagName('meta_id')->item(0)->nodeValue,
                'term_id' => $element->getElementsByTagName('term_id')->item(0)->nodeValue,
                'meta_key' => $element->getElementsByTagName('meta_key')->item(0)->nodeValue,
                'meta_value' => $element->getElementsByTagName('meta_value')->item(0)->nodeValue
                );
                $wpdb->update('wpij_termmeta', $updates, array('meta_id' => $element_id));
            
        }else{
                $new_insert = array(
                    'meta_id' => $element->getElementsByTagName('meta_id')->item(0)->nodeValue,
                    'term_id' => $element->getElementsByTagName('term_id')->item(0)->nodeValue,
                    'meta_key' => $element->getElementsByTagName('meta_key')->item(0)->nodeValue,
                    'meta_value' => $element->getElementsByTagName('meta_value')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_termmeta', $new_insert);
        }
    }

    $elements = $dom->getElementsByTagName('attribute_taxonomy');
    $options = array();
    foreach($elements as $element){
        $element_id = $element->getElementsByTagName('attribute_id')->item(0)->nodeValue;
        if($wpdb->get_results("SELECT attribute_id  FROM `wpij_woocommerce_attribute_taxonomies` WHERE attribute_id = $element_id")){
            
                $updates = array(
                'attribute_id' => $element->getElementsByTagName('attribute_id')->item(0)->nodeValue,
                'attribute_name' => $element->getElementsByTagName('attribute_name')->item(0)->nodeValue,
                'attribute_label' => $element->getElementsByTagName('attribute_label')->item(0)->nodeValue,
                'attribute_type' => $element->getElementsByTagName('attribute_type')->item(0)->nodeValue,
                'attribute_orderby' => $element->getElementsByTagName('attribute_orderby')->item(0)->nodeValue,
                'attribute_public' => $element->getElementsByTagName('attribute_public')->item(0)->nodeValue
                );
                $wpdb->update('wpij_woocommerce_attribute_taxonomies', $updates, array('attribute_id' => $element_id));
            
        }else{
                $new_insert = array(
                'attribute_id' => $element->getElementsByTagName('attribute_id')->item(0)->nodeValue,
                'attribute_name' => $element->getElementsByTagName('attribute_name')->item(0)->nodeValue,
                'attribute_label' => $element->getElementsByTagName('attribute_label')->item(0)->nodeValue,
                'attribute_type' => $element->getElementsByTagName('attribute_type')->item(0)->nodeValue,
                'attribute_orderby' => $element->getElementsByTagName('attribute_orderby')->item(0)->nodeValue,
                'attribute_public' => $element->getElementsByTagName('attribute_public')->item(0)->nodeValue
                );
                $wpdb->insert('wpij_woocommerce_attribute_taxonomies', $new_insert);
        }
        array_push($options, (object)array(
            'attribute_id' => $element->getElementsByTagName('attribute_id')->item(0)->nodeValue,
            'attribute_name' => $element->getElementsByTagName('attribute_name')->item(0)->nodeValue,
            'attribute_label' => $element->getElementsByTagName('attribute_label')->item(0)->nodeValue,
            'attribute_type' => $element->getElementsByTagName('attribute_type')->item(0)->nodeValue,
            'attribute_orderby' => $element->getElementsByTagName('attribute_orderby')->item(0)->nodeValue,
            'attribute_public' => $element->getElementsByTagName('attribute_public')->item(0)->nodeValue
        )
    );
    }
    update_option( '_transient_wc_attribute_taxonomies', $options, true );
    
}

function xmlTSqlDeletions(){
    global $wpdb;
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read xml file for Deletions';
    }

    $dom = new DomDocument('1.0', 'UTF-8');
    $dom->loadXML($xmlFile);
    $xpath = new DOMXPath($dom);
    $db_inserts = $wpdb->get_results("SELECT ID FROM wpij_posts WHERE post_type = 'product' OR post_type = 'product_variation'ORDER BY ID ASC");
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/products/product/ID[text() = $db_insert->ID]/..")) == 0){
            $wpdb->delete('wpij_posts', array('ID'=>$db_insert->ID));
        } 
    }

    $db_inserts = $wpdb->get_results("SELECT ID FROM wpij_posts WHERE post_type = 'attachment' ORDER BY ID ASC");
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/imgs/img/ID[text() = $db_insert->ID]/..")) == 0){
            $wpdb->delete('wpij_posts', array('ID'=>$db_insert->ID));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT product_id FROM wpij_wc_product_meta_lookup ORDER BY product_id ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/products_meta/product_meta/product_id[text() = $db_insert->product_id]/..")) == 0){
            $wpdb->delete('wpij_wc_product_meta_lookup', array('product_id'=>$db_insert->product_id));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT term_id FROM wpij_terms ORDER BY term_id ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/terms/term/term_id[text() = $db_insert->term_id]/..")) == 0){
            $wpdb->delete('wpij_terms', array('term_id'=>$db_insert->term_id));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT object_id, term_taxonomy_id FROM wpij_term_relationships ORDER BY object_id ASC');
    foreach($db_inserts as $db_insert){
        $object_id = (int)$db_insert->object_id;
        $term_taxonmy_id = (int)$db_insert->term_taxonomy_id;
        if(count($xpath->query("/wphr_posts/term_relationships/term_relationship[object_id = $object_id and term_taxonomy_id = $term_taxonmy_id]")) == 0){
            $wpdb->delete('wpij_term_relationships', array('object_id'=>$db_insert->object_id, 'term_taxonomy_id'=>$db_insert->term_taxonomy_id));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT term_taxonomy_id FROM wpij_term_taxonomy ORDER BY term_taxonomy_id ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/term_taxonomies/term_taxonomy/term_taxonomy_id[text() = $db_insert->term_taxonomy_id]/..")) == 0){
            $wpdb->delete('wpij_term_taxonomy', array('term_taxonomy_id'=>$db_insert->term_taxonomy_id));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT meta_id FROM wpij_postmeta ORDER BY meta_id ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/posts_meta/post_meta/meta_id[text() = $db_insert->meta_id]/..")) == 0){
            $wpdb->delete('wpij_postmeta', array('meta_id'=>$db_insert->meta_id));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT meta_id FROM wpij_termmeta ORDER BY meta_id ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/terms_meta/term_meta/meta_id[text() = $db_insert->meta_id]/..")) == 0){
            $wpdb->delete('wpij_termmeta', array('meta_id'=>$db_insert->meta_id));
        } 
    }

    $db_inserts = $wpdb->get_results('SELECT attribute_id FROM wpij_woocommerce_attribute_taxonomies ORDER BY attribute_id ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/attribute_taxonomies/attribute_taxonomy/attribute_id[text() = $db_insert->attribute_id]/..")) == 0){
            $wpdb->delete('wpij_woocommerce_attribute_taxonomies', array('attribute_id'=>$db_insert->attribute_id));
        } 
    }
    // Option does not need to be deleted
}

function readSQLFile($fileUrl, $filePath){
    $file = fopen($fileUrl, "r");
    global $wpdb;
    while(! feof($file)){
        $line = fgets($file);
        $wpdb->query($line);
    }
    fclose($file);
    unlink($filePath);
}

function readCSVFile($fileUrl, $filePath){
    
    $file = fopen($fileUrl,"r");
    $firstLine = fgets($file);
    while(!feof($file))
    {
    
    $line = fgets($file);
    
    $pieces = explode(",", $line);
   
    makeNewTable($pieces);
    }
    fclose($file);
    unlink( $filePath );
}

function makeNewTable($line){
    
    global $wpdb;
    $wpdb->insert('myTable', array('id' => $line[0], 'first_name' => $line[1],'last_name' => $line[2], 'email' => $line[3], 'gender' => $line[4]) );
}

function add_new_mime(){
    $mimes['sql'] = 'text/plain';
    $mimes['csv'] = 'text/plain';
    $mimes['jpg'] = 'image/jpeg';
    $mimes['png'] = 'image/png';
    return $mimes;
}

function update_SQL_from_XML(){
    xmlToSqlNewInserts();
    xmlTSqlDeletions();
    imagesTransfer();
    
}

function customTime($schedules){
    $schedules['everysecond'] = array(
        'interval'  => 60, 
        'display'   => 'Every 1 Minute'
    );
    return $schedules;
}

function schedule_job(){
    if(!wp_next_scheduled( 'cron_job_db'))
        wp_schedule_event( time(), 'everysecond', 'cron_job_db');
}

 $pluginFile = plugin_basename(__FILE__);
 add_filter( 'mime_types','add_new_mime');
 add_filter('plugin_action_links_' . $pluginFile, 'settings_link');

 add_filter( 'cron_schedules', 'customTime');
 add_action( 'wp_loaded', 'schedule_job');
 add_action( 'cron_job_db', 'update_SQL_from_XML');
 //register_activation_hook( $pluginFile, 'xmlToSql' );
 
 add_action('admin_menu', 'add_admin_page');
 add_shortcode( 'database', 'updateItems' );
 add_shortcode( 'submitform', 'submitForm' );
 
?>