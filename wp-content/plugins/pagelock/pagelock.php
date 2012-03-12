<?php
/*
Plugin Name: Lock Services Pages from Edit
Plugin URI: 
Description: 
Version: 0.1
Author: Eliot Fallon
Author URI: http://www.masonwebdevelopment.com
*/

function lock_parent_pages_from_edit( $capauser, $capask, $param){
    global $wpdb;
    //var_dump($capauser);

    $post = get_post( $param[2] );
    
    if (5 == $post->post_parent || 5==$post->ID) {
        if( $param[0] == "edit_page" ) {
              foreach( (array) $capask as $capasuppr) {
                 if ( array_key_exists($capasuppr, $capauser) ) {
                    $capauser[$capasuppr] = 0;
                 }
              }
        }
    }

    return $capauser;
    
}
add_filter('user_has_cap', 'lock_parent_pages_from_edit', 100, 3 );
?>