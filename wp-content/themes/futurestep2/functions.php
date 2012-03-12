<?php

add_theme_support( 'post-thumbnails' );

add_action('save_post', 'copy_post');

function copy_post(){
    //var_dump($post_ID);
    //global $post;
    //var_dump($post);
    
}


//URL REWRITES
add_filter('rewrite_rules_array','wp_insertMyRewriteRules');
add_filter('query_vars','wp_insertMyRewriteQueryVars');
add_filter('init','flushRules');

// Remember to flush_rules() when adding rules
function flushRules(){
	global $wp_rewrite;
   	$wp_rewrite->flush_rules();
}

// Adding a new rule
function wp_insertMyRewriteRules($rules){
	$newrules = array();
	$newrules['([^/]+)/events-calendar/(.+)'] = 'index.php?pagename=news-and-events/events-calendar&eventyear=$matches[2]';
        $secondnewrules = array();
	$secondnewrules['([^/]+)/press-releases/(.+)'] = 'index.php?pagename=news-and-events/press-releases&release=$matches[2]';
        $thirdnewrules = array();
	$thirdnewrules['working-with-us/(.+)'] = 'index.php?pagename=working-with-us&sector=$matches[1]';
        $fourthnewrules = array();
	$fourthnewrules['leaders/(.+)'] = 'index.php/?experts=$matches[1]';
	$finalrules = $fourthnewrules + $thirdnewrules + $secondnewrules + $newrules + $rules;
        return $finalrules;
}

// Adding the var so that WP recognizes it
function wp_insertMyRewriteQueryVars($vars){
    array_push($vars, 'eventyear');
    array_push($vars, 'release');
    array_push($vars, 'sector');
    return $vars;
}

//Stop wordpress from redirecting
remove_filter('template_redirect', 'redirect_canonical');

add_action('init', 'my_rem_editor_from_post_type');
function my_rem_editor_from_post_type() {
    remove_post_type_support( 'locations', 'editor' );
    remove_post_type_support( 'locations', 'excerpt' );
    remove_post_type_support( 'locations', 'trackbacks' );
    remove_post_type_support( 'locations', 'custom-fields' );
    remove_post_type_support( 'locations', 'author' );
    remove_post_type_support( 'locations', 'comments' );
}

function remove_menus () {
global $menu;
	$restricted = array(__('Posts'));
	end ($menu);
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
	}
}
add_action('admin_menu', 'remove_menus');

if (class_exists('MultiPostThumbnails')) {
    new MultiPostThumbnails(array(
    'label' => 'Secondary Image',
    'id' => 'secondary-image',
    'post_type' => 'case-studies'
    )
);
}


function myfeed_request($qv) {
	    if (isset($qv['feed']))
	        $qv['post_type'] = array('insight', 'articles', 'press', 'news-events', 'opinion', 'news');
	    return $qv;
	}
	add_filter('request', 'myfeed_request');
    
    
function copyeditorscreenoptions($userid) {
    global $wpdb;
    $querystr="SELECT * FROM $wpdb->usermeta WHERE (`meta_key` LIKE 'metabox%' OR `meta_key` LIKE 'closedpostboxes%' OR `meta_key` LIKE 'screen_layout%') AND `user_id` = 2";
    $results = $wpdb->get_results($querystr, OBJECT);

    foreach( $results as $result) {
        $querystr = "INSERT INTO $wpdb->usermeta (`user_id`, `meta_key`, `meta_value`) VALUES ($userid, '$result->meta_key', '$result->meta_value')";
        $wpdb->query($querystr);
    }

    }
    
add_action('user_register','copyeditorscreenoptions',10,3);

//SET THE SCREEN OPTIONS FOR ALL USERS EXCEPT ADMIN TO THE SAME AS EDITOR
$querystr="SELECT * FROM $wpdb->users WHERE `ID` != 1;";
$users = $wpdb->get_results($querystr, OBJECT);


$querystr="SELECT * FROM $wpdb->usermeta WHERE (`meta_key` LIKE 'metabox%' OR `meta_key` LIKE 'closedpostboxes%' OR `meta_key` LIKE 'screen_layout%') AND `user_id` = 2";
$results = $wpdb->get_results($querystr, OBJECT);

foreach( $results as $result) {
    foreach($users as $user){
        $userid=$user->ID;
        global $wpdb;
        $querystr = "UPDATE $wpdb->usermeta SET `meta_value`='$result->meta_value' WHERE `user_id`=$userid AND `meta_key`='$result->meta_key'";
        $wpdb->query($querystr);
    }
}
//

function clean_wp_width_height($string){
	return preg_replace('/\<(.*?)(width="(.*?)")(.*?)(height="(.*?)")(.*?)\>/i', '<$1$4$7>',$string);
}

function getTruthy($trueOrFalseString){
    if($trueOrFalseString == 'true' || $trueOrFalseString == '1' ){
        return true;
    }

    return false;
}


//FEED FUNCTIONS

//PARSE THE FEED
function getJobDetails($feedtype, $feedaddress) {
    //TODO: Caching not working - fix!!
    
    $cacheKey = $feedtype;
    $cacheGroup = $feedaddress;

    $cachedJobs = wp_cache_get($cacheKey);

    if($cachedJobs != false){
        return $cachedJobs;
    }

    $xml = simplexml_load_file($feedaddress);
    $x=0;
    (object) $jobdetails = null;
            
    foreach($xml->channel->children() as $item) :
        if($item->getName()=='item' && $x==0) :
         foreach($item->children() as $details) :
         
           $jobdetails[$feedtype][$details->getName()] = $details;
        
          endforeach;
          $x=1;
        endif;
    endforeach;

    wp_cache_set($cacheKey, $jobdetails);

    return $jobdetails;
    }
    
 //
    function outputhomejobsslider() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS `feedstore` ( `ID` INT NOT NULL AUTO_INCREMENT , `time` TIMESTAMP NOT NULL ,`output` VARCHAR( 50000 ) NOT NULL, PRIMARY KEY (  `ID` ))");
        $querystr="SELECT * FROM feedstore WHERE `ID` = 1 AND `time`>(NOW()-10800)";
        
        $feedoutput = $wpdb->get_row($querystr, OBJECT);
    
      if(is_null($feedoutput))   {
        $string = get_include_contents("wp-content/themes/futurestep/home-jobsslider.php");
        
        $wpdb->query("TRUNCATE TABLE `feedstore`");
        $query = sprintf("INSERT INTO `feedstore` (`ID`, `time`, `output`) VALUES ('1', CURRENT_TIMESTAMP, '%s')",  mysql_real_escape_string($string));
        $wpdb->query($query);
        echo $string;
        
      } else {
          echo $feedoutput->output;
      }

    }
    
    //READ OUT JOBSSLIDER FILE
    function get_include_contents($filename) {
            if (is_file($filename)) {
                ob_start();
                include $filename;
                return ob_get_clean();
            }
            
        return false;
        }
        //

?>