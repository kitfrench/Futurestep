<?php

add_theme_support( 'post-thumbnails' );

add_action('save_post', 'save_json_data');

function slugify($variable) {
    $slugifysector = trim($variable);
    $slugifysector = strtolower ($slugifysector);
    $slugifysector = str_replace( ' ', '-', $slugifysector);
    return $slugifysector;
}

function save_json_data(){
	global $post;

$args = array( 'post_type' => 'case-studies');
$loop = new WP_Query( $args );
$allsectors = array();
$alllocation = array();
$allservice = array();

//Loop through Case Studies
  while ( $loop->have_posts() ) { $loop->the_post(); 
   
      //Reset Each posts collections of Sectors and Services
      $currentsector = array();
      $currentservice = array();
  
      $title=get_the_title(); 
      $url=get_permalink();
      
      //break sector into separate sectors
      $pieces = explode(",", get_post_meta(get_the_ID(), 'sector', true));
      
      // Turn each sector into ID that will be held in 'Sector' of JSON
      foreach($pieces as $sectorpiece) {
        $sectorpiece = trim($sectorpiece);
        $slugifysector = slugify($sectorpiece);
        $currentsector[] = $slugifysector;
        
       // If not held already add full Title into array to create Sector JSON
        if (!in_array($sectorpiece, $allsectors) && $sectorpiece !='') {
            $allsectors[]=$sectorpiece;
        }
      }
      
      //Adds any new Locations to the array.
      if (!in_array(get_post_meta(get_the_ID(), 'location', TRUE), $alllocation)) {
          $alllocation[]=get_post_meta(get_the_ID(), 'location', true);
      }
      
      //break services into separate services
      $pieces = explode(",", get_post_meta(get_the_ID(), 'service', true));
      
      //Turn each Service into ID that will be held to create Service JOSN
      foreach($pieces as $servicepiece) {
        $servicepiece = trim($servicepiece);
        $slugifyservice = slugify($servicepiece);
        $currentservice[] = $slugifyservice;
        
        //If not held already there add full Title into array to create Service JSON
        if (!in_array($servicepiece, $allservice) && $sectorpiece !='') {
            $allservice[]=$servicepiece;
        }
      }
      
      $image=wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
      //Write post details all into an array to be added to JSON
      $posts[] = array('title'=> $title, 
        'url'=> $url, 
        "id" => slugify($title),
        "excerpt" => get_the_excerpt(),
        "image" => $image[0],
        "location" => slugify(get_post_meta(get_the_ID(), 'location', true)),
        "sector" => $currentsector,
        "service" => $currentservice,
        "link"  => get_permalink());
  }
  
  //Take array of all sectors and break them into an array of Title and slugified ID
  foreach($allsectors as $onesector) {
    $slug = slugify($onesector);
    if($slug!='') {
        $sectors[] = array ( 'id' => $slug, 'title' => $onesector);
    }
  }
  
  //Take array of all locations and break them into an array of Title and slugified ID
  foreach($alllocation as $onelocation) {
    $slug = slugify($onelocation);
    
    if($slug!='') {
        $locations[] = array ( 'id' => $slug, 'title' => $onelocation);
    }
  }
  
  //Take array of all services and break them into an array of Title and slugified ID
  foreach($allservice as $oneservice) {
    $slug = slugify($oneservice);
    
    if($slug!='') {
        $services[] = array ( 'id' => $slug, 'title' => $oneservice);
    }
  }
  
$response['client'] = $posts;
$response['sector'] = $sectors;
$response['location'] = $locations;
$response['service'] = $services;

$fp = fopen('../results.json', 'w');
fwrite($fp, json_encode($response));
fclose($fp);

}


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
	$finalrules = $thirdnewrules + $secondnewrules + $newrules + $rules;
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

?>