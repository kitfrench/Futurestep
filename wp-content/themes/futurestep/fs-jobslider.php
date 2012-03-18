<?php

//echo "ADD MySQL creation here";
$wpdb->query('CREATE TABLE IF NOT EXISTS `feedlocations` (
`slot` INT NOT NULL AUTO_INCREMENT ,
`category` VARCHAR( 250 ) NOT NULL ,
`locations` VARCHAR( 250 ) NOT NULL ,
`language` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY (  `slot` ))');
if(function_exists('icl_get_languages')) :
  $languages = icl_get_languages('skip_missing=1');
  $x=1;  
  foreach ($languages as $l) :
    $wpdb->query("INSERT IGNORE INTO feedlocations (`slot`, `category`, `language`) VALUES('".$x."', 'Technology Services', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO feedlocations (`slot`, `category`, `language`) VALUES('".$x."', 'Industrial', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO feedlocations (`slot`, `category`, `language`) VALUES('".$x."', 'Government & Not For Profit', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO feedlocations (`slot`, `category`, `language`) VALUES('".$x."', 'Life Sciences', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO feedlocations (`slot`, `category`, `language`) VALUES('".$x."', 'Consumer','".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO feedlocations (`slot`, `category`, `language`) VALUES('".$x."', 'Financial Services','".$l['language_code']."');");
    $x++;
  endforeach;

//ADD PAGE TO SHOW Carousel Admin
add_action('admin_menu', 'jobslider_adminmenu');

function jobslider_adminmenu() {

  add_menu_page('Job Slider', 'Job Slider', 'edit_pages', 'jobslider', 'jobslider_adminoptions');
  //add_submenu_page( 'carousel', 'Page title', 'Carousel', 'edit_pages', 'carousel-page', 'carousel_adminoptions');
  //add_pages_page('Carousel', 'Carousel', 'edit_pages', 'carousel-admin', 'carousel_adminoptions');

}

function recache_slider($lang) {
    global $sitepress;
    global $wpdb;
    $oldLang = ICL_LANGUAGE_CODE;
    $sitepress->switch_lang($lang);
    
    $string = get_include_contents("../wp-content/themes/futurestep/home-jobsslider.php");   
    $query = sprintf("UPDATE `feedstore` SET `time`=CURRENT_TIMESTAMP, `output`='%s' WHERE language='".$lang['language_code']."'",  mysql_real_escape_string($string));
    $wpdb->query($query);
    $wpdb->print_error();
    $sitepress->switch_lang($oldlang);
}

function jobslider_adminoptions() {
    
 global $wpdb;
  if (!current_user_can('edit_pages'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
  
  
  $languages = icl_get_languages('skip_missing=1');
  
  $locations = $wpdb->get_results("SELECT * FROM `feedlocations` ORDER BY `slot`");
    foreach( $locations as $location ) { 
        if($_POST['Save']) {
            $slot_id=$location->slot;
            $wpdb->query("UPDATE feedlocations SET `locations` = '".$_POST[$slot_id.'-locations']."', `category` = '".$_POST[$slot_id.'-category']."', `language` = '".$_POST[$slot_id.'-language']."' WHERE slot ='".$location->slot."'");         
        } 
    }
  
  $languages = icl_get_languages('skip_missing=1');
    foreach ($languages as $l) : 
        if($_POST['Save']) {
            recache_slider($l);          
        } 
    endforeach;

?>
  <div class="wrap">
  <div id="icon-edit-pages" class="icon32"></div>
  <h2>Carousel Options</h2>
  <p>The table below displays allows you to update and alter the homepage carousel.</p>

<form name="input" action="" method="post">  
  <p><input class="button-primary" type="submit" name="Save" value="Save Options" id="save" /></p>
  <?php 
if($_POST['Save']) echo "<h4>Details Saved</h4>";
$languages = icl_get_languages('skip_missing=1');

foreach ($languages as $l) : ?>
  <h3>Language: <?php echo $l['native_name']; ?></h3>
  <table class="widefat">
  <thead>
  <tr><th>Slot</th><th>Category</th><th>Location</th></tr>
  </thead>
  <tbody>
  <?php 
  $locations = $wpdb->get_results("SELECT * FROM `feedlocations` WHERE `language` = '".$l['language_code']."' ORDER BY `slot`");
  foreach( $locations as $location ) { ?>
  <tr>
  <td><?php echo $location->slot; ?></td>
  <td><?php echo $location->category; ?><input type="hidden" name="<?php echo $location->slot; ?>-category" value="<?php echo $location->category; ?>"/></td>
  <td><input type="text" name="<?php echo $location->slot; ?>-locations" value="<?php echo $location->locations; ?>"/>
      <input type="hidden" name="<?php echo $location->slot; ?>-language" value="<?php echo $location->language; ?>"/>
  </td>
  </tr>
  <?php } ?>
  </tbody>
  </table>
<?php endforeach; ?>
  
  </form>
  <div>
  </div>
  </div>
<?php
}
//
endif;
?>