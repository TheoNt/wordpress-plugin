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
        sqlToXml();
    }

    if(isset($_POST['test_btn'])){
        //get_terms_taxonomies();
        //print_r(get_wp_posts_metas();
        //get_wp_term_relations();
        test_function();
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

    ?>
        <form method="post" action="">
        <input type="submit" name="test_btn" value="Test"><br><br>
        </form> 
    <?php

}

function test_function(){
    global $wpdb;
    $product_IDs = $wpdb->get_results("SELECT ID FROM `wphr_posts` WHERE ID IN (SELECT post_parent FROM wphr_posts WHERE post_type='product_variation') ", ARRAY_A);
    $product_options = $wpdb->get_results("SELECT option_name, option_value FROM wphr_options WHERE option_name REGEXP " ."'".  implode('|',array_column($product_IDs, 'ID') ) . "'");
    $price_options = get_option( '_transient_wc_var_prices_294', true );
    if($product_options){
        foreach($product_options as $product_option){
            echo $product_option->option_name;
            echo "<br>";
            echo $product_option->option_value;
            echo "<br>";
        }
    }else{
        echo 'not price';
    }
}

function get_term_metas(){
    global $wpdb;
    $term_query = get_wp_terms();
    $term_ids = array();
    foreach($term_query ->terms as $term ){
        array_push($term_ids, $term->term_id);
    }
    
        $term_metas = $wpdb->get_results("SELECT * FROM `wphr_termmeta` WHERE wphr_termmeta.term_id IN(". implode(",", $term_ids) .")");
    return $term_metas;
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
    $posts_meta = $wpdb->get_results("SELECT * FROM wphr_postmeta WHERE wphr_postmeta.post_id IN (SELECT wp.ID
    FROM wphr_posts wp
        INNER JOIN wphr_postmeta wpm
            ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
        INNER JOIN wphr_postmeta wpm2
            ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) OR wphr_postmeta.post_id IN (SELECT wpm2.post_id
    FROM wphr_posts wp
        INNER JOIN wphr_postmeta wpm
            ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
        INNER JOIN wphr_postmeta wpm2
            ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) OR wphr_postmeta.post_id IN (SELECT wpm.post_id 
            FROM  wphr_posts wp INNER JOIN wphr_postmeta wpm ON (wp.ID = wpm.post_id AND wp.post_type = 'product_variation')) ORDER BY wphr_postmeta.meta_id ASC");
    return $posts_meta;
}

// Export all database data to xml file
function sqlToXml(){
    global $wpdb;
    $productQuery = "SELECT * FROM wphr_posts WHERE post_type = 'product' OR post_type = 'product_variation' ORDER BY ID ASC";
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

    $imageQuery = "SELECT * FROM wphr_posts WHERE wphr_posts.ID IN (SELECT wpm2.post_id
    FROM wphr_posts wp
        INNER JOIN wphr_postmeta wpm
            ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
        INNER JOIN wphr_postmeta wpm2
            ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) ORDER BY wphr_posts.ID";
    $imageRows = $wpdb->get_results($imageQuery);
    $xmlImgRoot = $xml->createElement('imgs');
    $xmlImgRoot = $xmlRoot->appendChild($xmlImgRoot);
    foreach($imageRows as $imageRow){
            $currentPerson = $xml->createElement("img");
            $currentPerson = $xmlImgRoot->appendChild($currentPerson);
            $currentPerson->appendChild($xml->createElement('ID',$imageRow->ID));
            $currentPerson->appendChild($xml->createElement('post_author', $imageRow->post_author));
            $currentPerson->appendChild($xml->createElement('post_date',$imageRow->post_date));
            $currentPerson->appendChild($xml->createElement('post_date_gmt',$imageRow->post_date_gmt));
            $currentPerson->appendChild($xml->createElement('post_content', $imageRow->post_content));
            $currentPerson->appendChild($xml->createElement('post_title', $imageRow->post_title));
            $currentPerson->appendChild($xml->createElement('post_excerpt', $imageRow->post_excerpt));
            $currentPerson->appendChild($xml->createElement('post_status', $imageRow->post_status));
            $currentPerson->appendChild($xml->createElement('comment_status', $imageRow->comment_status));
            $currentPerson->appendChild($xml->createElement('ping_status', $imageRow->ping_status));
            $currentPerson->appendChild($xml->createElement('post_password', $imageRow->post_password));
            $currentPerson->appendChild($xml->createElement('post_name', $imageRow->post_name));
            $currentPerson->appendChild($xml->createElement('to_ping', $imageRow->to_ping));
            $currentPerson->appendChild($xml->createElement('pinged', $imageRow->pinged));
            $currentPerson->appendChild($xml->createElement('post_modified', $imageRow->post_modified));
            $currentPerson->appendChild($xml->createElement('post_modified_gmt', $imageRow->post_modified_gmt));
            $currentPerson->appendChild($xml->createElement('post_content_filtered', $imageRow->post_content_filtered));
            $currentPerson->appendChild($xml->createElement('post_parent', $imageRow->post_parent));
            $currentPerson->appendChild($xml->createElement('guid', $imageRow->guid));
            $currentPerson->appendChild($xml->createElement('menu_order', $imageRow->menu_order));
            $currentPerson->appendChild($xml->createElement('post_type', $imageRow->post_type));
            $currentPerson->appendChild($xml->createElement('post_mime_type', $imageRow->post_mime_type));
            $currentPerson->appendChild($xml->createElement('comment_count', $imageRow->comment_count));
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
        
        $xml_term_meta_root = $xml->createElement('terms_meta');
        $xml_term_meta_root = $xmlRoot->appendChild($xml_term_meta_root);
        $terms_meta = get_term_metas();    
                    foreach($terms_meta as $term_meta){
                        $currentPerson = $xml->createElement("term_meta");
                        $currentPerson = $xml_term_meta_root->appendChild($currentPerson);
                        $currentPerson->appendChild($xml->createElement('meta_id',$term_meta->meta_id));
                        $currentPerson->appendChild($xml->createElement('term_id',$term_meta->term_id));
                        $currentPerson->appendChild($xml->createElement('meta_key',$term_meta->meta_key));
                        $currentPerson->appendChild($xml->createElement('meta_value',$term_meta->meta_value));
        }
        
        $xml_attribute_taxonomies_root = $xml->createElement('attribute_taxonomies');
        $xml_attribute_taxonomies_root = $xmlRoot->appendChild($xml_attribute_taxonomies_root);
        $attribute_taxonomies = $wpdb->get_results("SELECT * FROM `wphr_woocommerce_attribute_taxonomies`");    
                    foreach($attribute_taxonomies as $attribute_taxonomy){
                        $currentPerson = $xml->createElement("attribute_taxonomy");
                        $currentPerson = $xml_attribute_taxonomies_root->appendChild($currentPerson);
                        $currentPerson->appendChild($xml->createElement('attribute_id',$attribute_taxonomy->attribute_id));
                        $currentPerson->appendChild($xml->createElement('attribute_name',$attribute_taxonomy->attribute_name));
                        $currentPerson->appendChild($xml->createElement('attribute_label',$attribute_taxonomy->attribute_label));
                        $currentPerson->appendChild($xml->createElement('attribute_type',$attribute_taxonomy->attribute_type));
                        $currentPerson->appendChild($xml->createElement('attribute_orderby',$attribute_taxonomy->attribute_orderby));
                        $currentPerson->appendChild($xml->createElement('attribute_public',$attribute_taxonomy->attribute_public));
        }
        // kemfjfjhn
        $xml_options_root = $xml->createElement('options');
        $xml_options_root = $xmlRoot->appendChild($xml_options_root);
        $product_IDs = $wpdb->get_results("SELECT ID FROM `wphr_posts` WHERE ID IN (SELECT post_parent FROM wphr_posts WHERE post_type='product_variation') ", ARRAY_A);
        $product_options = $wpdb->get_results("SELECT option_name, option_value FROM wphr_options WHERE option_name REGEXP " ."'".  implode('|',array_column($product_IDs, 'ID') ) . "'");
        foreach($product_options as $product_option){        
            $currentPerson = $xml->createElement("option");
            $currentPerson = $xml_options_root->appendChild($currentPerson);
            $currentPerson->appendChild($xml->createElement('option_name',$product_option->option_name));
            $currentPerson->appendChild($xml->createElement('option_value',$product_option->option_value));
            
    }
        // Options attributes have same values with attribute_taxonomies so there is no need to export to xml file

    saveXMLFile($xml);
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
    $last_insert_id = $wpdb->get_results("SELECT ID FROM `wphr_posts` WHERE post_type = 'product' OR post_type = 'product_variation' ORDER BY ID DESC LIMIT 1")[0]->ID; // Last insert product ID from DB
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($xmlFile);
    $root_node = $dom->getElementsByTagName('products')->item(0);
    $xml = new SimpleXMLElement($dom->saveXML());
    $last_product_element = $xml->xpath('/wphr_posts/products/product[last()]')[0]->ID; // Last product ID from XML
    
    if($last_product_element < $last_insert_id){
        $new_inserts = $wpdb->get_results("SELECT * FROM `wphr_posts` WHERE ID > $last_product_element AND post_type = 'product' OR post_type = 'product_variation' ORDER BY ID ASC");
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
        $last_insert_img_id = $wpdb->get_results("SELECT wphr_posts.ID FROM wphr_posts WHERE wphr_posts.ID IN (SELECT wpm2.post_id
        FROM wphr_posts wp
            INNER JOIN wphr_postmeta wpm
                ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
            INNER JOIN wphr_postmeta wpm2
                ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) ORDER BY wphr_posts.ID DESC LIMIT 1")[0]->ID; //Last img ID from DB
        $last_img_element = $xml->xpath('/wphr_posts/imgs/img[last()]')[0]->ID; // Last img ID from XML
        
        if($last_img_element < $last_insert_img_id){
            $new_img_inserts = $wpdb->get_results("SELECT * FROM wphr_posts WHERE wphr_posts.ID IN (SELECT wpm2.post_id
            FROM wphr_posts wp
                INNER JOIN wphr_postmeta wpm
                    ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
                INNER JOIN wphr_postmeta wpm2
                    ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) AND wphr_posts.ID > $last_img_element ORDER BY wphr_posts.ID");
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
            $new_products_meta = $wpdb->get_results("SELECT * FROM `wphr_wc_product_meta_lookup` WHERE product_id BETWEEN $last_element_product_meta_id+1 AND $last_product_meta_id");
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
    $term_relationships = $wpdb->get_results("SELECT * FROM `wphr_term_relationships` WHERE object_id IN (SELECT ID FROM `wphr_posts` WHERE post_type='product') ORDER BY object_id ASC");
    foreach($term_relationships as $term_relationship){
        $is_object_id_exist = $xml->xpath("/wphr_posts/term_relationships/term_relationship[object_id = $term_relationship->object_id and term_taxonomy_id = $term_relationship->term_taxonomy_id]");
        if(count($is_object_id_exist) == 0)
        {
            $post_node = $root_node->appendChild($dom->createElement('term_relationship'));
            $post_node->appendChild($dom->createElement('object_id',$term_relationship->object_id));
            $post_node->appendChild($dom->createElement('term_taxonomy_id',$term_relationship->term_taxonomy_id));
            $post_node->appendChild($dom->createElement('term_order',$term_relationship->term_order));
        }
    }
        saveXMLFile($dom);

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
    $last_post_meta_id = $wpdb->get_results("SELECT wphr_postmeta.meta_id FROM wphr_postmeta WHERE wphr_postmeta.post_id IN (SELECT wp.ID
    FROM wphr_posts wp
        INNER JOIN wphr_postmeta wpm
            ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
        INNER JOIN wphr_postmeta wpm2
            ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) OR wphr_postmeta.post_id IN (SELECT wpm2.post_id
    FROM wphr_posts wp
        INNER JOIN wphr_postmeta wpm
            ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
        INNER JOIN wphr_postmeta wpm2
            ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) ORDER BY wphr_postmeta.meta_id DESC LIMIT 1")[0]->meta_id; //Last post_meta_id in DB
    $last_post_meta_id_element = $xml->xpath("/wphr_posts/posts_meta/post_meta[last()]")[0]->meta_id; // Last post_meta_id in XML file 
    if($last_post_meta_id > $last_post_meta_id_element){

        $posts_meta = $wpdb->get_results("SELECT * FROM wphr_postmeta WHERE wphr_postmeta.post_id IN (SELECT wp.ID
        FROM wphr_posts wp
            INNER JOIN wphr_postmeta wpm
                ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
            INNER JOIN wphr_postmeta wpm2
                ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) OR wphr_postmeta.post_id IN (SELECT wpm2.post_id
        FROM wphr_posts wp
            INNER JOIN wphr_postmeta wpm
                ON (wp.ID = wpm.post_id AND wpm.meta_key = '_thumbnail_id')
            INNER JOIN wphr_postmeta wpm2
                ON (wpm.meta_value = wpm2.post_id AND wpm2.meta_key = '_wp_attached_file')) OR wphr_postmeta.post_id IN (SELECT wpm.post_id 
                FROM  wphr_posts wp INNER JOIN wphr_postmeta wpm ON (wp.ID = wpm.post_id AND wp.post_type = 'product_variation')) 
                GROUP BY wphr_postmeta.meta_id HAVING wphr_postmeta.meta_id > $last_post_meta_id_element ORDER BY wphr_postmeta.meta_id ASC");
        foreach($posts_meta as $post_meta){
                $post_node = $root_node->appendChild($dom->createElement('post_meta'));
                $post_node->appendChild($dom->createElement('meta_id',$post_meta->meta_id));
                $post_node->appendChild($dom->createElement('post_id',$post_meta->post_id));
                $post_node->appendChild($dom->createElement('meta_key',$post_meta->meta_key));
                $post_node->appendChild($dom->createElement('meta_value',$post_meta->meta_value));
        }
        saveXMLFile($dom);
    }

    $root_node = $dom->getElementsByTagName('terms_meta')->item(0);
    $last_term_meta_id = $wpdb->get_results("SELECT meta_id FROM `wphr_termmeta` ORDER BY meta_id DESC LIMIT 1")[0]->meta_id; // Last last meta id in db
    $last_term_meta_id_element = $xml->xpath("/wphr_posts/terms_meta/term_meta[last()]")[0]->meta_id; // Last term in XML file
    if($last_term_meta_id > $last_term_meta_id_element){
        $terms_meta = get_term_metas();
        foreach ( $terms_meta as $term_meta ) {
            if($term_meta->meta_id > $last_term_meta_id_element){
                $post_node = $root_node->appendChild($dom->createElement('term_meta'));
                $post_node->appendChild($dom->createElement('meta_id',$term_meta->meta_id));
                $post_node->appendChild($dom->createElement('term_id',$term_meta->term_id));
                $post_node->appendChild($dom->createElement('meta_key',$term_meta->meta_key));
                $post_node->appendChild($dom->createElement('meta_value',$term_meta->meta_value));
            }
        }
            saveXMLFile($dom);
    }
    
    $root_node = $dom->getElementsByTagName('attribute_taxonomies')->item(0);
    $last_attribute_taxonomies_id = $wpdb->get_results("SELECT attribute_id FROM `wphr_woocommerce_attribute_taxonomies` ORDER BY attribute_id DESC LIMIT 1")[0]->attribute_id; // Last last meta id in db
    $last_attribute_taxonomies_id_element = $xml->xpath("/wphr_posts/attribute_taxonomies/attribute_taxonomy[last()]")[0]->attribute_id; // Last term in XML file
    if($last_attribute_taxonomies_id > $last_attribute_taxonomies_id_element){
        $attribute_taxonomies = $wpdb->get_results("SELECT * FROM `wphr_woocommerce_attribute_taxonomies` WHERE attribute_id > $last_attribute_taxonomies_id_element");
        foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
                $post_node = $root_node->appendChild($dom->createElement('attribute_taxonomy'));
                $post_node->appendChild($dom->createElement('attribute_id',$attribute_taxonomy->attribute_id));
                $post_node->appendChild($dom->createElement('attribute_name',$attribute_taxonomy->attribute_name));
                $post_node->appendChild($dom->createElement('attribute_label',$attribute_taxonomy->attribute_label));
                $post_node->appendChild($dom->createElement('attribute_type',$attribute_taxonomy->attribute_type));
                $post_node->appendChild($dom->createElement('attribute_orderby',$attribute_taxonomy->attribute_orderby));
                $post_node->appendChild($dom->createElement('attribute_public',$attribute_taxonomy->attribute_public));
            
        }
            saveXMLFile($dom);
    }
    // options doesn't need to check for new inserts

    $endTime = microtime(true);
    echo "Check new inserts time: " . ($endTime - $startTime) . "<br>";
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
        $term_tax_rel_element_id = $term_rel_element->getElementsByTagName('term_taxonomy_id')->item(0)->nodeValue;
        $row = $wpdb->get_results("SELECT * FROM `wphr_term_relationships` WHERE object_id = $term_rel_element_id AND term_taxonomy_id = $term_tax_rel_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('term_relationships')->item(0)->removeChild($term_rel_element);
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
    
    $terms_meta_elements = $dom->getElementsByTagName('terms_meta')->item(0)->getElementsByTagName('term_meta');
    $elements_length = $terms_meta_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $term_meta_element = $terms_meta_elements[$i];
        $term_meta_element_id = $term_meta_element->getElementsByTagName('meta_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_termmeta` WHERE meta_id = $term_meta_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('terms_meta')->item(0)->removeChild($term_meta_element);
        }else{
                $term_meta_element->getElementsByTagName('meta_id')->item(0)->nodeValue = $row[0]->meta_id;
                $term_meta_element->getElementsByTagName('term_id')->item(0)->nodeValue = $row[0]->term_id;
                $term_meta_element->getElementsByTagName('meta_key')->item(0)->nodeValue = $row[0]->meta_key;
                $term_meta_element->getElementsByTagName('meta_value')->item(0)->nodeValue = $row[0]->meta_value;
        }
    }
    
    $attribute_taxonomies_elements = $dom->getElementsByTagName('attribute_taxonomies')->item(0)->getElementsByTagName('attribute_taxonomy');
    $elements_length = $attribute_taxonomies_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $attribute_taxonomy_element = $attribute_taxonomies_elements[$i];
        $attribute_taxonomy_element_id = $attribute_taxonomy_element->getElementsByTagName('attribute_id')->item(0)->nodeValue;
        
        $row = $wpdb->get_results("SELECT * FROM `wphr_woocommerce_attribute_taxonomies` WHERE attribute_id = $attribute_taxonomy_element_id");
        if(empty($row)){
            $dom->getElementsByTagName('attribute_taxonomies')->item(0)->removeChild($attribute_taxonomy_element);
        }else{
                $attribute_taxonomy_element->getElementsByTagName('attribute_id')->item(0)->nodeValue = $row[0]->attribute_id;
                $attribute_taxonomy_element->getElementsByTagName('attribute_name')->item(0)->nodeValue = $row[0]->attribute_name;
                $attribute_taxonomy_element->getElementsByTagName('attribute_label')->item(0)->nodeValue = $row[0]->attribute_label;
                $attribute_taxonomy_element->getElementsByTagName('attribute_type')->item(0)->nodeValue = $row[0]->attribute_type;
                $attribute_taxonomy_element->getElementsByTagName('attribute_orderby')->item(0)->nodeValue = $row[0]->attribute_orderby;
                $attribute_taxonomy_element->getElementsByTagName('attribute_public')->item(0)->nodeValue = $row[0]->attribute_public;
        }
    }
    
    saveXMLFile($dom);
    $endTime = microtime(true);
    echo "Checkup updates time: " . ($endTime - $startTime);
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