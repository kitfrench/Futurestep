<?php
/*
Plugin Name: WordPress Meta Keywords
Plugin URI: http://www.destio.de/tools/wp-meta-keywords/
Description: This plugin gives you full control of <code>meta keywords</code> for posts and pages.
Author: Designstudio, Philipp Speck
Version: 1.3
Author URI: http://www.destio.de/
*/

if ( !class_exists ('wp_meta_keywords_plugin')) {
	class wp_meta_keywords_plugin {

	function meta_keywords_textdomain() {
		load_plugin_textdomain( 'wpmkp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function meta_keywords_init() {
		  $labels = array(
			'name' => __( 'Keywords', 'wpmkp' ),
			'singular_name' => __( 'Keyword', 'wpmkp' ),
			'search_items' =>  __( 'Search Keywords', 'wpmkp' ),
			'popular_items' => __( 'Popular Keywords', 'wpcsp'),
			'all_items' => __( 'All Keywords', 'wpmkp' ),
			'parent_item' => __( 'Parent Keyword', 'wpmkp' ),
			'edit_item' => __( 'Edit Keyword', 'wpmkp' ), 
			'update_item' => __( 'Update Keyword', 'wpmkp' ),
			'add_new_item' => __( 'Add New Keyword', 'wpmkp' ),
			'new_item_name' => __( 'New Keyword Name', 'wpmkp' ),
		  );

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'rewrite' => false,
		);

		register_taxonomy('keywords', page, $args);
	}
	
	function meta_keywords_tag() {	
		if ( is_page() ) {
			$tags = get_the_terms($post->id, 'keywords');
		} else {
			$tags = get_the_tags($post->ID);
		}
		
		if ( !empty($tags) ) {
			foreach ($tags as $tag) {
				$keywords[count($keywords)] = $tag->name;
			}
			echo '<meta name="keywords" content="'.implode(", ", $keywords).'" />'."\n";
		}
	}
	
	function add_meta_keywords_columns($columns) {  
		$new = array();  
		foreach($columns as $key => $title) {    
			if ($key=='comments') // Put the column before the $key column      
				$new['keywords'] = __('Keywords', 'wpmkp');   
			$new[$key] = $title;  
		}  
		return $new;
	}
		
	function fill_meta_keywords_columns($column_name, $id) {
		switch($column_name) {
		case 'keywords':
			$admin = get_admin_url();
			$string = "edit.php?post_type=page&";
			$tags = get_the_terms( $id, 'keywords' );
			if ( !empty( $tags ) ) {
				$out = array();
				foreach ( $tags as $tag )
					$out[] = "<a href='".$string."keywords=$tag->slug'> " . esc_html(sanitize_term_field('name', $tag->name, $tag->term_id, 'keywords', 'display')) . "</a>";
				echo join( ', ', $out );
			} else {
				_e('No Keywords');
			}
			break;
		default:
			break;
		}		 
	}
	
	} // class wp_meta_keywords_plugin
}

add_action('init', array('wp_meta_keywords_plugin','meta_keywords_textdomain'));
add_action('init', array('wp_meta_keywords_plugin','meta_keywords_init'));
add_action('wp_head', array('wp_meta_keywords_plugin','meta_keywords_tag'));
add_filter('manage_pages_columns', array('wp_meta_keywords_plugin', 'add_meta_keywords_columns'));
add_action('manage_pages_custom_column', array('wp_meta_keywords_plugin', 'fill_meta_keywords_columns'), 10, 2 );
?>