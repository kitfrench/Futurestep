<?php

add_theme_support( 'post-thumbnails' );

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
        
        $my_id = icl_object_id(207, 'page');
	$newrules['([^/]+)/'.get_post($my_id)->post_name.'/(.+)'] = 'index.php?page_id='.$my_id.'&eventyear=$matches[2]';
        
        $my_id = icl_object_id(204, 'page');
        $secondnewrules = array();
	$secondnewrules['([^/]+)/'.get_post($my_id)->post_name.'/(.+)'] = 'index.php?page_id='.$my_id.'&release=$matches[2]';
        
        $my_id = icl_object_id(32, 'page');
        $thirdnewrules = array();
	$thirdnewrules[get_post($my_id)->post_name.'/(.+)'] = 'index.php?page_id='.$my_id.'&sector=$matches[1]';
        
        $fourthnewrules = array();
	$fourthnewrules['leaders/(.+)'] = 'index.php/?experts=$matches[1]';
        
        $my_id = icl_object_id(38, 'page');
        $fifthnewrules = array();
	$fifthnewrules[get_post($my_id)->post_name.'/region/(.+)'] = 'index.php?page_id='.$my_id.'&category-region=$matches[1]';
	
        $finalrules = $fifthnewrules + $fourthnewrules + $thirdnewrules + $secondnewrules + $newrules + $rules;
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
    
    if($feedaddress!='') :
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
        
    endif;
    }
    
 //
 function outputhomejobsslider() {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS `feedstore` ( `ID` INT NOT NULL AUTO_INCREMENT , `time` TIMESTAMP NOT NULL ,`output` VARCHAR( 50000 ) NOT NULL, PRIMARY KEY (  `ID` ))");
        
        $wpdb->query("SELECT * FROM `feedstore`");
        $columns = $wpdb->get_col_info('name', -1);
    
        if(!in_array('langauge', $columns)) {
            update_jobslider_db();
        }
        
        $languages = icl_get_languages('skip_missing=1');
        $x=1;
          foreach ($languages as $l) :
            $wpdb->query("INSERT IGNORE INTO feedstore (`language`, `ID`) VALUES('".$l['language_code']."', '".$x."');");
            $x++;
          endforeach;
        
        $querystr="SELECT * FROM feedstore WHERE `language` = '".ICL_LANGUAGE_CODE."' AND `time`>(NOW()-10800)";
        $feedoutput = $wpdb->get_row($querystr, OBJECT);
    
      if(is_null($feedoutput))   {
        $string = get_include_contents("wp-content/themes/futurestep/home-jobsslider.php");
        
        $query = sprintf("UPDATE `feedstore` SET `time`=CURRENT_TIMESTAMP, `output`='%s' WHERE language='".ICL_LANGUAGE_CODE."'",  mysql_real_escape_string($string));
        $wpdb->query($query);
        echo $string;
        
      } else {
          echo $feedoutput->output;
      }

}

function update_jobslider_db() {
        $wpdb->query("ALTER TABLE `feedstore` ADD `language` VARCHAR( 50 ) NOT NULL;");
        $wpdb->query("UPDATE `feedstore` SET `language`='en'");
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

    function format_content($content)
    {
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);
        return $content;
    }

    function dateHasPosts($wpdb, $year, $month, $day, $type){

       //echo('year = '.$year.'. Month = '.$month.'. Day = '.$day.'. Type = '.$type);

        if(strlen($day) == 1){
            $day = '0'.$day;
        }

      $qrydate = $year."-".$month."-".$day ;
        //echo('$qrydate = '.$qrydate);

      $querystr = "
        SELECT $wpdb->posts.*
        FROM $wpdb->posts, $wpdb->postmeta
        WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
        AND $wpdb->postmeta.meta_key = 'events-date'
        AND $wpdb->postmeta.meta_value LIKE '".$qrydate."'
        AND $wpdb->posts.post_status = 'publish'
        AND $wpdb->posts.post_type = 'news-events'
        AND $wpdb->posts.post_date < NOW()
        ORDER BY $wpdb->postmeta.meta_value ASC
      ";

     $pageposts = $wpdb->query($querystr, OBJECT);

    return $pageposts;
    }

    function surroundStringWith($stringValue, $template){

        //return nothing if the string value is empty.
        if($stringValue == null || strLen($stringValue) <= 0){
            return '';
        }

        if($stringValue == null || strLen($stringValue) <= 0){
            throw new Exception('no template provided to surroundStringWith function');
        }

        $pos = strpos($template, "{{replace}}");
        if($pos <= 0){
            throw new Exception("surroundStringWith function : template is not valid, can't find {{replace}}");
        }

        return str_replace( "{{replace}}", $stringValue, $template);
    }

    
    
//TRANSLATION STRINGS
    
    icl_register_string('Header', 'Java Drop Down', 'Services');
    icl_register_string('Header', 'Find a job', 'Find a job');
    icl_register_string('Header', 'Futurestep home', 'Futurestep home');
    icl_register_string('Header', 'My futurestep', 'My futurestep');
    icl_register_string('Header', 'Subscribe', 'Subscribe');
    icl_register_string('Header', 'Strapline', 'Talent with impact');
    
    icl_register_string('Footer', 'Twitter Link', 'http://www.twitter.com/futurestep');
    icl_register_string('Footer', 'LinkedIn Link', 'http://www.linkedin.com/company/3740?goback=%2Efcs_GLHD_futurestep_false_*2_*2_*2_*2_*2_*2_*2_*2_*2_*2_*2_*2&trk=ncsrch_hits');
    
    icl_register_string('Home', 'Latest Insights', 'Latest Insights');
    icl_register_string('Home', 'View all insights', 'View all insights');
    icl_register_string('Home', 'Job Opportunites with our clients', 'Job Opportunites with our clients');
    icl_register_string('Home', 'Apply for this job', 'Apply for this job');
    icl_register_string('Home', 'See our most recent roles', 'See our most recent roles');
    icl_register_string('Home', 'Latest News and Events', 'Latest News and Events');
    icl_register_string('Home', 'Register for event', 'Register for event');
    
    icl_register_string('Carousel', 'Find out more', 'Find out more');
    
    icl_register_string('Home', 'Working for us', 'Working for us');
    icl_register_string('Home', 'Working for us - prose', 'Futurestep has a global presence, with offices across five continents. No matter which one of them
                    interests you, we encourage you to register and start the application process immediately.');
    icl_register_string('Home', 'Find out more', 'Find out more');
    icl_register_string('Home', 'View all news and events', 'View all news and events');
    
    icl_register_string('Home', 'Global Network', 'Global Network');
    icl_register_string('Home', '39 offices in 20 countries with 800+ professionals', '39 offices in 20 countries with 800+ professionals');
    
    
    icl_register_string('Job Form', 'Find an expert', 'Find an expert');
    icl_register_string('Job Form', 'Select a country', 'Select a country');
    icl_register_string('Job Form', 'Select a sector', 'Select a sector');
    icl_register_string('Job Form', 'Select a solution', 'Select a solution');
    
    icl_register_string('Search button', 'Search', 'Search');
    
    icl_register_string('Clients', 'All Sectors', 'All Sectors');
    icl_register_string('Clients', 'Some of the clients', 'Some of the clients');
    icl_register_string('Clients', 'Read the case study', 'Read the case study');
    
    icl_register_string('Insights - Landing', 'Featured opinion', 'Featured opinion');
    icl_register_string('Insights - Landing', 'View all opinions', 'View all opinions');
    icl_register_string('Insights - Landing', 'Featured blog post', 'Featured blog post');
    icl_register_string('Insights - Landing', 'View all posts', 'View all posts');
    icl_register_string('Insights - Landing', 'Most read articles', 'Most read articles');
    icl_register_string('Insights - Landing', 'See all articles', 'See all articles');
    
    icl_register_string('News - Landing', 'View events calendar', 'View events calendar');
    icl_register_string('News - Landing', 'View all news', 'View all news');
    icl_register_string('News - Landing', 'View all press releases', 'View all press releases');
    icl_register_string('News - Landing', 'In the News', 'In the News');
    icl_register_string('News - Landing', 'Press releases', 'Press releases');
    
    icl_register_string('Sign up', 'Sign up to hear more', 'Sign up to hear more');
    icl_register_string('Sign up', 'Sign up - prose', 'Receive updates on white papers, articles, and other valuable thought leadership resources covering all
            facets of talent acquisition and management.');
    icl_register_string('Sign up', 'Sign up', 'Sign up');
    
    icl_register_string('Contact', 'Global offices', 'Global offices');
    icl_register_string('Contact', 'Find an Expert', 'Find an Expert');
    icl_register_string('Contact', 'Filter by Region', 'Filter by Region');
    icl_register_string('Contact', 'Show all offices', 'Show all offices');
    
    icl_register_string('Leadership', 'Regional Leadership', 'Regional Leadership');
    icl_register_string('Leadership', 'Global', 'Global');
    
    icl_register_string('404', 'Sorry about this...', 'Sorry about this...');
    icl_register_string('404', 'The page you are looking for could not be found!', 'The page you are looking for could not be found!');
    icl_register_string('404', 'Homepage', 'Homepage');
    icl_register_string('404', 'Site map', 'Site map');
    icl_register_string('404', '404 - Prose', "Sometimes pages move or go out of date and get deleted. It's possible that the page you are looking for has either changed names or is no longer available. Please visit our <b>homepage</b> or <b>site map</b> for the most up-to-date list of our pages.");
    
    icl_register_string('Events Calendar', 'Sunday', 'Sunday');
    icl_register_string('Events Calendar', 'Monday', 'Monday');
    icl_register_string('Events Calendar', 'Tuesday', 'Tuesday');
    icl_register_string('Events Calendar', 'Wednesday', 'Wednesday');
    icl_register_string('Events Calendar', 'Thursday', 'Thursday');
    icl_register_string('Events Calendar', 'Friday', 'Friday');
    icl_register_string('Events Calendar', 'Saturday', 'Saturday');
    
    icl_register_string('Events Calendar', 'No events to show', 'We have no events scheduled this month. Use the links below to search for forthcoming events');
    icl_register_string('Events Calendar', 'Forthcoming Events', 'Forthcoming Events');
    icl_register_string('Events Calendar', 'Previous Events', 'Previous Events');
    
    icl_register_string('Download PDF', 'Download PDF', 'Download PDF');
    
    icl_register_string('Press Releases', 'Empty Year', 'There are no press releases in this year.');
    
    icl_register_string('Asides', 'Contact your local specialist', 'Contact your local specialist');
    
    icl_register_string('Asides', 'Latest News and Events', 'Latest News and Events');
    icl_register_string('Asides', 'Related Links', 'Related Links');
    
    icl_register_string('Single Events', 'Back to Events Calendar', 'Back to Events Calendar');
    icl_register_string('Single Events', 'Register for event', 'Register for event');
    
    icl_register_string('Single Experts', 'Location', 'Location');
    
    icl_register_string('Site Map', 'Home', 'Home');
    icl_register_string('Site Map', 'Services', 'Services');
    icl_register_string('Site Map', 'Clients', 'Clients');
    icl_register_string('Site Map', 'Insights', 'Insights');
    icl_register_string('Site Map', 'All Insights', 'All Insights');
    icl_register_string('Site Map', 'All Articles', 'All Articles');
    icl_register_string('Site Map', 'News and Events', 'News and Events');
    icl_register_string('Site Map', 'Event Calendar', 'Event Calendar');
    icl_register_string('Site Map', 'All News', 'All News');
    icl_register_string('Site Map', 'Press Release', 'Press Release');
    icl_register_string('Site Map', 'About Us', 'About Us');
    icl_register_string('Site Map', 'Leadership Team', 'Leadership Team');
    icl_register_string('Site Map', 'Contact', 'Contact');
    icl_register_string('Site Map', 'Find an Expert', 'Find an Expert');
    icl_register_string('Site Map', 'Privacy', 'Privacy');
    
    ///
    include_once 'fs-jobslider.php';
?>