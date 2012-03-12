<?php
	define('RAMP_DEBUG_SHOW_TRANSFER_OPTIONS', true);

// Cleanout
	if (isset($_GET['page']) && $_GET['page'] == 'cf-ramp-test' && !empty($_GET['clean-history'])) {
		$deploy_posts = get_posts(array(
			'post_type' => CF_DEPLOY_POST_TYPE,
			'post_status' => array('draft', 'publish', 'import'),
			'showposts' => -1
		));

		$found = count($deploy_posts);			
		$deleted = 0;
		if (count($deploy_posts)) {
			foreach ($deploy_posts as $d_post) {
				$ret = wp_delete_post($d_post->ID, true);
				$deleted++;
			}
		}
		
		$message = 'Deleted '.$deleted.' deploy post items. <a href="'.admin_url('admin.php?page=cf-ramp-test').'">Click here to return to the test page</a>';
		wp_die($message);
		exit;
	}

// guid fix

	/**
	 * Some versions of WP < 3.1 had an issue where page guids were improperly generated. It may be necessary to fix those guids so that 
	 * the deploy process can properly identify pages that were generated on older versions of wordpress.
	 */
	if (isset($_GET['page']) &&  $_GET['page'] == 'cf-ramp-test') {
		if (!empty($_GET['fix-page-guids'])) {
			global $wpdb;
		
			$bad_guid = trailingslashit(get_bloginfo('home'));
		
			$pages = get_bad_guids();
			$found = count($pages);
			$updated = 0;
			if (!empty($pages)) {
				foreach ($pages as $page) {
					if ($r = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET guid = %s WHERE ID = %s", $bad_guid.'?page_id='.$page->ID, $page->ID))) {
						$updated++;
					}
				}
			}
			wp_die('Updated '.$updated.' of '.$found.' bad page guids. <a href="'.admin_url('admin.php?page=cf-ramp').'">Return to RAMP System</a>.');
		}
	}

	function get_bad_guids() {
		global $wpdb;
	
		$bad_guid = trailingslashit(get_bloginfo('home'));
	
		$query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid = %s", $bad_guid);
		$pages = $wpdb->get_results($query);
		return $pages;
	}

// test post type on a single side only

	/**
	 * Register a custom post type
	 * 
	 * Supplied is a "reasonable" list of defaults
	 * @see register_post_type for full list of options for register_post_type
	 * @see add_post_type_support for full descriptions of 'supports' options
	 * @see get_post_type_capabilities for full list of available fine grained capabilities that are supported
	 */
	/*
	register_post_type('test_post_type', array( 
		'labels' => array(
			'name' => __('Test Types'),
			'singular_name' => __('test-type')
		),
		'description' => __('Post type for testing cf_deploy&rsquo;s handling of custom post types'),
		'public' => true,
		'exclude_from_search' => null,
		'publically_queryable' => null,
		'show_ui' => true,
		'show_in_nav_menus' => null,
		'hierarchal' => false,
		'supports' => array(
			'title',
			'editor',
			'comments',
			'revisions',
			'trackbacks',
			'author',
			'excerpt',
			'page-attributes',
			'thumbnail',
			'custom-fields'
		),
		'taxonomies' => array(
			'post_tag',
			'category',
		),
		'capability_type' => 'post',
		'capabilities' => array(
			// for fine grained control include valid capabilities here
			// if left empty 'capability_type' will define editing capability requirements
		),
	));
	*/
	
// Admin class extension

class cf_deploy_admin_tests extends cf_deploy_admin {
	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add our test JS @init
	 *
	 * @return void
	 */
	public function admin_init() {
		parent::admin_init();
		if (is_cfd_page()) {
			wp_enqueue_script('cfd_tests_js', $this->baseurl.'/js/tests.js', array('cfd_admin_js'), CF_DEPLOY_VERSION);
			wp_enqueue_style('cfd_tests_css', $this->baseurl.'/css/tests.css', array('cfd_admin_css'), CF_DEPLOY_VERSION, 'all');
		}
	}
	
	/**
	 * Add our test page to the menu
	 *
	 * @return void
	 */
	public function admin_menu() {
		parent::admin_menu();
		add_submenu_page('cf-ramp', __('TESTS'), __('TESTS'), 'manage_options', 'cf-ramp-test', array($this, 'admin_test_page'));
	}

// Data Import

	public function test_import($args) {
		try {
			switch ($args['import_type']) {
				case 'post-type':
					$method = 'import_post_types';
					$data = $args['post_types'];
					break;
				case 'user':
					$method = 'import_users';
					$data = $args['users'];
					break;
				case 'taxonomy':
					$method = 'import_taxonomies';
					$data = $args['taxonomies'];
					break;
				case 'bookmark':
					$method = 'import_bookmarks';
					$data = $args['bookmarks'];
					break;
				case 'menu':
					$method = 'import_menus';
					$data = $args['menus'];
					break;
			}
			
			if (!empty($method) && method_exists($this, $method)) {
				$result = $this->$method($data);
				if ($result['success'] == true) {
					$ret = new cfd_message(array(
						'success' => true,
						'type' => $method.'-success',
						'message' => $result['message']
					));
				}
				else {
					$ret = new cfd_message(array(
						'success' => false,
						'type' => 'insert-user-failed',
						'message' => (!empty($result['message']) ? $result['message'] : __('no message returned by method.', 'cf-deploy'))
					));
				}
			}
			elseif (empty($method)) {
				$ret = new cfd_message(array(
					'success' => false,
					'type' => 'invalid-object-type',
					'message' => 'Import type "'.esc_html($args['import_type']).'" not recognized.'
				));
			}
			elseif (!method_exists($this,$method)) {
				$ret = new cfd_message(array(
					'success' => false,
					'type' => 'invalid-method',
					'message' => 'Invalid import method "'.$method.'" selected.'
				));				
			}
		}
		catch (Exception $e) {
			// in the rare event that there's something to catch
			$ret = new cfd_message(array(
				'success' => false,
				'type' => 'insert-'.$args['import_type'].'-failed',
				'message' => $e->getMessage()
			));
		}

		return $ret;
	}

// Ajax

	/**
	 * Gateway function for Testing Comms
	 *
	 * @param array $args 
	 * @return object cfd_message
	 */
	public function ajax_test_comms($args) {		
		$server = $args['cfd_settings']['remote_server'][0]['address'];
		$auth_key = $args['cfd_settings']['remote_server'][0]['key'];

		$method = 'ajax_test_'.$args['test_action'];
		if ($method == 'ajax_test_say_hello' && method_exists($this, $method)) {
			// simple "hello world" comms test
			$ret = $this->$method($server, $auth_key, $args);
			if (empty($ret) || !($ret instanceof cfd_message)) {
				$ret = new cfd_message(array(
					'success' => false,
					'type' => 'unknown-return-value',
					'message' => '<div class="error"><p>Unexpected return value from "'.esc_html($args['test_action']).'"</p></div>'
				));
			}
			else {
				return $ret;
			}
		}
		elseif (method_exists($this, $method)) {
			// batch data testing
			$batch_data = $this->$method($args, array());
			if ($batch_data instanceof cfd_message) {
				return $batch_data;
			}
			
			$batch = new cfd_batch(array(
				'ID' => 0, // batch ID of 0 tells batch to use included 'data' param
				'data' => $batch_data
			));
			
			$params = array(
				'server' => $server,
				'auth_key' => $auth_key,
				'method' => 'import_batch',
				'args' => array(
					'batch' => $batch->get_deploy_data()
				)
			);

			$ret = $this->send($params);
			if (is_array($ret->message)) {
				$post_type_messages = $ret->message;
				$ret->message = '';
				foreach ($post_type_messages as $post_type => $messages) {
					$ret->message .= $this->parse_message_response($messages, $post_type);
				}
			}
			
			if ($ret->success) {
				$ret->message = '<div class="success message"><h3>Import Successful</h3></div>'.$ret->message;
			}
			else {
				$ret->message = '<div class="error message"><h3>Import Failed</h3></div>'.$ret->message;
			}
		
			if (empty($ret) || !($ret instanceof cfd_message)) {
				$ret = new cfd_message(array(
					'success' => false,
					'type' => 'unknown-return-value',
					'message' => '<div class="error message"><p>Unexpected return value from "'.esc_html($args['test_action']).'"</p></div>'
				));
			}
		}
		else {
			$ret = new cfd_message(array(
				'success' => false,
				'type' => 'bad-method',
				'message' => '<div class="error message"><p>Method "'.esc_html($args['test_action']).'" does not exist</p></div>'
			));
		}

		return $ret;
	}
	
	public function mimic_batch_data_format($array) {
		$cfd_common = new cfd_common();
		array_walk_recursive($array, array($cfd_common, 'object_to_array'));
		array_walk_recursive($array, array($cfd_common, 'trim_scalar'));
		return $array;
	}

// Simple Comms test
	
	/**
	 * Simple hello communications test
	 *
	 * @param string $server 
	 * @param string $auth_key 
	 * @param array $args 
	 * @return object cfd_message
	 */
	protected function ajax_test_say_hello($server, $auth_key, $args) {
		$params = array(
			'server' => $server,
			'auth_key' => $auth_key,
			'method' => 'hello',
			'args' => array(
				'name' => 'bob'
			)
		);

		$ret = $this->send($params);
		
		return new cfd_message(array(
			'success' => $ret->success,
			'type' => 'testing-comms',
			'message' => $ret->message
		));
	}

// Test import data fetchers
	
	/**
	 * Add a post_type id to test batch_items array
	 *
	 * @param array $args 
	 * @param array $batch_array 
	 * @return array
	 */
	protected function ajax_test_send_post($args, $batch_array) {
		# validate params
		if (empty($args['test_action_send_post_id'])) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'empty-post-id',
				'message' => '<div class="error"><p>Post ID is empty. Please enter a Post ID to continue.</p></div>'
			));
		}
		elseif (!is_numeric($args['test_action_send_post_id'])) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'non-numeric-post-id',
				'message' => '<div class="error"><p>Invalid Post ID. Post ID must be numeric.</p></div>'
			));
			
		}
		
		# try to get a post to transfer
		$post_id = intval($args['test_action_send_post_id']);
		$post = new cfd_post(intval($args['test_action_send_post_id']));
				
		# validate returned post
		if (!$post) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'invalid-post-id',
				'message' => '<div class="error"><p>Invalid Post ID. Post ID '.$post_id.' does not exist.</p></div>'
			));
		}
		elseif($post instanceof WP_Error) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'unknown-error',
				'message' => '<div class="error"><p>Error ('.$post->get_error_code().'): '.$post->get_error_message().'</p></div>'
			));
		}
		
		$batch_array['post_types'][$post->post->post_type][] = $post->id();
		return $batch_array;
	}
	
	/**
	 * Add a category id to test batch_items array
	 *
	 * @param array $args 
	 * @param array $batch_array 
	 * @return array
	 */
	protected function ajax_test_send_category($args, $batch_array) {
		$cat_slug = esc_attr($args['test_action_send_category_id']);
		$category = get_term_by('slug', $cat_slug, 'category');

		if (empty($category)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'invalid-taxonomy-object',
				'message' => '<div class="error"><p>Error: Invalid term "'.$cat_slug.'"</p></div>'
			));
		}
		
		$batch_array['taxonomies'][$category->taxonomy][] = $category->term_id;
		return $batch_array;
	}

	/**
	 * Add a bookmark id to test batch_items array
	 *
	 * @param array $args 
	 * @param array $batch_array 
	 * @return array
	 */
	protected function ajax_test_send_bookmark($args, $batch_array) {
		$bookmark_id = intval($args['test_action_send_bookmark_id']);
		if (empty($bookmark_id)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'empty-bookmark-id',
				'message' => '<div class="error"><p>Empty Bookmark Id. WTF?</p></div>'
			));
		}
		
		$bookmark = get_bookmark($bookmark_id);
		if (empty($bookmark)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'invalid-bookmark-id',
				'message' => '<div class="error"><p>Invalid bookmark id. WTF?</p></div>'
			));
		}

		$batch_items['bookmarks'][] = $bookmark->link_id;
		return $batch_items;
	}
	
	/**
	 * Add a user id to test batch_items array
	 *
	 * @param array $args 
	 * @param array $batch_array 
	 * @return array
	 */
	protected function ajax_test_send_user($args, $batch_items) {
		$user_id = intval($args['test_action_send_user_id']);
		if (empty($user_id)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'empty-user-id',
				'message' => '<div class="error"><p>Empty user id. How did you manage that?</p></div>'
			));
		}

		$user = new WP_User($user_id);
		if (empty($user)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'invalid-user',
				'message' => '<div class="error"><p>Invalid user. Huh?</p></div>'
			));
		}
		
		$batch_items['users'][] = $user->ID;
		return $batch_items;
	}
	
	/**
	 * Add a menu id to test batch_items array
	 *
	 * @param array $args 
	 * @param array $batch_array 
	 * @return array
	 */
	protected function ajax_test_send_menu($args, $batch_items) {
		$menu_id = esc_attr($args['test_action_send_menu_id']);
		try {
			$menu = new cfd_menu($menu_id);
		}
		catch (Exception $e) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'invalid-menu-id',
				'message' => '<div class="error"><p>Invalid Menu ID. Menu ID '.$menu_id.' does not exist.</p></div>'
			));
		}

		$batch_items['menus'][] = $menu->id();
		return $batch_items;
	}
	
// Pages
	
	public function admin_test_page() {
		$bad_guids = get_bad_guids();

		echo $this->admin_wrapper_open('Test');
		echo '
			<div id="cf-header" class="cf-clearfix">
				<ul id="cf-nav">
					<li><a href="#cf-deploy-server-comms-test">Test Comms</a></li>
					<li><a href="#cf-deploy-sample-data-test">Sample Data</a></li>';
		if (!empty($bad_guids)) {
			echo '
					<li><a href="#cf-deploy-server-fix-guids">Fix Bad Guids</a></li>';
		}
		echo '
				</ul>
			</div>
			<div id="cf-deploy-debug-tab-content">
			';
		echo $this->sample_data_form();
		echo $this->test_communications_form();
		echo '
				<div id="cf-deploy-server-fix-guids">
					<p>Due to a bug in current versions of WordPress bad page guids can be created. A patch should be out for WP 3.0.2, but in the mean time if this tab shows up your DB need repair.</p>
					<p>'.count($bad_guids).' bad page guid(s) found. <a href="'.admin_url('admin.php?page=cf-deploy-test&fix-page-guids=1').'">Fix bad guids</a>.</p>
				</div>';
		echo '
			</div><!-- #cf-tab-content-->';
		echo $this->admin_wrapper_close();
	}
	
	public function test_communications_form() {
		# address
		if (!empty($_POST[CF_DEPLOY_SETTINGS]['remote_server'][0]['address'])) {
			$server_0_address = $_POST[CF_DEPLOY_SETTINGS]['remote_server'][0]['address'];
		}
		else {
			$server_0_address = !empty($this->options['remote_server'][0]['address']) ? esc_attr($this->options['remote_server'][0]['address']) : null;
		}
		
		# key
		if (!empty($_POST[CF_DEPLOY_SETTINGS]['remote_server'][0]['key'])) {
			$server_0_key = $_POST[CF_DEPLOY_SETTINGS]['remote_server'][0]['key'];
		}
		else {
			$server_0_key = !empty($this->options['remote_server'][0]['key']) ? esc_attr($this->options['remote_server'][0]['key']) : null;
		}

		# test action
		$test_action = 'say_hello';
		$test_action_post_id = '';
		if (!empty($_POST['test_action'])) {
			$test_action = $_POST['test_action'];
			switch (true) {
				case $test_action == 'send_post':
					if (!empty($_POST['test_action_send_post_id'])) {
						$test_action_post_id = intval($_POST['test_action_send_post_id']);
					}
					break;
				case $test_action == 'send_menu':
					if (!empty($_POST['test_action_send_menu_id'])) {
						$test_action_menu_id = esc_attr($_POST['test_action_send_menu_id']);
					}
					break;
				case $test_action == 'send_category':
					if (!empty($_POST['test_action_send_category_id'])) {
						$test_action_category_id = esc_attr($_POST['test_action_send_category_id']);
					}
				case $test_action == 'send_user':
					if (!empty($_POST['test_action_user_id'])) {
						$test_action_user_id = esc_attr($_POST['test_action_user_id']);
					}
				case $test_action == 'send_bookmark':
					if (!empty($_POST['test_action_bookmark_id'])) {
						$test_action_user_id = intval($_POST['test_action_bookmark_id']);
					}
					break;
				case $test_action == 'say_hello':
				default:
					break;
			}
		}
		
		echo '
			<div id="cf-deploy-server-comms-test">
				<h3>Test Server Communications</h3>
				<form id="cf_deploy_test_comms" name="cf_deploy_test_comms" class="cf-form" method="post">
					<fieldset class="cf-lbl-pos-left">
						<p class="cf-elm-help">'.__('Settings to allow this server to push content to a remote server', 'cf-deploy').'</p>
						<div class="cf-elm-block cf-elm-width-full">
							<label for="'.CF_DEPLOY_SETTINGS.'_remote_server_0_address">'.__('Remote Server Address', 'cf-deploy').'</label>
							<input class="cf-elm-text" id="'.CF_DEPLOY_SETTINGS.'_remote_server_0_address" name="'.CF_DEPLOY_SETTINGS.'[remote_server][0][address]" value="'.$server_0_address.'" />
						</div>
						<div class="cf-elm-block cf-elm-width-full">
							<label for="'.CF_DEPLOY_SETTINGS.'_remote_server_0_key">'.__('Remote Server Auth Key', 'cf-deploy').'</label>
							<input class="cf-elm-text" id="'.CF_DEPLOY_SETTINGS.'_remote_server_0_key" name="'.CF_DEPLOY_SETTINGS.'[remote_server][0][key]" value="'.$server_0_key.'" />
						</div>
					</fieldset>
					<fieldset class="cf-lbl-pos-left test-actions">
						<legend>Action</legend>
						
						<!-- Test -->
						<div class="cf-elm-block cf-has-radio">
							<input class="cf-elm-radio has-text-companion" type="radio" name="test_action" id="test_action_hello" value="say_hello" '.checked('say_hello', $test_action, false).'/>
							<label class="cf-lbl-radio">Say Hello</label>
						</div>';
		
		// I want to be able to hide these while in development
		if (defined('RAMP_DEBUG_SHOW_TRANSFER_OPTIONS') && RAMP_DEBUG_SHOW_TRANSFER_OPTIONS) {
			echo '
			
							<!-- Post/Page -->
							<div class="cf-elm-block cf-has-radio">
								<input class="cf-elm-radio has-companion" type="radio" name="test_action" id="test_action_send_post" value="send_post" '.checked('send_post', $test_action, false).'/>
								<label class="cf-lbl-radio" for="test_action_send_post">Send Post/Page</label> &nbsp; 
								<label class="cf-lbl-radio" for="test_action_send_post_id">post_id: </label> 
								<input class="inp-companion" type="text" name="test_action_send_post_id" id="test_action_send_post_id" value="'.$test_action_post_id.'" '.($test_action != 'send_post' ? 'disabled="disabled"' : '').'/>
							</div>
						
							<!-- Menus -->
							<div class="cf-elm-block cf-has-radio">
								<input class="cf-elm-radio has-companion" type="radio" name="test_action" id="test_action_send_menu" value="send_menu" '.checked('send_menu', $test_action, false).'/>
								<label class="cf-lbl-radio" for="test_action_send_menu">Send Menu</label> &nbsp; 
								<label class="cf-lbl-radio" for="test_action_send_menu_id">menu_id: </label>
								<select class="inp-companion" name="test_action_send_menu_id" id="test_action_send_menu_id" '.($test_action != 'send_menu' ? 'disabled="disabled"' : '').'>';
			$menus = wp_get_nav_menus(array(
				'hide_empty' => false
			));
			if (count($menus)) {
				foreach ($menus as $menu) {
					echo '
									<option value="'.esc_attr($menu->slug).'"'.selected($test_action_menu_id, esc_attr($menu->slug), false).'>'.$menu->name.' &nbsp; </option>';
				}
			}
			echo '
								</select>
							</div>
						
							<!-- Categories -->
							<div class="cf-elm-block cf-has-radio">
								<input class="cf-elm-radio has-companion" type="radio" name="test_action" id="test_action_send_category" value="send_category" '.checked('send_menu', $test_action, false).'/>
								<label class="cf-lbl-radio" for="test_action_send_category">Send Category</label> &nbsp; 
								<label class="cf-lbl-radio" for="test_action_send_category_id">Category: </label>
								<select class="inp-companion" name="test_action_send_category_id" id="test_action_send_category_id" '.($test_action != 'send_category' ? 'disabled="disabled"' : '').'>';
			$categories = get_terms('category', array(
				'hide_empty' => false
			));
			foreach ($categories as $cat) {
				if (!empty($cat->parent)) {
					$parent = get_term($cat->parent, $cat->taxonomy);
					$cat->parent = $parent;
				}
				echo '
									<option value="'.$cat->slug.'"'.selected($test_action_taxonomy_id, esc_attr($cat->slug), false).'>'.$cat->name.' ('.$cat->count.' posts) '.(!empty($cat->parent) ? ' parent: '.$cat->parent->name : '').' </option>';
			}
			echo '
								</select>
							</div>
						
							<!-- Users -->
							<div class="cf-elm-block cf-has-radio">
								<input class="cf-elm-radio has-companion" type="radio" name="test_action" id="test_action_send_user" value="send_user" '.checked('send_user', $test_action, false).'/>
								<label class="cf-lbl-radio" for="test_action_send_user">Send User</label> &nbsp; 
								<label class="cf-lbl-radio" for="test_action_send_user_id">User: </label>
								<select class="inp-companion" name="test_action_send_user_id" id="test_action_send_user_id" '.($test_action != 'send_user' ? 'disabled="disabled"' : '').'>';
			global $wpdb;
			$users = $wpdb->get_results($wpdb->prepare('SELECT '.$wpdb->users.'.* FROM '.$wpdb->users.' ORDER BY `user_nicename` ASC'));
			if (!empty($users)) {
				foreach ($users as &$user) {
					$user = new WP_User($user->ID);
					if ($user->has_cap('edit_posts')) {
						echo '
									<option value="'.$user->ID.'"'.selected($test_action_user_id, $user->ID, false).'>'.$user->display_name.' ('.implode(', ', $user->roles).')</option>';
					}
				}
			}
			echo '
								</select>
							</div>
							
							<!-- Bookmarks -->
							<div  class="cf-elm-block cf-has-radio">
								<input class="cf-elm-radio has-companion" type="radio" name="test_action" id="test_action_send_bookmark" value="send_bookmark" '.checked('send_bookmark', $test_action, false).'/>
								<label class="cf-lbl-radio" for="test_action_send_bookmark">Send Link</label> &nbsp; 
								<label class="cf-lbl-radio" for="test_action_send_bookmark_id">Link: </label>
								<select class="inp-companion" name="test_action_send_bookmark_id" id="test_action_send_bookmark_id" '.($test_action != 'send_bookmark' ? 'disabled="disabled"' : '').'>';
			$bookmarks = get_bookmarks($args);
			foreach ($bookmarks as $bookmark) {
				echo '
									<option value="'.$bookmark->link_id.'">'.$bookmark->link_name.'</option>';
			}			
			echo '
								</select>
							</div><!-- bookmarks/links -->';
							
		}			
		echo '
					</fieldset>
					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="Test Server Comms" />
					</p>
				</form>
				<div id="cf_deploy_test_comms_results"></div>
			</div>';
	}
	
	public function sample_data_form() {
		if (!empty($_POST['cfd_page_id']) || !empty($_POST['cfd_post_id'])) {
			$post_id = !empty($_POST['cfd_page_id']) ? intval($_POST['cfd_page_id']) : (!empty($_POST['cfd_post_id']) ? intval($_POST['cfd_post_id']) : null);
			$post = $this->get_full_post_data($post_id);
			if (!empty($post) && !is_wp_error($post)) {			
				switch ($_POST['formatting']) {
					case 'serialized':
						$post = serialize($post);
						break;
					default:
						$post = print_r($post, true);
						break;
				}
				$return_output = '<textarea id="cf-deploy-sample-post" class="cf-deploy-sample-post">'.htmlspecialchars($post, ENT_QUOTES, 'UTF-8').'</textarea>';
			}
			else {
				$return_output = '<div class="error inline" style="margin: 0 0 1.5em 0; padding: 5px; display: block;">Error: '.implode(', ', $post->get_error_messages()).'</div>';
			}
		}
		elseif (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && empty($_POST['cfd_page_id']) && empty($_POST['cfd_post_id'])) {
			$return_output = '<div class="error inline" style="margin: 0 0 1.5em 0; padding: 5px; display: block;">Error: no post or page id selected.</div>';
			$form_error = true;
		}
		
		// sample data
		$list_args = array(
			'echo' => false,
			'name' => 'cfd_page_id',
			'number' => 100,
			'show_option_none' => 'None',
			'selected' => (!empty($_POST['cfd_page_id']) ? intval($_POST['cfd_page_id']) : 0)
		);
		$post_id = (!empty($_POST['cfd_post_id']) ? intval($_POST['cfd_post_id']) : null);
		echo '
			<div id="cf-deploy-sample-data-test">
				<h3>Display Sample Page Object</h3>
					<form name="cf_deploy_sample_post_array" class="cf-form" method="post">
						<fieldset>
							<div class="cf-elm-block'.(!empty($form_error) && $form_error ? ' cf-error' : '').'">
								page: '.wp_dropdown_pages($list_args).' or post_id: <input type="text" name="cfd_post_id" value="'.$post_id.'" />';
		$selected_formatting = !empty($_POST['formatting']) ? esc_attr($_POST['formatting']) : 'none';
		foreach (array('none', 'serialized') as $formatting) {
			echo ' &nbsp; <label>'.$formatting.' <input type="radio" name="formatting" value="'.$formatting.'" '.checked($selected_formatting, $formatting, false).'/></label>';
		}		
		echo ' &nbsp; <input type="submit" name="Submit" class="button-primary" value="Show Data" />
							</div>
						</fieldset>
					</form>';
		echo $return_output;
		echo '
			</div><!-- #cf-deploy-sample-data-test -->';
	}
}

?>