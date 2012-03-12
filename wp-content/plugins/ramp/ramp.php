<?php
/*
Plugin Name: RAMP (Review And Move to Production)
Plugin URI: http://crowdfavorite.com
Description: Deploy content between servers.
Version: 1.0.3
Author: Crowd Favorite
Author URI: http://crowdfavorite.com 
*/

// Config

	define('CF_DEPLOY_VERSION', '1.0.3');
	define('CF_DEPLOY_SETTINGS', 'cfd_settings');
	define('CF_DEPLOY_POST_TYPE', 'cf-deploy');
	define('CF_DEPLOY_BATCH_PREFIX', 'cf_deploy_batch_');
	define('CF_DEPLOY_CAPABILITIES', 'publish_posts');
	define('CF_DEPLOY_USE_COMPRESSION', true);
	
	define('RAMP_DEBUG', false); // enables TESTS menu item & dump of debug data to /tmp
	
	define('CF_ADMIN_DIR', 'ramp/lib/cf-admin/');
	
// Init

	// functionality
	require_once('classes/common.class.php');
	require_once('classes/exception.class.php');
	require_once('classes/message.class.php');
	require_once('classes/batch.class.php');
	require_once('classes/client.class.php');
	require_once('classes/deploy.class.php');
	require_once('classes/admin.class.php');
	
	// content objects
	require_once('classes/item_base.class.php');
	require_once('classes/post.class.php');
	require_once('classes/attachment.class.php');
	require_once('classes/menu.class.php');
	require_once('classes/user.class.php');
	
	function cfd_init() {
		// allow on admin pages & xml-rpc requests
		if (is_admin() || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
			$classname = 'cf_deploy_admin';
			if (defined('RAMP_DEBUG') && RAMP_DEBUG) {
				require_once('classes/admin_tests.class.php');
				$classname .= '_tests';
			}
			$GLOBALS['cfd_admin'] = new $classname;
			
			if (is_cfd_page() || (defined('DOING_AJAX') && DOING_AJAX) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
				do_action('cfd_admin_init');
			}
		}

		// customizeable title name for menus and screens
		define('CF_DEPLOY_TITLE_NAME', apply_filters('cf-deploy-title-name', 'RAMP'));

		// Register a custom post type
		register_post_type(CF_DEPLOY_POST_TYPE, array( 
			'labels' => array(
				'name' => CF_DEPLOY_TITLE_NAME.' '.__('Batch Items'),
				'singular_name' => CF_DEPLOY_TITLE_NAME.' '.__('Batch Item')
			),
			'description' => __('Deployment Mechanism'),
			'public' => false,
			'exclude_from_search' => true,
			'publically_queryable' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'hierarchal' => false,
			'can_export' => false,
			'query_var' => false,
			'supports' => array(
				'title',
				'author',
				'excerpt',
				'page-attributes',
				'custom-fields'
			),
			'taxonomies' => array(),
			'capabilities' => array(
				'manage_options'
			),
		));
		
		/**
		 * Active batch: draft
		 * Pushed batch: publish
		 * Imported batch: import
		 *
		 * This api is here, but is not fully implemented in the wp-admin.
		 * As of right now this doesn't show anywhere but is queryable.
		 * I claim no responsibility if this breaks in the future
		 */
		register_post_status('import', array(
			'label' => 'Import',
			'_edit_link' => 'admin.php?page=cf-ramp-batch&batch=%d',
			'publicly_queryable' => false,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false
		));
	}
	add_action('init', 'cfd_init');

// Libs

	/**
	 * Load in the Revision Management core
	 *
	 * @return void
	 */
	function cfd_get_libs() {
		if (!function_exists('cfr_register_metadata')) {
			include_once('lib/cf-revision-manager/cf-revision-manager.php');
		}
		if (!defined('ADMIN_UI_VERSION') && is_cfd_page()) {
			remove_action('admin_menu', 'admin_ui_plugin_menu');
			remove_action('admin_head', 'admin_ui_plugin_css');
			remove_action('admin_head', 'admin_ui_plugin_js');
		}
	}
	add_action('plugins_loaded', 'cfd_get_libs', 10);

// XMLRPC
	
	function cfd_receive($args) {
		ini_set('memory_limit', '512M');
		global $cfd_admin;
		return $cfd_admin->receive($args);
	}

	/**
	 * Add our listener to the XMLRPC methods
	 *
	 * @param array $methods 
	 * @return array
	 */
	function cfd_xmlrpc_methods($methods) {
		$methods = array_merge($methods, array(
			'cfd.receive' => 'cfd_receive'
		));
		return $methods;
	}
	add_filter('xmlrpc_methods', 'cfd_xmlrpc_methods');
	
// Utility

	function is_cfd_page() {
		if (empty($_GET) || empty($_GET['page'])) { return false; }
		return strpos($_GET['page'], 'cf-ramp') === 0;
	}
	
// 3rd Party hooks & helpers

	function cfd_register_deploy_callback($name, $description, $callbacks) {
		global $cfd_admin;
		return $cfd_admin->register_deploy_callback($name, $description, $callbacks);
	}
	
	function cfd_deregister_deploy_callback($name) {
		global $cfd_admin;
		return $cfc_admin->deregister_deploy_callback($name);
	}
	
	function cfd_get_post_guid($post) {
		if (is_object($post)) {
			return $post->guid;
		}
		
		$post = get_post($post);
		if (!empty($post)) {
			return $post->guid;
		}
	}

	function cfd_get_taxonomy_term_guid($term_id, $taxonomy) {
		$term = get_term($term_id, $taxonomy);
		return $term->slug;
	}
	
	function cfd_get_user_guid($user_id) {
		$ret = $user_id;
		$user = get_userdata($user_id);
		if (!empty($user)) {
			$ret = $user->user_login;
		}
		return $ret;
	}
	
	/**
	 * Checks to see if there are duplicate GUIDs.
	 * 
	 * @return bool
	 */
	function cfd_are_there_duplicate_guids() {
		global $wpdb;
		$query = $wpdb->prepare('
			SELECT 1
			FROM '.$wpdb->posts.'
			WHERE post_type != "revision"
			GROUP BY guid
			HAVING COUNT(guid) > 1
		');
		$guids = $wpdb->get_col($query);
		return (bool) (count($guids) > 0);
	}
	
	function cfd_get_post_by_guid($guid) {
		global $wpdb;
		$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE guid = %s', $guid);

		$post_id = $wpdb->get_var($query);

		if (!empty($post_id)) {
			return get_post($post_id);
		}
		else {
			return false;
		}
	}
	
	function cfd_get_post_id_by_guid($guid) {		
		$post_id = null;
		
		$post = cfd_get_post_by_guid($guid);
		if (!empty($post)) {
			$post_id = $post->ID;
		}

		return $post_id;
	}
	
	function cfd_get_term_id_by_guid($guid, $taxonomy) {
		$term = get_term_by('slug', $guid, $taxonomy);
		return $term->term_id;
	}
	
	function cfd_get_user_id_by_guid($guid) {
		$user = get_user_by('user_login', $guid);
		return $user->user_id;
	}
	
// Activation

	/**
	 * Throw an error on php versions that are too old so that the plugin won't activate
	 *
	 * @return void
	 */
	function cfd_activate() {
		if (version_compare(PHP_VERSION, '5.2', '<=')) {
			trigger_error(__('This plugin requires PHP version 5.2 or greater.', 'cf-deploy'));
		}
		
		global $wp_version;
		if (version_compare($wp_version, '3.1', '<=')) {
			trigger_error(__('This plugin requires WordPress version 3.1 or higher to avoid an issue with conflicting page guids. <a href="http://core.trac.wordpress.org/ticket/15041">See this Trac ticket for more information</a>', 'cf-deploy'));
		}
	}
	register_activation_hook(__FILE__, 'cfd_activate');

// Debug

	/**
	 * Funtion to write debug data to /tmp dir
	 * Function respects current DEBUG status
	 *
	 * @param string $filename 
	 * @param string $data 
	 * @param string $type 
	 * @param string $append 
	 * @return int/bool
	 */
	function cfd_tmp_dbg($filename, $data, $type = 'print', $append = false) {
		if (!defined('RAMP_DEBUG') || !RAMP_DEBUG) {
			return false;
		}
		if (!is_dir('/tmp/cfd-tmp')) {
			mkdir('/tmp/cfd-tmp');
		}
		return cfd_data_file('/tmp/cfd-tmp/'.$filename, $data, $type, $append);
	}

	/**
	 * Funtion to write data to a file in variable format
	 *
	 * @param string $filename 
	 * @param string $data 
	 * @param string $type 
	 * @param string $append 
	 * @return int/bool
	 */
	function cfd_data_file($filename, $data, $type = '', $append = false) {
		if ($type == 'print') {
			$data = print_r($data, true);
		}
		elseif ($type == 'export') {
			$data = var_export($data, true);
		}
		elseif ($type == 'dump') {
			ob_start();
			var_dump($data);
			$data = ob_get_clean();			
		}
		
		if (!is_writable(dirname($filename))) {
			return false;
		}
		
		return file_put_contents($filename, $data, ($append ? FILE_APPEND : null));		
	}
?>
