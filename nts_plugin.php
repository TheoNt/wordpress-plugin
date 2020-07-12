<?php
/*
Plugin Name: nts-plugin
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

    if(isset($_POST['newPosts_btn'])){
        update_XML_from_sql();
    }

    if(isset($_POST['export_btn'])){
        //sqlToXml();
        //get_terms_taxonomies();
        //get_wp_posts_metas();
        //get_wp_term_relations();
        get_products();
    }

    ?>
        <form method="post" action="">
        <input type="submit" name="newPosts_btn" value="Check New Posts"><br><br>
        </form> 
    <?php

    ?>
        <form method="post" action="">
        <input type="submit" name="export_btn" value="Export to XML"><br><br>
        </form> 
    <?php

}

function get_products(){
    global $wpdb;
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents(ABSPATH .  '/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read img files';
    }
    
    $images = $wpdb->get_results("SELECT * FROM wphr_posts WHERE post_type = 'product'");
    foreach($images as $image){
            // attachment
            echo 'ID: ' . $image->ID . ' img->';
            echo (get_post_meta( $image->ID, '_thumbnail_id', true));
            echo '<br>';
        
    }
    
}

function get_terms_taxonomies(){
    $taxonomy_objects = get_object_taxonomies( 'product', 'names');
    print_r( $taxonomy_objects);
}

function get_wp_term_relations(){
    global $wpdb;
    
    $array_term_id = array();
    $wp_terms = get_wp_terms();
    foreach($wp_terms->terms as $term){
        array_push($array_term_id, $term->term_id);
    }
    $min_term_id = min($array_term_id);
    $max_term_id = max($array_term_id);

        $term_taxonomy_rows = $wpdb->get_results("SELECT * FROM wphr_term_relationships WHERE term_taxonomy_id BETWEEN $min_term_id AND $max_term_id ORDER BY object_id");
        return $term_taxonomy_rows;
}

function get_wp_terms(){
    $exclude_terms = array(1, 2, 5, 6, 7, 8, 22);
    $args = array(
        'order' => 'ASC',
        'orderby' => 'term_id', 
        'exclude' => $exclude_terms,
        'hide_empty' => false,
    );
    $term_query = new WP_Term_Query( $args );
    if ( ! empty( $term_query->terms ) ){
        return $term_query;
    }else {
        printf('No term found.');
    }
}

function get_wp_posts_metas(){
    global $wpdb;
    $posts_meta = $wpdb->get_results("SELECT * FROM `wphr_postmeta` WHERE post_id IN (SELECT wphr_posts.ID FROM `wphr_posts` WHERE wphr_posts.post_type = 'product') ORDER BY meta_id ASC");
    return $posts_meta;
}

// Export all database data to xml file
function sqlToXml(){
    global $wpdb;
    $product = 'product';
    $productQuery = "SELECT * FROM wphr_posts WHERE post_type = 'product' ORDER BY ID ASC";
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xmlRoot = $xml->createElement('wphr_posts');
    $xmlRoot = $xml->appendChild($xmlRoot);
    $xmlProductRoot = $xml->createElement('products');
    $xmlProductRoot = $xmlRoot->appendChild($xmlProductRoot);
    $productRows = $wpdb->get_results($productQuery);
    foreach($productRows as $productRow){
            $currentPerson = $xml->createElement("product");
            $currentPerson = $xmlProductRoot->appendChild($currentPerson);
            $currentPerson->appendChild($xml->createElement('ID',$productRow->ID));
            $currentPerson->appendChild($xml->createElement('post_author', $productRow->post_author));
            $currentPerson->appendChild($xml->createElement('post_date',$productRow->post_date));
            $currentPerson->appendChild($xml->createElement('post_date_gmt',$productRow->post_date_gmt));
            $currentPerson->appendChild($xml->createElement('post_content', $productRow->post_content));
            $currentPerson->appendChild($xml->createElement('post_title', $productRow->post_title));
            $currentPerson->appendChild($xml->createElement('post_excerpt', $productRow->post_excerpt));
            $currentPerson->appendChild($xml->createElement('post_status', $productRow->post_status));
            $currentPerson->appendChild($xml->createElement('comment_status', $productRow->comment_status));
            $currentPerson->appendChild($xml->createElement('ping_status', $productRow->ping_status));
            $currentPerson->appendChild($xml->createElement('post_password', $productRow->post_password));
            $currentPerson->appendChild($xml->createElement('post_name', $productRow->post_name));
            $currentPerson->appendChild($xml->createElement('to_ping', $productRow->to_ping));
            $currentPerson->appendChild($xml->createElement('pinged', $productRow->pinged));
            $currentPerson->appendChild($xml->createElement('post_modified', $productRow->post_modified));
            $currentPerson->appendChild($xml->createElement('post_modified_gmt', $productRow->post_modified_gmt));
            $currentPerson->appendChild($xml->createElement('post_content_filtered', $productRow->post_content_filtered));
            $currentPerson->appendChild($xml->createElement('post_parent', $productRow->post_parent));
            $currentPerson->appendChild($xml->createElement('guid', htmlspecialchars($productRow->guid)));
            $currentPerson->appendChild($xml->createElement('menu_order', $productRow->menu_order));
            $currentPerson->appendChild($xml->createElement('post_type', $productRow->post_type));
            $currentPerson->appendChild($xml->createElement('post_mime_type', $productRow->post_mime_type));
            $currentPerson->appendChild($xml->createElement('comment_count', $productRow->comment_count));
    }

    $xmlImgRoot = $xml->createElement('imgs');
    $xmlImgRoot = $xmlRoot->appendChild($xmlImgRoot);

    $imgQuery = "SELECT * FROM `wphr_posts` WHERE post_parent IN (SELECT ID FROM wphr_posts WHERE post_type = 'product')";
    $imgRows = $wpdb->get_results($imgQuery);
    foreach($imgRows as $imgRow){
        $currentPerson = $xml->createElement("img");
        $currentPerson = $xmlImgRoot->appendChild($currentPerson);
        $currentPerson->appendChild($xml->createElement('ID',$imgRow->ID));
        $currentPerson->appendChild($xml->createElement('post_author', $imgRow->post_author));
        $currentPerson->appendChild($xml->createElement('post_date',$imgRow->post_date));
        $currentPerson->appendChild($xml->createElement('post_date_gmt',$imgRow->post_date_gmt));
        $currentPerson->appendChild($xml->createElement('post_content', $imgRow->post_content));
        $currentPerson->appendChild($xml->createElement('post_title', $imgRow->post_title));
        $currentPerson->appendChild($xml->createElement('post_excerpt', $imgRow->post_excerpt));
        $currentPerson->appendChild($xml->createElement('post_status', $imgRow->post_status));
        $currentPerson->appendChild($xml->createElement('comment_status', $imgRow->comment_status));
        $currentPerson->appendChild($xml->createElement('ping_status', $imgRow->ping_status));
        $currentPerson->appendChild($xml->createElement('post_password', $imgRow->post_password));
        $currentPerson->appendChild($xml->createElement('post_name', $imgRow->post_name));
        $currentPerson->appendChild($xml->createElement('to_ping', $imgRow->to_ping));
        $currentPerson->appendChild($xml->createElement('pinged', $imgRow->pinged));
        $currentPerson->appendChild($xml->createElement('post_modified', $imgRow->post_modified));
        $currentPerson->appendChild($xml->createElement('post_modified_gmt', $imgRow->post_modified_gmt));
        $currentPerson->appendChild($xml->createElement('post_content_filtered', $imgRow->post_content_filtered));
        $currentPerson->appendChild($xml->createElement('post_parent', $imgRow->post_parent));
        $currentPerson->appendChild($xml->createElement('guid', $imgRow->guid));
        $currentPerson->appendChild($xml->createElement('menu_order', $imgRow->menu_order));
        $currentPerson->appendChild($xml->createElement('post_type', $imgRow->post_type));
        $currentPerson->appendChild($xml->createElement('post_mime_type', $imgRow->post_mime_type));
        $currentPerson->appendChild($xml->createElement('comment_count', $imgRow->comment_count));
    }

    $xmlMetaRoot = $xml->createElement('products_meta');
    $xmlMetaRoot = $xmlRoot->appendChild($xmlMetaRoot);

    $meta_product_Query = "SELECT * FROM `wphr_wc_product_meta_lookup`";
    $meta_product_Rows = $wpdb->get_results($meta_product_Query);
    foreach($meta_product_Rows as $meta_product_Row){
        $currentPerson = $xml->createElement("product_meta");
        $currentPerson = $xmlMetaRoot->appendChild($currentPerson);
        $currentPerson->appendChild($xml->createElement('product_id',$meta_product_Row->product_id));
        $currentPerson->appendChild($xml->createElement('sku', $meta_product_Row->sku));
        $currentPerson->appendChild($xml->createElement('virtual',$meta_product_Row->virtual));
        $currentPerson->appendChild($xml->createElement('downloadable',$meta_product_Row->downloadable));
        $currentPerson->appendChild($xml->createElement('min_price', $meta_product_Row->min_price));
        $currentPerson->appendChild($xml->createElement('max_price', $meta_product_Row->max_price));
        $currentPerson->appendChild($xml->createElement('onsale', $meta_product_Row->onsale));
        $currentPerson->appendChild($xml->createElement('stock_quantity', $meta_product_Row->stock_quantity));
        $currentPerson->appendChild($xml->createElement('stock_status', $meta_product_Row->stock_status));
        $currentPerson->appendChild($xml->createElement('rating_count', $meta_product_Row->rating_count));
        $currentPerson->appendChild($xml->createElement('average_rating', $meta_product_Row->average_rating));
        $currentPerson->appendChild($xml->createElement('total_sales', $meta_product_Row->total_sales));
        $currentPerson->appendChild($xml->createElement('tax_status', $meta_product_Row->tax_status));
        $currentPerson->appendChild($xml->createElement('tax_class', $meta_product_Row->tax_class));
    }

        $xmlTermRoot = $xml->createElement('terms');
        $xmlTermRoot = $xmlRoot->appendChild($xmlTermRoot);
        $term_query = get_wp_terms();
        foreach ( $term_query ->terms as $term ) 
        {
            $currentPerson = $xml->createElement("term");
            $currentPerson = $xmlTermRoot->appendChild($currentPerson);
            $currentPerson->appendChild($xml->createElement('term_id',$term->term_id));
            $currentPerson->appendChild($xml->createElement('name',$term->name));
            $currentPerson->appendChild($xml->createElement('slug',$term->slug));
            $currentPerson->appendChild($xml->createElement('term_group',$term->term_group));
        }

        $xmlRelRoot = $xml->createElement('term_relationships');
        $xmlRelRoot = $xmlRoot->appendChild($xmlRelRoot);
        $term_relations = get_wp_term_relations();
        foreach ( $term_relations as $term_relation ) 
        {
            $currentPerson = $xml->createElement("term_relationship");
            $currentPerson = $xmlRelRoot->appendChild($currentPerson);
            $currentPerson->appendChild($xml->createElement('object_id',$term_relation->object_id));
            $currentPerson->appendChild($xml->createElement('term_taxonomy_id',$term_relation->term_taxonomy_id));
            $currentPerson->appendChild($xml->createElement('term_order',$term_relation->term_order));
        }

        $xmlTaxRoot = $xml->createElement('term_taxonomies');
        $xmlTaxRoot = $xmlRoot->appendChild($xmlTaxRoot);
        $term_query = get_wp_terms();
        foreach ( $term_query ->terms as $term ) {
            $term_taxonomy_rows = $wpdb->get_results("SELECT * FROM `wphr_term_taxonomy` WHERE term_id = $term->term_id");
            foreach($term_taxonomy_rows as $term_taxonomy_row){
                $currentPerson = $xml->createElement("term_taxonomy");
                $currentPerson = $xmlTaxRoot->appendChild($currentPerson);
                $currentPerson->appendChild($xml->createElement('term_taxonomy_id',$term_taxonomy_row->term_taxonomy_id));
                $currentPerson->appendChild($xml->createElement('term_id',$term_taxonomy_row->term_id));
                $currentPerson->appendChild($xml->createElement('taxonomy',$term_taxonomy_row->taxonomy));
                $currentPerson->appendChild($xml->createElement('description',$term_taxonomy_row->description));
                $currentPerson->appendChild($xml->createElement('parent',$term_taxonomy_row->parent));
                $currentPerson->appendChild($xml->createElement('count',$term_taxonomy_row->count));
            }
            
        }

        $xml_post_meta_root = $xml->createElement('posts_meta');
        $xml_post_meta_root = $xmlRoot->appendChild($xml_post_meta_root);
        $posts_meta = get_wp_posts_metas();    
                    foreach($posts_meta as $post_meta){
                        $currentPerson = $xml->createElement("post_meta");
                        $currentPerson = $xml_post_meta_root->appendChild($currentPerson);
                        $currentPerson->appendChild($xml->createElement('meta_id',$post_meta->meta_id));
                        $currentPerson->appendChild($xml->createElement('post_id',$post_meta->post_id));
                        $currentPerson->appendChild($xml->createElement('meta_key',$post_meta->meta_key));
                        $currentPerson->appendChild($xml->createElement('meta_value',$post_meta->meta_value));
        }

    saveXMLFile($xml);
}

function xmlToSql($xml_url){
    global $wpdb; 
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->load('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') or die("Cant load xml file");
    $elements_length = $dom->getElementsByTagName('post')->length;
    $posts_elements = $dom->getElementsByTagName('post');
    $last_post_element = $posts_elements[$elements_length-1];
    $last_post_element_id = $last_post_element->getElementsByTagName('ID')->item(0)->nodeValue;
    $last_insert_id = $wpdb->get_results("SELECT ID FROM `wphr_posts` ORDER BY ID DESC LIMIT 1"); // last ID
    $last_insert_id_num = $last_insert_id[0]->ID;
    $xpath = new DOMXPath($dom);
    $new_inserts = $xpath->query("//wphr_posts/post/ID[text() = $last_insert_id_num]/..");
    //print_r($new_inserts->item(0)->getElementsByTagName('post_modified')->item(0)->nodeValue);
    $new_insert_id = preg_replace("/[^0-9]/", '', $new_inserts->item(0)->getElementsByTagName('ID')->item(0)->getNodePath());
    if($last_post_element_id > $last_insert_id_num){
        for($i=$new_insert_id; $i<$posts_elements->length; $i++){
            $next_element = $posts_elements->item($i);
            $new_insert_array = array(
            'ID' => $next_element->getElementsByTagName('ID')->item(0)->nodeValue, 
            'post_author' => $next_element->getElementsByTagName('post_author')->item(0)->nodeValue,
            'post_date' => $next_element->getElementsByTagName('post_date')->item(0)->nodeValue,
            'post_date_gmt' => $next_element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
            'post_content' => $next_element->getElementsByTagName('post_content')->item(0)->nodeValue,
            'post_title' => $next_element->getElementsByTagName('post_title')->item(0)->nodeValue,
            'post_excerpt' => $next_element->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
            'post_status' => $next_element->getElementsByTagName('post_status')->item(0)->nodeValue,
            'comment_status' => $next_element->getElementsByTagName('comment_status')->item(0)->nodeValue,
            'ping_status' => $next_element->getElementsByTagName('ping_status')->item(0)->nodeValue,
            'post_password' => $next_element->getElementsByTagName('post_password')->item(0)->nodeValue,
            'post_name' => $next_element->getElementsByTagName('post_name')->item(0)->nodeValue,
            'to_ping' => $next_element->getElementsByTagName('to_ping')->item(0)->nodeValue,
            'pinged' => $next_element->getElementsByTagName('pinged')->item(0)->nodeValue,
            'post_modified' => $next_element->getElementsByTagName('post_modified')->item(0)->nodeValue,
            'post_modified_gmt' => $next_element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
            'post_content_filtered' => $next_element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
            'post_parent' => $next_element->getElementsByTagName('post_parent')->item(0)->nodeValue,
            'guid' => $next_element->getElementsByTagName('guid')->item(0)->nodeValue,
            'menu_order' => $next_element->getElementsByTagName('menu_order')->item(0)->nodeValue,
            'post_type' => $next_element->getElementsByTagName('post_type')->item(0)->nodeValue,
            'post_mime_type' => $next_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
            'comment_count' => $next_element->getElementsByTagName('comment_count')->item(0)->nodeValue);
            $wpdb->insert('wphr_posts', $new_insert_array);
        }
    }
}

function xmlToSqlUpdates(){
    global $wpdb;
    $dom = new DomDocument('1.0', 'UTF-8');
    $dom ->load('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') or die("Cant load xml file");
    $posts_elements = $dom->getElementsByTagName('posts');
    $xpath = new DOMXPath($dom);
    $db_inserts = $wpdb->get_results('SELECT ID, post_modified FROM wphr_posts ORDER BY ID ASC');
    foreach($db_inserts as $db_insert){
        if(count($xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")) == 0){
            $wpdb->delete('wphr_posts', array('ID'=>$db_insert->ID));
        }else{
            $xml_timestamp = $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_modified')->item(0)->nodeValue;
            $db_timestamp = $db_insert->post_modified;
            if($xml_timestamp > $db_timestamp){
                $wpdb->update('wphr_posts', array('post_modified' => $xml_timestamp), array('ID' => $db_insert->ID));
            }
        } 
    }  
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

function makeNewTable(){
    
    global $wpdb;
    $wpdb->insert('myTable', array('id' => 1, 'first_name' => 'first name','last_name' => 'last name', 'email' => 'email@email.com', 'gender' => 'male') );
}

function add_new_mime(){
    $mimes['sql'] = 'text/plain';
    $mimes['csv'] = 'text/plain';
    $mimes['jpg'] = 'image/jpeg';
    $mimes['png'] = 'image/png';
    return $mimes;
}

function check_inserts(){
    global $wpdb, $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents(ABSPATH .  '/wp-admin/wphr_posts.xml') ) {
        echo 'failed to check inserts';
    }
    $startTime = microtime(true);
    // Last product ID in DB
    $last_insert_id = $wpdb->get_results("SELECT ID FROM `wphr_posts` WHERE post_type = 'product' ORDER BY ID DESC LIMIT 1")[0]->ID; // Last insert product ID from DB
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($xmlFile);
    $root_node = $dom->getElementsByTagName('products')->item(0);
    $xml = new SimpleXMLElement($dom->saveXML());
    $last_product_element = $xml->xpath('/wphr_posts/products/product[last()]')[0]->ID; // Last product ID from XML
    
    if($last_product_element < $last_insert_id){
        $new_inserts = $wpdb->get_results("SELECT * FROM `wphr_posts` where ID between $last_product_element+1 and $last_insert_id and post_type = 'product'");
        foreach($new_inserts as $new_insert){
            $post_node = $root_node->appendChild($dom->createElement('product'));
            $post_node->appendChild($dom->createElement('ID', $new_insert->ID));
            $post_node->appendChild($dom->createElement('post_author', $new_insert->post_author));
            $post_node->appendChild($dom->createElement('post_date', $new_insert->post_date));
            $post_node->appendChild($dom->createElement('post_date_gmt', $new_insert->post_date_gmt));
            $post_node->appendChild($dom->createElement('post_content', $new_insert->post_content));
            $post_node->appendChild($dom->createElement('post_title', $new_insert->post_title));
            $post_node->appendChild($dom->createElement('post_excerpt', $new_insert->post_excerpt));
            $post_node->appendChild($dom->createElement('post_status', $new_insert->post_status));
            $post_node->appendChild($dom->createElement('comment_status', $new_insert->comment_status));
            $post_node->appendChild($dom->createElement('ping_status', $new_insert->ping_status));
            $post_node->appendChild($dom->createElement('post_password', $new_insert->post_password));
            $post_node->appendChild($dom->createElement('post_name', $new_insert->post_name));
            $post_node->appendChild($dom->createElement('to_ping', $new_insert->to_ping));
            $post_node->appendChild($dom->createElement('pinged', $new_insert->pinged));
            $post_node->appendChild($dom->createElement('post_modified', $new_insert->post_modified));
            $post_node->appendChild($dom->createElement('post_modified_gmt', $new_insert->post_modified_gmt));
            $post_node->appendChild($dom->createElement('post_content_filtered', $new_insert->post_content_filtered));
            $post_node->appendChild($dom->createElement('post_parent', $new_insert->post_parent));
            $post_node->appendChild($dom->createElement('guid', $new_insert->guid));
            $post_node->appendChild($dom->createElement('menu_order', $new_insert->menu_order));
            $post_node->appendChild($dom->createElement('post_type', $new_insert->post_type));
            $post_node->appendChild($dom->createElement('post_mime_type', $new_insert->post_mime_type));
            $post_node->appendChild($dom->createElement('comment_count', $new_insert->comment_count));

        }
        
    }
        $root_node = $dom->getElementsByTagName('imgs')->item(0);
        $last_insert_img_id = $wpdb->get_results("SELECT * FROM `wphr_posts` WHERE post_parent IN (SELECT ID FROM wphr_posts WHERE post_type = 'product') ORDER BY ID DESC LIMIT 1")[0]->ID;
        $last_img_element = $xml->xpath('/wphr_posts/imgs/img[last()]')[0]->ID; // Last img ID from XML
        
        if($last_img_element < $last_insert_img_id){
            $new_img_inserts = $wpdb->get_results("SELECT * FROM `wphr_posts` where ID between $last_img_element+1 and $last_insert_img_id and post_type = 'attachment'");
            foreach($new_img_inserts as $new_img_insert){
                $post_node = $root_node->appendChild($dom->createElement('img'));
                $post_node->appendChild($dom->createElement('ID', $new_img_insert->ID));
                $post_node->appendChild($dom->createElement('post_author', $new_img_insert->post_author));
                $post_node->appendChild($dom->createElement('post_date', $new_img_insert->post_date));
                $post_node->appendChild($dom->createElement('post_date_gmt', $new_img_insert->post_date_gmt));
                $post_node->appendChild($dom->createElement('post_content', $new_img_insert->post_content));
                $post_node->appendChild($dom->createElement('post_title', $new_img_insert->post_title));
                $post_node->appendChild($dom->createElement('post_excerpt', $new_img_insert->post_excerpt));
                $post_node->appendChild($dom->createElement('post_status', $new_img_insert->post_status));
                $post_node->appendChild($dom->createElement('comment_status', $new_img_insert->comment_status));
                $post_node->appendChild($dom->createElement('ping_status', $new_img_insert->ping_status));
                $post_node->appendChild($dom->createElement('post_password', $new_img_insert->post_password));
                $post_node->appendChild($dom->createElement('post_name', $new_img_insert->post_name));
                $post_node->appendChild($dom->createElement('to_ping', $new_img_insert->to_ping));
                $post_node->appendChild($dom->createElement('pinged', $new_img_insert->pinged));
                $post_node->appendChild($dom->createElement('post_modified', $new_img_insert->post_modified));
                $post_node->appendChild($dom->createElement('post_modified_gmt', $new_img_insert->post_modified_gmt));
                $post_node->appendChild($dom->createElement('post_content_filtered', $new_img_insert->post_content_filtered));
                $post_node->appendChild($dom->createElement('post_parent', $new_img_insert->post_parent));
                $post_node->appendChild($dom->createElement('guid', $new_img_insert->guid));
                $post_node->appendChild($dom->createElement('menu_order', $new_img_insert->menu_order));
                $post_node->appendChild($dom->createElement('post_type', $new_img_insert->post_type));
                $post_node->appendChild($dom->createElement('post_mime_type', $new_img_insert->post_mime_type));
                $post_node->appendChild($dom->createElement('comment_count', $new_img_insert->comment_count));
            }
            saveXMLFile($dom);
        }

        $root_node = $dom->getElementsByTagName('products_meta')->item(0);
        $last_product_meta_id = $wpdb->get_results("SELECT product_id FROM `wphr_wc_product_meta_lookup` ORDER BY product_id DESC LIMIT 1")[0]->product_id; // Last product_id meta in DB
        $last_element_product_meta = $xml->xpath("/wphr_posts/products_meta/product_meta[last()]"); // Last product_id meta in XML file
        $last_element_product_meta_id = (int)$last_element_product_meta[0]->product_id;
        if($last_product_meta_id > $last_element_product_meta_id){
            $new_products_meta = $wpdb->get_results("SELECT * FROM `wphr_wc_product_meta_lookup` WHERE product_id BETWEEN $last_element_product_meta_id+1 and $last_product_meta_id");
            foreach($new_products_meta as $new_product_meta){
                $post_node = $root_node->appendChild($dom->createElement('product_meta'));
                $post_node->appendChild($dom->createElement('product_id',$new_product_meta->product_id));
                $post_node->appendChild($dom->createElement('sku', $new_product_meta->sku));
                $post_node->appendChild($dom->createElement('virtual',$new_product_meta->virtual));
                $post_node->appendChild($dom->createElement('downloadable',$new_product_meta->downloadable));
                $post_node->appendChild($dom->createElement('min_price', $new_product_meta->min_price));
                $post_node->appendChild($dom->createElement('max_price', $new_product_meta->max_price));
                $post_node->appendChild($dom->createElement('onsale', $new_product_meta->onsale));
                $post_node->appendChild($dom->createElement('stock_quantity', $new_product_meta->stock_quantity));
                $post_node->appendChild($dom->createElement('stock_status', $new_product_meta->stock_status));
                $post_node->appendChild($dom->createElement('rating_count', $new_product_meta->rating_count));
                $post_node->appendChild($dom->createElement('average_rating', $new_product_meta->average_rating));
                $post_node->appendChild($dom->createElement('total_sales', $new_product_meta->total_sales));
                $post_node->appendChild($dom->createElement('tax_status', $new_product_meta->tax_status));
                $post_node->appendChild($dom->createElement('tax_class', $new_product_meta->tax_class));
        }
        saveXMLFile($dom);
    }

    $root_node = $dom->getElementsByTagName('terms')->item(0);
    $term_query = get_wp_terms();
    $max_term_id = array();
    foreach($term_query->terms as $term){
        array_push($max_term_id, (int)$term->term_id);
    }
    $last_term_id = max($max_term_id); // Last term_id in DB
    $last_term_id_element = $xml->xpath("/wphr_posts/terms/term[last()]")[0]->term_id; // Last term in XML file
    if($last_term_id > $last_term_id_element){
    foreach ( $term_query ->terms as $term ) {
        if($term->term_id > $last_term_id_element){
            $post_node = $root_node->appendChild($dom->createElement('term'));
            $post_node->appendChild($dom->createElement('term_id',$term->term_id));
            $post_node->appendChild($dom->createElement('name',$term->name));
            $post_node->appendChild($dom->createElement('slug',$term->slug));
            $post_node->appendChild($dom->createElement('term_group',$term->term_group));
            }
        }
        saveXMLFile($dom);
    }

    $root_node = $dom->getElementsByTagName('term_relationships')->item(0);
    $last_object_id = $wpdb->get_results("SELECT object_id FROM `wphr_term_relationships` WHERE object_id IN (SELECT ID FROM `wphr_posts` WHERE post_type='product') ORDER BY object_id DESC LIMIT 1")[0]->object_id; // Last object_id in DB
    $last_object_id_element = $xml->xpath("/wphr_posts/term_relationships/term_relationship[last()]")[0]->object_id; // Last object_id in XML file
    if($last_object_id > $last_object_id_element){
    $term_relations = get_wp_term_relations();
    $new_products_meta = $wpdb->get_results("SELECT * FROM `wphr_term_relationships` WHERE object_id IN (SELECT ID FROM `wphr_posts` WHERE post_type='product') AND object_id BETWEEN $last_object_id_element+1 and $last_object_id");
    foreach ( $new_products_meta as $new_product_meta ) {
        $post_node = $root_node->appendChild($$dom->createElement('term_relationship'));
        $post_node->appendChild($dom->createElement('object_id',$new_product_meta->object_id));
        $post_node->appendChild($dom->createElement('term_taxonomy_id',$new_product_meta->term_taxonomy_id));
        $post_node->appendChild($dom->createElement('term_id',$new_product_meta->term_id));
        }
        saveXMLFile($dom);
    }

    $root_node = $dom->getElementsByTagName('term_taxonomies')->item(0);
    $term_query = get_wp_terms();
    $max_term_taxonomy_id_array = array();
    foreach($term_query->terms as $term){
        array_push($max_term_taxonomy_id_array, $term->term_id);
    }
    $last_term_taxonomy_id_element = $xml->xpath("/wphr_posts/term_taxonomies/term_taxonomy[last()]")[0]->term_taxonomy_id; // Last term_taxonomy_id in XML file
    $max_term_taxonomy_id = max($max_term_taxonomy_id_array); //Last term_taxonomy_id in DB
    if($max_term_taxonomy_id > $last_term_taxonomy_id_element){
        foreach($term_query->terms as $term){
            if($term->term_taxonomy_id > $last_term_taxonomy_id_element){
                $post_node = $root_node->appendChild($dom->createElement('term_taxonomy'));
                $post_node->appendChild($dom->createElement('term_taxonomy_id',$term->term_taxonomy_id));
                $post_node->appendChild($dom->createElement('term_id',$term->term_id));
                $post_node->appendChild($dom->createElement('taxonomy',$term->taxonomy));
                $post_node->appendChild($dom->createElement('description',$term->description));
                $post_node->appendChild($dom->createElement('parent',$term->parent));
                $post_node->appendChild($dom->createElement('count',$term->count));
            }
        }
        saveXMLFile($dom);
    }

    $root_node = $dom->getElementsByTagName('posts_meta')->item(0);
    $last_post_meta_id = $wpdb->get_results("SELECT * FROM `wphr_postmeta` ORDER BY meta_id DESC LIMIT 1")[0]->meta_id; //Last post_meta_id in DB
    $last_post_meta_id_element = $xml->xpath("/wphr_posts/posts_meta/post_meta[last()]")[0]->meta_id; // Last post_meta_id in XML file 
    if($last_post_meta_id > $last_post_meta_id_element){
        $posts_meta = $wpdb->get_results("SELECT * FROM `wphr_postmeta` WHERE meta_id BETWEEN $last_post_meta_id_element+1 AND $last_post_meta_id");
        foreach($posts_meta as $post_meta){
            if(get_post_type( $post_meta->post_id ) == 'product'){
                $post_node = $root_node->appendChild($dom->createElement('post_meta'));
                $post_node->appendChild($dom->createElement('meta_id',$post_meta->meta_id));
                $post_node->appendChild($dom->createElement('post_id',$post_meta->post_id));
                $post_node->appendChild($dom->createElement('meta_key',$post_meta->meta_key));
                $post_node->appendChild($dom->createElement('meta_value',$post_meta->meta_value));
            }
        }
        saveXMLFile($dom);
    }

    $endTime = microtime(true);
    echo "Check new inserts time: " . ($endTime - $startTime) . "<br>";
}

function saveXMLFile($dom){
    global $wp_filesystem;
    // Initialize the WP filesystem, no more using 'file-put-contents' function
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    $string_dom = $dom->saveXML();
    if(!$wp_filesystem->put_contents(ABSPATH .  '/wp-admin/wphr_posts.xml', $string_dom, 0644) ) {
        echo 'failed to check inserts';
    }
}

function checkUpDates(){
    global $wpdb;
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents(ABSPATH .  '/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read xml file in check inserts';
    }
    
    $startTime = microtime(true);
    $dom = new DOMDocument();
    $dom->loadXML($xmlFile);
    $posts_elements = $dom->getElementsByTagName('products')->item(0)->getElementsByTagName('product');
    $meta_elements = $dom->getElementsByTagName('products_meta')->item(0)->getElementsByTagName('product_meta');
    $elements_length = $posts_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $post_element = $posts_elements[$i];
        $post_meta_element = $meta_elements[$i];
        $post_element_id = $post_element->getElementsByTagName('ID')->item(0)->nodeValue;
        $row = $wpdb->get_results("SELECT * FROM `wphr_posts` WHERE ID = $post_element_id");
        $metaRow = $wpdb->get_results("SELECT * FROM `wphr_wc_product_meta_lookup` WHERE product_id = $post_element_id");
        if(empty($row)){
            $old_element = $dom->getElementsByTagName('products')->item(0)->removeChild($post_element);
            
        }else{
            $db_timestamp = new DateTime($row[0]->post_modified);
            $xml_timestamp = new DateTime($post_element->getElementsByTagName('post_modified')->item(0)->nodeValue);
            if($db_timestamp > $xml_timestamp){
                $post_element->getElementsByTagName('post_author')->item(0)->nodeValue = $row[0]->post_author;
                $post_element->getElementsByTagName('post_date')->item(0)->nodeValue = $row[0]->post_date;
                $post_element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue = $row[0]->post_date_gmt;
                $post_element->getElementsByTagName('post_content')->item(0)->nodeValue = $row[0]->post_content;
                $post_element->getElementsByTagName('post_title')->item(0)->nodeValue = $row[0]->post_title;
                $post_element->getElementsByTagName('post_excerpt')->item(0)->nodeValue = $row[0]->post_excerpt;
                $post_element->getElementsByTagName('post_status')->item(0)->nodeValue = $row[0]->post_status;
                $post_element->getElementsByTagName('comment_status')->item(0)->nodeValue = $row[0]->comment_status;
                $post_element->getElementsByTagName('ping_status')->item(0)->nodeValue = $row[0]->ping_status;
                $post_element->getElementsByTagName('post_password')->item(0)->nodeValue = $row[0]->post_password;
                $post_element->getElementsByTagName('post_name')->item(0)->nodeValue = $row[0]->post_name;
                $post_element->getElementsByTagName('to_ping')->item(0)->nodeValue = $row[0]->to_ping;
                $post_element->getElementsByTagName('pinged')->item(0)->nodeValue = $row[0]->pinged;
                $post_element->getElementsByTagName('post_modified')->item(0)->nodeValue = $row[0]->post_modified;
                $post_element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue = $row[0]->post_modified_gmt;
                $post_element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue = $row[0]->post_content_filtered;
                $post_element->getElementsByTagName('post_parent')->item(0)->nodeValue = $row[0]->post_parent;
                $post_element->getElementsByTagName('guid')->item(0)->nodeValue = $row[0]->guid;
                $post_element->getElementsByTagName('menu_order')->item(0)->nodeValue = $row[0]->menu_order;
                $post_element->getElementsByTagName('post_type')->item(0)->nodeValue = $row[0]->post_type;
                $post_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue = $row[0]->post_mime_type;
                $post_element->getElementsByTagName('comment_count')->item(0)->nodeValue = $row[0]->comment_count;

                $post_meta_element->getElementsByTagName('product_id')->item(0)->nodeValue = $metaRow[0]->product_id;
                $post_meta_element->getElementsByTagName('sku')->item(0)->nodeValue = $metaRow[0]->sku;
                $post_meta_element->getElementsByTagName('virtual')->item(0)->nodeValue = $metaRow[0]->virtual;
                $post_meta_element->getElementsByTagName('downloadable')->item(0)->nodeValue = $metaRow[0]->downloadable;
                $post_meta_element->getElementsByTagName('min_price')->item(0)->nodeValue = $metaRow[0]->min_price;
                $post_meta_element->getElementsByTagName('max_price')->item(0)->nodeValue = $metaRow[0]->max_price;
                $post_meta_element->getElementsByTagName('onsale')->item(0)->nodeValue = $metaRow[0]->onsale;
                $post_meta_element->getElementsByTagName('stock_quantity')->item(0)->nodeValue = $metaRow[0]->stock_quantity;
                $post_meta_element->getElementsByTagName('stock_status')->item(0)->nodeValue = $metaRow[0]->stock_status;
                $post_meta_element->getElementsByTagName('rating_count')->item(0)->nodeValue = $metaRow[0]->rating_count;
                $post_meta_element->getElementsByTagName('average_rating')->item(0)->nodeValue = $metaRow[0]->average_rating;
                $post_meta_element->getElementsByTagName('total_sales')->item(0)->nodeValue = $metaRow[0]->total_sales;
                $post_meta_element->getElementsByTagName('tax_status')->item(0)->nodeValue = $metaRow[0]->tax_status;
                $post_meta_element->getElementsByTagName('tax_class')->item(0)->nodeValue = $metaRow[0]->tax_class;
            }
        }
    }

    $img_elements = $dom->getElementsByTagName('imgs')->item(0)->getElementsByTagName('img');
    $elements_length = $img_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $img_element = $img_elements[$i];
        $img_element_id = $img_element->getElementsByTagName('ID')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_posts` WHERE ID = $img_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('imgs')->item(0)->removeChild($img_element);
            
        }else{
            $db_timestamp = new DateTime($row[0]->post_modified);
            $xml_timestamp = new DateTime($img_element->getElementsByTagName('post_modified')->item(0)->nodeValue);
            if($db_timestamp > $xml_timestamp){
                $img_element->getElementsByTagName('post_author')->item(0)->nodeValue = $row[0]->post_author;
                $img_element->getElementsByTagName('post_date')->item(0)->nodeValue = $row[0]->post_date;
                $img_element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue = $row[0]->post_date_gmt;
                $img_element->getElementsByTagName('post_content')->item(0)->nodeValue = $row[0]->post_content;
                $img_element->getElementsByTagName('post_title')->item(0)->nodeValue = $row[0]->post_title;
                $img_element->getElementsByTagName('post_excerpt')->item(0)->nodeValue = $row[0]->post_excerpt;
                $img_element->getElementsByTagName('post_status')->item(0)->nodeValue = $row[0]->post_status;
                $img_element->getElementsByTagName('comment_status')->item(0)->nodeValue = $row[0]->comment_status;
                $img_element->getElementsByTagName('ping_status')->item(0)->nodeValue = $row[0]->ping_status;
                $img_element->getElementsByTagName('post_password')->item(0)->nodeValue = $row[0]->post_password;
                $img_element->getElementsByTagName('post_name')->item(0)->nodeValue = $row[0]->post_name;
                $img_element->getElementsByTagName('to_ping')->item(0)->nodeValue = $row[0]->to_ping;
                $img_element->getElementsByTagName('pinged')->item(0)->nodeValue = $row[0]->pinged;
                $img_element->getElementsByTagName('post_modified')->item(0)->nodeValue = $row[0]->post_modified;
                $img_element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue = $row[0]->post_modified_gmt;
                $img_element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue = $row[0]->post_content_filtered;
                $img_element->getElementsByTagName('post_parent')->item(0)->nodeValue = $row[0]->post_parent;
                $img_element->getElementsByTagName('guid')->item(0)->nodeValue = $row[0]->guid;
                $img_element->getElementsByTagName('menu_order')->item(0)->nodeValue = $row[0]->menu_order;
                $img_element->getElementsByTagName('post_type')->item(0)->nodeValue = $row[0]->post_type;
                $img_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue = $row[0]->post_mime_type;
                $img_element->getElementsByTagName('comment_count')->item(0)->nodeValue = $row[0]->comment_count;
            }
        }
    }

    $meta_elements = $dom->getElementsByTagName('products_meta')->item(0)->getElementsByTagName('product_meta');
    $elements_length = $meta_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $meta_element = $meta_elements[$i];
        $meta_element_id = $meta_element->getElementsByTagName('product_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_wc_product_meta_lookup` WHERE product_id = $meta_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('products_meta')->item(0)->removeChild($meta_element);
        }
    }
    
    $term_elements = $dom->getElementsByTagName('terms')->item(0)->getElementsByTagName('term');
    $elements_length = $term_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $term_element = $term_elements[$i];
        $term_element_id = $term_element->getElementsByTagName('term_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_terms` WHERE term_id = $term_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('terms')->item(0)->removeChild($term_element);
        }else{
            $term_element->getElementsByTagName('term_id')->item(0)->nodeValue = $row[0]->term_id;
            $term_element->getElementsByTagName('name')->item(0)->nodeValue = $row[0]->name;
            $term_element->getElementsByTagName('slug')->item(0)->nodeValue = $row[0]->slug;
            $term_element->getElementsByTagName('term_group')->item(0)->nodeValue = $row[0]->term_group;
        }
    }

    $term_rel_elements = $dom->getElementsByTagName('term_relationships')->item(0)->getElementsByTagName('term_relationship');
    $elements_length = $term_rel_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $term_rel_element = $term_rel_elements[$i];
        $term_rel_element_id = $term_rel_element->getElementsByTagName('object_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_term_relationships` WHERE object_id = $term_rel_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('term_relationships')->item(0)->removeChild($term_rel_element);
        }else{
            $term_rel_element->getElementsByTagName('object_id')->item(0)->nodeValue = $row[0]->object_id;
            $term_rel_element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue = $row[0]->term_taxonomy_id;
            $term_rel_element->getElementsByTagName('term_order')->item(0)->nodeValue = $row[0]->term_order;
        }
    }

    $term_tax_elements = $dom->getElementsByTagName('term_taxonomies')->item(0)->getElementsByTagName('term_taxonomy');
    $elements_length = $term_tax_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $term_tax_element = $term_tax_elements[$i];
        $term_tax_element_id = $term_tax_element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_term_taxonomy` WHERE term_taxonomy_id = $term_tax_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('term_taxonomies')->item(0)->removeChild($term_tax_element);
        }else{
            $term_tax_element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue = $row[0]->term_taxonomy_id;
            $term_tax_element->getElementsByTagName('term_id')->item(0)->nodeValue = $row[0]->term_id;
            $term_tax_element->getElementsByTagName('taxonomy')->item(0)->nodeValue = $row[0]->taxonomy;
            $term_tax_element->getElementsByTagName('description')->item(0)->nodeValue = $row[0]->description;
            $term_tax_element->getElementsByTagName('parent')->item(0)->nodeValue = $row[0]->parent;
            $term_tax_element->getElementsByTagName('count')->item(0)->nodeValue = $row[0]->count;
        }
    }
    
    $posts_meta_elements = $dom->getElementsByTagName('posts_meta')->item(0)->getElementsByTagName('post_meta');
    $elements_length = $posts_meta_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $post_meta_element = $posts_meta_elements[$i];
        $post_meta_element_id = $post_meta_element->getElementsByTagName('meta_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_postmeta` WHERE meta_id = $post_meta_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('meta_id')->item(0)->removeChild($post_meta_element);
        }else{
                $post_meta_element->getElementsByTagName('meta_id')->item(0)->nodeValue = $row[0]->meta_id;
                $post_meta_element->getElementsByTagName('post_id')->item(0)->nodeValue = $row[0]->post_id;
                $post_meta_element->getElementsByTagName('meta_key')->item(0)->nodeValue = $row[0]->meta_key;
                $post_meta_element->getElementsByTagName('meta_value')->item(0)->nodeValue = $row[0]->meta_value;
        }
    }

    saveXMLFile($dom);
    $endTime = microtime(true);
    echo "Checkup updates time: " . ($endTime - $startTime);
}

function transferImgs(){
    global $wpdp;
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents(ABSPATH .  '/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read img files';
    }


}

function update_XML_from_sql(){
    check_inserts();
    checkUpDates();
}

function customTime($schedules){
    $schedules['everysecond'] = array(
        'interval'  => 60, 
        'display'   => 'Every 1 Minute'
    );
    return $schedules;
}

function schedule_job(){
    if(!wp_next_scheduled( 'cron_job_db')){
        wp_schedule_event( time(), 'everysecond', 'cron_job_db');
    }
}

 $pluginFile = plugin_basename(__FILE__);
 //add_filter( 'mime_types','add_new_mime');
 add_filter('plugin_action_links_' . $pluginFile, 'settings_link');

 //add_filter( 'cron_schedules', 'customTime');
 //add_action( 'wp_loaded', 'schedule_job');
 //add_action( 'cron_job_db', 'update_XML_from_sql');
 //register_activation_hook( $pluginFile, 'sqlToXml');

 add_action('admin_menu', 'add_admin_page');
 add_shortcode( 'database', 'updateItems' );
 add_shortcode( 'submitform', 'submitForm' );
 
?>