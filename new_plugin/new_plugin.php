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

    if(isset($_POST['xml_btn'])){
            update_SQL_from_XML();
        
    }

    ?>
        <form method="post" action="">
        <input type="submit" name="xml_btn" value="Import xml">
        </form> 
    <?php
}

function sqlToXml(){
    global $wpdb;
    $query = "SELECT * FROM wphr_posts";
    
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xmlRoot = $xml->createElement('wphr_posts');
    $xmlRoot = $xml->appendChild($xmlRoot);
    $rows = $wpdb->get_results($query);
    foreach($rows as $row){
        $currentPerson = $xml->createElement("post");
        $currentPerson = $xmlRoot->appendChild($currentPerson);
        $currentPerson->appendChild($xml->createElement('ID',$row->ID));
        $currentPerson->appendChild($xml->createElement('post_author', $row->post_author));
        $currentPerson->appendChild($xml->createElement('post_date',$row->post_date));
        $currentPerson->appendChild($xml->createElement('post_date_gmt',$row->post_date_gmt));
        $currentPerson->appendChild($xml->createElement('post_content', $row->post_content));
        $currentPerson->appendChild($xml->createElement('post_title', $row->post_title));
        $currentPerson->appendChild($xml->createElement('post_excerpt', $row->post_excerpt));
        $currentPerson->appendChild($xml->createElement('post_status', $row->post_status));
        $currentPerson->appendChild($xml->createElement('comment_status', $row->comment_status));
        $currentPerson->appendChild($xml->createElement('ping_status', $row->ping_status));
        $currentPerson->appendChild($xml->createElement('post_password', $row->post_password));
        $currentPerson->appendChild($xml->createElement('post_name', $row->post_name));
        $currentPerson->appendChild($xml->createElement('to_ping', $row->to_ping));
        $currentPerson->appendChild($xml->createElement('pinged', $row->pinged));
        $currentPerson->appendChild($xml->createElement('post_modified', $row->post_modified));
        $currentPerson->appendChild($xml->createElement('post_modified_gmt', $row->post_modified_gmt));
        $currentPerson->appendChild($xml->createElement('post_content_filtered', $row->post_content_filtered));
        $currentPerson->appendChild($xml->createElement('post_parent', $row->post_parent));
        $currentPerson->appendChild($xml->createElement('guid', $row->guid));
        $currentPerson->appendChild($xml->createElement('menu_order', $row->menu_order));
        $currentPerson->appendChild($xml->createElement('post_type', $row->post_type));
        $currentPerson->appendChild($xml->createElement('post_mime_type', $row->post_mime_type));
        $currentPerson->appendChild($xml->createElement('comment_count', $row->comment_count));
        
    }
    file_put_contents("wphr_posts.xml", $xml->saveXML());
    
}

function xmlToSql(){
    global $wpdb;
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->load('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') or die("Cant load xml file");
    $posts_elements = $dom->getElementsByTagName('post');
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
            'guid' => $post_element->getElementsByTagName('guid')->item(0)->nodeValue,
            'menu_order' => $post_element->getElementsByTagName('menu_order')->item(0)->nodeValue,
            'post_type' => $post_element->getElementsByTagName('post_type')->item(0)->nodeValue,
            'post_mime_type' => $post_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
            'comment_count' => $post_element->getElementsByTagName('comment_count')->item(0)->nodeValue
        );
        $wpdb->insert('wphr_posts', $new_insert);
    }
}

function xmlToSqlNewInserts(){
    global $wpdb; 
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read xml file in check inserts';
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    //$dom->load('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') or die("Cant load xml file");
    $dom->loadXML($xmlFile);
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
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    if(!$xmlFile = $wp_filesystem->get_contents('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') ) {
        echo 'failed to read xml file in updates';
    }

    $dom = new DomDocument('1.0', 'UTF-8');
    //$dom->load('https://placeadvisor.aserver.gr/wp-admin/wphr_posts.xml') or die("Cant load xml file");
    $dom->loadXML($xmlFile);
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
                $updates = array(
                    'post_author' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_author')->item(0)->nodeValue,
                    'post_date' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_date')->item(0)->nodeValue,
                    'post_date_gmt' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_date_gmt')->item(0)->nodeValue,
                    'post_content' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_content')->item(0)->nodeValue,
                    'post_title' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_title')->item(0)->nodeValue,
                    'post_excerpt' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_excerpt')->item(0)->nodeValue,
                    'post_status' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_status')->item(0)->nodeValue,
                    'comment_status' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('comment_status')->item(0)->nodeValue,
                    'ping_status' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('ping_status')->item(0)->nodeValue,
                    'post_password' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_password')->item(0)->nodeValue,
                    'post_name' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_name')->item(0)->nodeValue,
                    'to_ping' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('to_ping')->item(0)->nodeValue,
                    'pinged' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('pinged')->item(0)->nodeValue,
                    'post_modified' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_modified')->item(0)->nodeValue,
                    'post_modified_gmt' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue,
                    'post_content_filtered' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_content_filtered')->item(0)->nodeValue,
                    'post_parent' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_parent')->item(0)->nodeValue,
                    'guid' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('guid')->item(0)->nodeValue,
                    'post_type' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_type')->item(0)->nodeValue,
                    'post_mime_type' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('post_mime_type')->item(0)->nodeValue,
                    'comment_count' => $xpath->query("//wphr_posts/post/ID[text() = $db_insert->ID]/..")->item(0)->getElementsByTagName('comment_count')->item(0)->nodeValue
                );
                $wpdb->update('wphr_posts', $updates, array('ID' => $db_insert->ID));
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
    xmlToSqlUpdates();
    
}

function check_inserts(){
    global $wpdb;
    $table_name = 'wphr_posts';
    $last_insert_id = $wpdb->get_results("SELECT ID FROM $table_name ORDER BY ID DESC LIMIT 1"); // last ID
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->load("wphr_posts.xml");
    $root_node = $dom->getElementsByTagName('wphr_posts')->item(0);
    //$last_post = $dom->getElementsByTagName('ID')->length;
    $xml = new SimpleXMLElement($dom->saveXML());
    $last_element = $xml->xpath("/wphr_posts/post[last()]"); // last element in xml file
    $last_element_num = $last_element[0]->ID;
    $last_insert_id_num = $last_insert_id[0]->ID;
    if($last_element[0]->ID < $last_insert_id[0]->ID){
        $new_inserts = $wpdb->get_results("SELECT * FROM $table_name where ID between $last_element_num+1 and $last_insert_id_num");
        foreach($new_inserts as $new_insert){
            $post_node = $root_node->appendChild($dom->createElement('post'));
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
        file_put_contents("wphr_posts.xml", $dom->saveXML());
    }
}

function checkUpDates(){
    global $wpdb;
    $dom = new DOMDocument();
    $dom->load("wphr_posts.xml");
    //$element = $dom->getElementsByTagName('post')[75];
    //$old_element = $dom->documentElement->removeChild($element);
    $posts_elements = $dom->getElementsByTagName('post');
    $elements_length = $posts_elements->count();
    for($i=$elements_length-1; $i>=0; $i--){
        
        $post_element = $posts_elements[$i];
        $post_element_id = $post_element->getElementsByTagName('ID')->item(0)->nodeValue;
        //$row = $wpdb->get_results("SELECT ID FROM `wphr_posts` WHERE ID = $post_element_id");
        $row = $wpdb->get_results("SELECT * FROM `wphr_posts` WHERE ID = $post_element_id");
        if(empty($row)){
            $old_element = $dom->documentElement->removeChild($dom->getElementsByTagName('post')[$i]);
            
        }else{
            $db_timestamp = new DateTime($row[0]->post_modified);
            $xml_timestamp = new DateTime($post_element->getElementsByTagName('post_modified')->item(0)->nodeValue);
            if($db_timestamp > $xml_timestamp){
                $post_element->getElementsByTagName('post_author')->item(0)->nodeValue = $row[0]->$post_author;
                $post_element->getElementsByTagName('post_date')->item(0)->nodeValue = $row[0]->$post_date;
                $post_element->getElementsByTagName('post_date_gmt')->item(0)->nodeValue = $row[0]->$post_date_gmt;
                $post_element->getElementsByTagName('post_content')->item(0)->nodeValue = $row[0]->$post_content;
                $post_element->getElementsByTagName('post_title')->item(0)->nodeValue = $row[0]->$post_title;
                $post_element->getElementsByTagName('post_excerpt')->item(0)->nodeValue = $row[0]->$post_excerpt;
                $post_element->getElementsByTagName('post_status')->item(0)->nodeValue = $row[0]->$post_status;
                $post_element->getElementsByTagName('comment_status')->item(0)->nodeValue = $row[0]->$comment_status;
                $post_element->getElementsByTagName('ping_status')->item(0)->nodeValue = $row[0]->$ping_status;
                $post_element->getElementsByTagName('post_password')->item(0)->nodeValue = $row[0]->$post_password;
                $post_element->getElementsByTagName('post_name')->item(0)->nodeValue = $row[0]->$post_name;
                $post_element->getElementsByTagName('to_ping')->item(0)->nodeValue = $row[0]->$to_ping;
                $post_element->getElementsByTagName('pinged')->item(0)->nodeValue = $row[0]->$pinged;
                $post_element->getElementsByTagName('post_modified')->item(0)->nodeValue = $row[0]->$post_modified;
                $post_element->getElementsByTagName('post_modified_gmt')->item(0)->nodeValue = $row[0]->$post_modified_gmt;
                $post_element->getElementsByTagName('post_content_filtered')->item(0)->nodeValue = $row[0]->$post_content_filtered;
                $post_element->getElementsByTagName('post_parent')->item(0)->nodeValue = $row[0]->$post_parent;
                $post_element->getElementsByTagName('guid')->item(0)->nodeValue = $row[0]->$guid;
                $post_element->getElementsByTagName('menu_order')->item(0)->nodeValue = $row[0]->$menu_order;
                $post_element->getElementsByTagName('post_type')->item(0)->nodeValue = $row[0]->$post_type;
                $post_element->getElementsByTagName('post_mime_type')->item(0)->nodeValue = $row[0]->$post_mime_type;
                $post_element->getElementsByTagName('comment_count')->item(0)->nodeValue = $row[0]->$comment_count;
            }elseif($db_timestamp == $xml_timestamp){
                //$wpdb->update('wphr_posts', array('post_modified' => '2020-06-11 09:33:20'), array('ID' => '268'));
            }
        }
    }
    
    file_put_contents("wphr_posts.xml", $dom->saveXML()); // Save to xml file
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
 register_activation_hook( $pluginFile, 'xmlToSql' );
 
 add_action('admin_menu', 'add_admin_page');
 add_shortcode( 'database', 'updateItems' );
 add_shortcode( 'submitform', 'submitForm' );
 
?>