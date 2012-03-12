<?php
/*
Plugin Name: Futurestep Carousel
Plugin URI: http://www.furthercreative.co.uk/
Description: Futurestep Carousel
Author: Eliot Fallon
Version: 1
Author URI: http://www.furthercreative.co.uk/
*/
//echo "ADD MySQL creation here";
$wpdb->query('CREATE TABLE IF NOT EXISTS `carousel` (
`slot` INT NOT NULL AUTO_INCREMENT ,
`image` VARCHAR( 250 ) NOT NULL ,
`line1` VARCHAR( 250 ) NOT NULL ,
`line2` VARCHAR( 250 ) NOT NULL ,
`line3` VARCHAR( 250 ) NOT NULL ,
`link` VARCHAR( 350 ) NOT NULL ,
`modified` TIMESTAMP NOT NULL ,
`language` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY (  `slot` ))');

$wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('1', 'en');");
$wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('2', 'en');");
$wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('3', 'en');");
$wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('4', 'en');");

//ADD PAGE TO SHOW Carousel Admin
add_action('admin_menu', 'carousel_adminmenu');

function carousel_adminmenu() {

  add_menu_page('Carousel', 'Carousel', 'edit_pages', 'carousel', 'carousel_adminoptions');
  //add_submenu_page( 'carousel', 'Page title', 'Carousel', 'edit_pages', 'carousel-page', 'carousel_adminoptions');
  //add_pages_page('Carousel', 'Carousel', 'edit_pages', 'carousel-admin', 'carousel_adminoptions');

}

function carousel_adminoptions() {
    
 global $wpdb;
  if (!current_user_can('edit_pages'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
  
  
  $languages = icl_get_languages('skip_missing=1');

  $x=1;
  foreach ($languages as $l) :
    $wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('".$x."', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('".$x."', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('".$x."', '".$l['language_code']."');");
    $x++;
    $wpdb->query("INSERT IGNORE INTO carousel (`slot`, `language`) VALUES('".$x."', '".$l['language_code']."');");
    $x++;    
  endforeach;
  
  
  $carousels = $wpdb->get_results("SELECT * FROM `carousel` ORDER BY `slot`");
    foreach( $carousels as $carousel ) { 
        if($_POST['Save']) {
            $slot_id=$carousel->slot;
            $wpdb->query("UPDATE carousel SET `image` = '".$_POST[$slot_id.'-image']."', `line1` = '".$_POST[$slot_id.'-line1']."', `line2` = '".$_POST[$slot_id.'-line2']."',`line3` = '".$_POST[$slot_id.'-line3']."', `link` = '".$_POST[$slot_id.'-link']."', `modified`=NOW() WHERE slot ='".$carousel->slot."'");
        } 
    }
 
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

foreach ($languages as $l) :
?>
  <h3>Language: <?php echo $l['native_name']; ?></h3>
  <table class="widefat">
  <thead>
  <tr><th>Slot</th><th>Image</th><th>Line 1</th><th>Line 2</th><th>Line 3</th><th>Where to link to?</th></tr>
  </thead>
  <tbody>
  <?php 
  $carousels = $wpdb->get_results("SELECT * FROM `carousel` WHERE `language` = '".$l['language_code']."' ORDER BY `slot`");
  foreach( $carousels as $carousel ) { ?>
  <tr>
  <td><?php echo $carousel->slot; ?></td>
  <td><select name="<?php echo $carousel->slot; ?>-image" >
          <option <?php if($carousel->image=='tree') echo 'selected="selected" '; ?>value="tree">Tree</option>
          <option <?php if($carousel->image=='chess') echo 'selected="selected" '; ?>value="chess">Chess</option>
          <option <?php if($carousel->image=='butterfly') echo 'selected="selected" '; ?>value="butterfly">Butterfly</option>
          <option <?php if($carousel->image=='hangglider') echo 'selected="selected" '; ?>value="hangglider">Hangglider</option>
          <option <?php if($carousel->image=='rowers') echo 'selected="selected" '; ?>value="rowers">Rowers</option>
          <option <?php if($carousel->image=='head') echo 'selected="selected" '; ?>value="head">Head</option>
          <option <?php if($carousel->image=='trainers') echo 'selected="selected" '; ?>value="trainers">Trainers</option>
          <option <?php if($carousel->image=='dragon') echo 'selected="selected" '; ?>value="dragon">Dragon</option>
          <option <?php if($carousel->image=='globe') echo 'selected="selected" '; ?>value="globe">Globe</option>
      </select>
  </td>
  <td><input type="text" name="<?php echo $carousel->slot; ?>-line1" value="<?php echo $carousel->line1; ?>"/></td>
  <td><input type="text" name="<?php echo $carousel->slot; ?>-line2" value="<?php echo $carousel->line2; ?>"/></td>
  <td><input type="text" name="<?php echo $carousel->slot; ?>-line3" value="<?php echo $carousel->line3; ?>"/></td>
  <td><input type="text" name="<?php echo $carousel->slot; ?>-link" value="<?php echo $carousel->link; ?>"/></td>
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

?>
