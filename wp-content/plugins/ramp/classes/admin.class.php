<?php

class cf_deploy_admin extends cf_deploy {	
	protected $settings_key = 'cf-deploy-settings';
	protected $deploy_batch_action_base = 'cf-deploy-batch-action';
	
	protected $admin_messages; 
	
	protected $baseurl;
	protected $basedir;
	
	public function __construct() {
		global $pagenow;
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_head', array($this, 'prune_admin_menu'));
		add_action('wp_ajax_cfd_ajax', array($this, 'ajax_handler'), 11);				
		add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
		parent::__construct();
		
		if (!empty($_POST['cf_deploy_action'])) {
			$this->handle_requests();
		}
		
		$this->baseurl = trailingslashit(WP_PLUGIN_URL).'ramp';
		$this->basedir = trailingslashit(WP_PLUGIN_DIR).'ramp';
	}
	
	public function admin_init() {
		register_setting($this->settings_key, CF_DEPLOY_SETTINGS, array($this, 'validate_settings'));
		if (is_cfd_page()) {
			include($this->basedir.'/lib/cf-admin/cf-admin.php');
			wp_enqueue_style('cfd_admin_css', $this->baseurl.'/css/admin.css', array(), CF_DEPLOY_VERSION, 'all');
			CF_Admin::load_css();
// 			wp_enqueue_style('cf_styles', $this->baseurl.'/lib/admin-ui/css/styles.css', array(), CF_DEPLOY_VERSION, 'all');
// 			wp_enqueue_style('cf_form_elements', $this->baseurl.'/lib/admin-ui/css/form-elements.css', array(), CF_DEPLOY_VERSION, 'all');
// 			wp_enqueue_style('cf_utility', $this->baseurl.'/lib/admin-ui/css/utility.css', array(), CF_DEPLOY_VERSION, 'all');
			if ($_GET['page'] == 'cf-ramp-batch-send') {
				wp_enqueue_script('cfd_admin_ajax_queue', $this->baseurl.'/js/ajaxQueue.js', array('jquery'), CF_DEPLOY_VERSION);
			}
			wp_enqueue_script('cfd_admin_js', $this->baseurl.'/js/admin.js', array('jquery'), CF_DEPLOY_VERSION);
			wp_localize_script('cfd_admin_js', 'cfd_admin_settings', array(
				'rollback_confirm' => __('Are you sure you want to rollback this import? This action cannot be undone.', 'cf-deploy'),
				'batch_delete_confirm' => __('Are you sure you want to delete this batch? This cannot be undone.', 'cf-deploy'),
				'new_key_fail' => __('New key generation failed.', 'cf-deploy'),
				'invalid_ajax_response' => __('Invalid response from server during Ajax Request', 'cf-deploy'),
				'ajax_parse_error' => __('Parse Error in data returned from server', 'cf-deploy'),
				'loading' => __('loading&hellip;', 'cf-deploy'),
				'toggle' => __('toggle', 'cf-deploy')
			));
		}
	}
	
	public function admin_menu() {
		global $menu;
		$menu[39] = array( '', 'read', 'separator-ramp', '', 'wp-menu-separator' );
		$system_privs = apply_filters('ramp-user-access-base', 'manage_options');
		$cf_menu_icon = plugins_url('/ramp/img/ramp-menu-icon.png');
		add_menu_page(CF_DEPLOY_TITLE_NAME, __('RAMP', 'cf-deploy'), $system_privs, 'cf-ramp', array($this, 'admin_home_page'), $cf_menu_icon, 40);		
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('Batch'), __('Deploy Batch'), $system_privs, 'cf-ramp-batch', array($this, 'admin_batch_page')); 							# hidden
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('Batch History'), __('Batch Export History'), $system_privs, 'cf-ramp-batch-history', array($this, 'admin_batch_history_page')); # hidden
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('Preflight'), __('Deploy Preflight'), $system_privs, 'cf-ramp-preflight', array($this, 'admin_preflight_page'));			# hidden
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('Batch Send'), __('Deploy Batch Send'), $system_privs, 'cf-ramp-batch-send', array($this, 'admin_batch_send_page'));		# hidden
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('History'), __('History'), $system_privs, 'cf-ramp-history', array($this, 'admin_history_page'));
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('Settings'), __('Settings'), $system_privs, 'cf-ramp-settings', array($this, 'admin_settings_page'));
		add_submenu_page('cf-ramp', CF_DEPLOY_TITLE_NAME.' '.__('Documentation'), __('Help'), $system_privs, 'cf-ramp-help', array($this, 'admin_docs_page'));
	}
	
	/**
	 * Adds a "Settings" link to the plugin listing page
	 *
	 * @param array $links 
	 * @param string $file 
	 * @return array 
	 */
	function plugin_action_links($links, $file) {
		if (basename($file) == 'ramp.php') {
			$settings_link = '<a href="'.admin_url('admin.php?page=cf-ramp-settings').'">'.__('Settings').'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}
	
	/**
	 * Prune the plain batch screen from the Admin menu
	 * Runs @action 'in_admin_header' so that menu can be registered and handled, 
	 * but can then be safely removed from the sidebar menu
	 *
	 * @return void
	 */
	public function prune_admin_menu() {
		$hide_menus = apply_filters('cf-deploy-hide-menus', array(
			'cf-ramp-batch',
			'cf-ramp-preflight',
			'cf-ramp-batch-send',
			'cf-ramp-batch-history'
		));
		foreach ($hide_menus as $page) {
			remove_submenu_page('cf-ramp', $page);
		}
	}
	
// Admin Messages

	public function add_admin_message($type, $message) {
		$this->admin_messages[$type][] = $message;
	}
	
	public function show_admin_messages() {
		if (!empty($_GET['batch_deleted'])) {
			$this->add_admin_message('__notice__', __('Batch successfully deleted', 'cf-deploy'));
		}

		if (!empty($this->admin_messages['__notice__'])) {
			echo '
				<div class="notice">';
			foreach($this->admin_messages['__notice__'] as $message) {
				echo '
					<p>'.$message.'</p>';
			}
			echo '
				</div>';
		}
		if (!empty($this->admin_messages['__error__'])) {
			echo '
				<div class="error">';
			foreach($this->admin_messages['__error__'] as $message) {
				echo '
					<p>'.$message.'</p>';
			}
			echo '
				</div>';			
		}
		if (!empty($this->admin_messages['__important__'])) {
			foreach($this->admin_messages['__important__'] as $message) {
				echo '
					<div class="important">'.$message.'</div>';
			}
		}
		if (!empty($this->admin_messages['__unformatted__'])) {
			foreach($this->admin_messages['__unformatted__'] as $message) {
				echo $message . ' ';
			}
		}
		
	}
// Configuration testing utility
	/**
	 * Is RAMP configured sufficiently to try to send a batch?
	 * Adds an admin message if not, by default.
	 *
	 * @param bool $quiet Suppress adding the admin message
	 * @return bool
	 */
	protected function sufficiently_configured($quiet = false) {
		$status = !empty($this->options['auth_key']) || !(empty($this->options['remote_server'][0]['address']) || empty($this->options['remote_server'][0]['key']));
		if (!$status && !$quiet) {
			$this->add_admin_message('__important__', __('An auth key or a remote server should be configured in the <a href="' . admin_url("admin.php?page=cf-ramp-settings") . '">RAMP Settings</a> to continue.', 'cf-deploy'));
		}
		return $status;
	}

// Requests

	protected function handle_requests() {
		if ($_POST['cf_deploy_action'] == 'save-batch') {
			switch (true) {
				case !empty($_POST['cf_deploy_preflight_batch']):
					$preflight = true;
				case !empty($_POST['cf_deploy_save_batch']):
					$post_id = $this->save_batch();
					if (empty($post_id)) {
						wp_die(__('error saving the batch post', 'cf-deploy')); // @TODO make this more fantastic				
					}
					
					if (!empty($preflight)) {
						# wp_nonce_url always assumes it's gonna be used for display, so undo it's doing on ampersands
						wp_redirect(str_replace('&amp;', '&', wp_nonce_url(admin_url('admin.php?page=cf-ramp-preflight&batch='.$post_id), $this->deploy_batch_action_base)));
						exit;
					}
					else { 
						wp_redirect(admin_url('admin.php?page=cf-ramp-batch&batch='.$post_id.'&updated=1'));
						exit;
					}
					break;
				case !empty($_POST['cf_deploy_delete_batch']):
					$ret = $this->delete_batch();
					if ($ret === true) {
						wp_redirect(admin_url('admin.php?page=cf-ramp&batch_deleted='.$_POST['batch_ID']));
						exit;
					}
					else {
						$this->add_admin_message('__error__', __('Batch delete failed', 'cf-deploy'));
					}
					break;
			}
		}
		elseif ($_POST['cf_deploy_action'] == 'send-batch') {
			switch (true) {
				case !empty($_POST['batch_preflight_refresh']):
					// do nothing, a page refresh is all that's needed
					break;
				case !empty($_POST['batch_preflight_submit']):
					# wp_nonce_url always assumes it's gonna be used for display, so undo it's doing on ampersands
					wp_redirect(str_replace('&amp;', '&', wp_nonce_url(admin_url('admin.php?page=cf-ramp-batch-send&batch='.intval($_REQUEST['batch'])), $this->deploy_batch_action_base)));
					exit;
					break;
			}
		}	
	}
	
// Pages
		
	/**
	 * Admin home page
	 * Show active batches & new batch button
	 *
	 * @return void
	 */
	public function admin_home_page() {
		$this->sufficiently_configured();
		
		$batches = $this->get_batches(array('status' => 'active'));

		echo $this->admin_wrapper_open('Batches <a href="'.esc_attr($this->batch_new_url()).'" class="button add-new-h2">'.
			__('New Batch', 'cf-deploy').'</a>');
		$this->show_admin_messages();
		echo '<p>A list of all batches that have not been sent. <a href="'.admin_url().'admin.php?page=cf-ramp-history">View history</a> of sent and received batches.</p>';
		echo $this->batch_table($batches);
		echo $this->admin_wrapper_close();
	}
	
	public function admin_docs_page() {
		echo $this->admin_wrapper_open('Help / Resources');

		echo '
			<div class="cf-clearfix">
				<div class="cf-help-box"> 
					<h3><a href="http://crowdfavorite.com/wordpress/ramp/docs/">Documentation</a></h3> 
					<p>Installation and usage tutorials, FAQs created from real user questions, tips and suggestions, and more...</p> 
				</div> 
				<div class="cf-help-box"> 
					<h3><a href="http://crowdfavorite.com/forums/forum/ramp">Forums</a></h3> 
					<p>Have a question or issue that you can\'t find an answer for in the documentation or FAQ? Visit the RAMP Forum page to swap ideas, share experiences and get help from other RAMP users.</p> 
				</div> 
			</div><!-- .cf-clearfix -->
			<hr />
		';
		echo '<p class="cf-txt-right"><a href="http://crowdfavorite.com"><img src="'.plugins_url('/ramp/img/dev-by-crowdfavorite.png').'" class="dev-by-cf"/></a></p>';
		echo $this->admin_wrapper_close();
	}
	
	function admin_history_page() {
		$batches = $this->get_batches(array(
			'status' => 'history',
			'posts_per_page' => 25
		));
		
		echo $this->admin_wrapper_open('History');
		if (!empty($_GET['rollback_success'])) {
			echo '
				<div class="message success"><p>'.__('Rollback Successful', 'cf-deploy').'</p></div>';
		}
		echo '<p>A history of sent and received batches. Rollbacks can be performed on the most recently received batches.</p>';
		echo '<h3>'.__('Batches Sent', 'cf-deploy').'</h3>';
		echo $this->batch_table($batches);
		$this->admin_import_history_page();
		echo $this->admin_wrapper_close();
	}
	
	/**
	 * Settings Page
	 * Show configurable settings
	 *
	 * NOTE: Due to the duplicate GUID issue that can exist in some older versions of WP, we 
	 * need to make sure there's no duplicate guids before we allow RAMP settings to be set 
	 * at all.
	 * 
	 * @return void
	 */
	public function admin_settings_page() {
		$auth_key = !empty($this->options['auth_key']) ? esc_attr($this->options['auth_key']) : null;
		$server_0_address = !empty($this->options['remote_server'][0]['address']) ? esc_attr($this->options['remote_server'][0]['address']) : null;
		$server_0_key = !empty($this->options['remote_server'][0]['key']) ? esc_attr($this->options['remote_server'][0]['key']) : null;
		
		echo $this->admin_wrapper_open('Settings');
		
		/* If there's duplicate GUIDs, add an admin message */
		$duplicate_guids = cfd_are_there_duplicate_guids();
		if ($duplicate_guids) {
			$this->add_admin_message(
				'__important__', 
				__(sprintf('<h3 class="title">Oh Snap! Your installation of WordPress has duplicate GUIDs!</h3><p>Please download and install our <a href="%s" title="CF GUID-Fix">CF GUID-Fix</a> plugin, it&rsquo;s fast and easy to run.  After running the CF GUID-Fix plugin you can come back and finish configuring your RAMP Settings.</p><a class="button-primary" href="%s">Download GUID-Fix</a>', 
				'http://crowdfavorite.com/wordpress/plugins/cf-guid-fix/',
				'http://crowdfavorite.com/wordpress/plugins/cf-guid-fix/'
			)));
		}

		$this->show_admin_messages();
		
		/* If there's duplicate GUIDs, show a message, and 
		don't finish the output of the settings page */
		if ($duplicate_guids) { return; }
		
		echo '
			<form method="post" action="options.php" name="cfd_settings" id="cfd_settings" class="cf-form">';
		settings_fields($this->settings_key);
		
		# this server
		echo '
				<div class="form-section">
					<fieldset class="cf-lbl-pos-left">
						<legend>'.__('This Server', 'cf-deploy').'</legend>
						<p class="cf-elm-help">'.__('Settings to allow another server to push content to this server.', 'cf-deploy').'</p>
						<div class="cf-elm-block cf-elm-width-full">
							<label>'.__('Address', 'cf-deploy').'</label>
							<input class="cf-elm-text" value="'.trailingslashit(get_bloginfo('wpurl')).'" readonly />
						</div>
						<div class="cf-elm-block cf-elm-width-full">
							<label for="'.CF_DEPLOY_SETTINGS.'_auth_key">'.__('Auth Key', 'cf-deploy').'</label>
							<input class="cf-elm-text" id="'.CF_DEPLOY_SETTINGS.'_auth_key" name="'.CF_DEPLOY_SETTINGS.'[auth_key]" value="'.$auth_key.'" /> &nbsp;
							<input id="cfd-new-auth-key" class="button-secondary button cf-elm-button" type="button" name="generate-key" value="'.__('Generate', 'cf-deploy').'" '.(!empty($auth_key) ? ' style="display: none"' : '').'/>
							<span class="cf-elm-help cf-elm-align-bottom">'.__('This auth key is to allow other servers to connect to this server.', 'cf-deploy').'</span>
						</div>
					</fieldset>
				</div>';
			
		# remote server(s)	
		echo '
				<div class="form-section">
					<fieldset class="cf-lbl-pos-left">
						<legend>'.__('Remote Server', 'cf-deploy').'</legend>
						<p class="cf-elm-help">'.__('Settings to allow this server to push content to a remote server.', 'cf-deploy').'</p>
						<div class="cf-elm-block cf-elm-width-full">
							<label for="'.CF_DEPLOY_SETTINGS.'_remote_server_0_address">'.__('Remote Server Address', 'cf-deploy').'</label>
							<input class="cf-elm-text" id="'.CF_DEPLOY_SETTINGS.'_remote_server_0_address" name="'.CF_DEPLOY_SETTINGS.'[remote_server][0][address]" value="'.$server_0_address.'" />
							<span class="cf-elm-help cf-elm-align-bottom">'.__('http://example.com/wordpress/ (path to WordPress, not site root)', 'cf-deploy').'</span>
						</div>
						<div class="cf-elm-block cf-elm-width-full">
							<label for="'.CF_DEPLOY_SETTINGS.'_remote_server_0_key">'.__('Remote Server Auth Key', 'cf-deploy').'</label>
							<input class="cf-elm-text" id="'.CF_DEPLOY_SETTINGS.'_remote_server_0_key" name="'.CF_DEPLOY_SETTINGS.'[remote_server][0][key]" value="'.$server_0_key.'" />
							<input type="button" class="button" name="cfd_settings_test" id="cfd_settings_test" value="'.__('Test', 'cf-deploy').'" />
							<span id="cf_deploy_test_comms_results"></span>
							<div id="cf_deploy_test_comms_message" style="display: none;"></div>
						</div>
					</fieldset>
				</div>';
				
		echo '
				<p class="submit">
					<input type="submit" name="submit" class="button-primary" value="'.__('Save Settings', 'cf-deploy').'" />
				</p>
			</form>
			';
		echo $this->admin_wrapper_close();

	}
	
	public function admin_import_history_page() {
		$import_history = get_posts(array(
			'post_type' => CF_DEPLOY_POST_TYPE,
			'post_status' => 'import',
			'showposts' => 25
		));

		echo '
			<h3>'.__('Batches Received', 'cf-deploy').'</h3>
			<table class="widefat fixed import-history">
				<thead>
					<tr>
						<th class="import-history-name">'.__('Batch Name', 'cf-deploy').'</th>
						<th class="import-history-date">'.__('Date', 'cf-deploy').'</th>
						<th class="import-history-source">'.__('Source', 'cf-deploy').'</th>
					</tr>
				</thead>
				<tbody>';
		if (!empty($import_history)) {
			$first = key($import_history);
			foreach ($import_history as $key => $import) {
				$source = get_post_meta($import->ID, '_batch_source', true);
				$complete = get_post_meta($import->ID, '_batch_import_complete', true);
				if ($key == $first) { 
					echo '<tr class="cf-has-rollback">';
				} else {
					echo '<tr>';
				}
				echo '<td class="ramp-batch-name">';
				if ($key == $first) {
					echo '
					<div class="cf-rollback">
						<input type="button" class="import-rollback-button button-primary" name="import_rollback" value="'.__('Rollback', 'cf-deploy').'" data-import-id="'.$import->ID.'" />
					</div>';
				}
				echo '<b>'.$import->post_title.'</b>';
				if (empty($complete)) {
					echo '<div class="message cfd-error">'.__('Error during import. See source server for details', 'cf-deploy').'</div>';
				}
				echo '</td>
						<td><span class="item-date">'.mysql2date('Y-m-d', $import->post_date).'</span><br /><span class="item-time">'.mysql2date('g:i a', $import->post_date).'</span></td>
						<td><a href="'.esc_url($source).'">'.esc_url($source).'</a></td>';
				echo '
					</tr>';
			}
		}
		else {
			echo '
					<tr>
						<td colspan="3">'.__('No batches to display.', 'cf-deploy').'</td>
					</tr>';
		}
		echo '
				</tbody>
			</table>';
	}
	
	public function admin_batch_history_page() {
		# make sure we can start our batch
		if (!empty($_REQUEST['batch'])) {
			$batch_id = intval($_REQUEST['batch']);
			try {
				$this->batch = new cfd_batch(array('ID' => $batch_id));
				echo $this->admin_wrapper_open(__('Batch Send Results for: ', 'cf-deploy').$this->batch->title);
				$this->show_admin_messages();
			}
			catch (Exception $e) {
				echo '<div class="error batch-error"><p><b>'.__('Cannot edit batch', 'cf-deploy').':</b> '.$e->getMessage().'</p></div>';
				return;
			}
		}
		else {
			echo '<div class="error batch-error"><p><b>'.__('No batch to edit', 'cf-deploy').'</b></p></div>';
			return;
		}
		
		$destination = esc_url(get_post_meta($this->batch->ID, '_batch_destination', true));
		
		/**
		 * Batch has already been sent. Scold the user
		 */
		if (isset($_GET['complete']) && $_GET['complete'] == 1) {
			echo '
				<div class="message error">
					<h3>'.__('Batch has already been sent', 'cf-deploy').'</h3>
					<p>'.__('Batches can only be sent once.', 'cf-deploy').'</p>
				</div>';
		}
		elseif (isset($_GET['success']) && $_GET['success'] == 1) {
			
			echo '
				<div class="message success">
					<h3>'.__('Transfer Complete', 'cf-deploy').'</h3>
					<p>'.sprintf(__('All items were successfully transfered. You may now leave this page. <a href="%s">Click here to visit the remote site</a>', 'cf-deploy'), $destination).'</p>
				</div>';
		}
		
		$this->batch_send_complete = get_post_meta($this->batch->ID, '_batch_export_complete', true);
		$this->batch_send_messages = get_post_meta($this->batch->ID, '_batch_deploy_messages', true);
		if (empty($this->batch_send_complete)) {
			echo '
				<div class="message error">
					<h3>'.__('Batch Export Incomplete', 'cf-deploy').'</h3>
				</div>';
		}
		elseif ($this->batch_send_complete == 2) {
			echo '
				<div class="message, error">
					<h3>'.__('Batch export cancelled', 'cf-deploy').'</h3>
				</div>';
		}

		$send_user = get_post_meta($this->batch->ID, '_batch_send_user', true);
		$sent_by = get_userdata($send_user);
		echo '
			<p>'.sprintf(__('Batch sent to <a href="%s">%s</a> by %s on %s.', 'cf-deploy'), $destination, $destination, $sent_by->user_nicename, mysql2date('Y-m-d h:i', $this->batch->post->post_modified)).'</p>
			<div id="cfd-batch-history">';
		$data = $this->batch->get_deploy_data(); 

		echo $this->batch_send_table_header();
		foreach ($data as $type => $data) {
			if (!empty($data)) {
				switch ($type) {
					case 'post_types':
						foreach ($data as $post_type => $posts) {
							if (!empty($posts)) {
								$p_type = get_post_type_object($post_type);								
								foreach ($posts as $guid => $post) {
									$id = 'post_types-'.$post['post']['ID'];
									$object_type = $post['post']['post_type'];
									echo $this->batch_send_table_row($id, $object_type, $guid, '', $p_type->labels->singular_name, esc_html($post['post']['post_title']));
								}
							}
						}
						break;
					case 'taxonomies':
						foreach ($data as $tax_type => $terms) {
							if (!empty($terms)) {

								if ($tax_type == 'link_category') {
									$taxonomy_name = __('Link Category', 'cf-deploy');
								}
								else {
									$t_type = get_taxonomy($tax_type);
									$taxonomy_name = $t_type->labels->singular_name;
								}
								foreach ($terms as $guid => $term) {
									$status = null;
									$id = 'taxonomies-'.$term['term_id'];
									$object_type = $term['taxonomy'];
									echo $this->batch_send_table_row($id, $object_type, $guid, '', $taxonomy_name, $term['name']);
								}
							}
						}
						break;
					case 'users':
						foreach ($data as $guid => $user) {
							$status = null;
							echo $this->batch_send_table_row('users-'.$user['ID'], '', $guid, '', __('User', 'cf-deploy'), $user['user_nicename'].' ('.$user['user_login'].')');
						}
						break;
					case 'menus':
						foreach ($data as $guid => $menu) {
							$status = null;
							echo $this->batch_send_table_row('menus-'.$menu['menu']['term_id'], '', $guid, '', __('Menu', 'cf-deploy'), $menu['menu']['name']);
						}
						break;
					case 'bookmarks':
						foreach ($data as $guid => $bookmark) {
							$status = null;
							echo $this->batch_send_table_row('bookmarks-'.$bookmark['link_id'], '', $guid, '', __('Link', 'cf-deploy'), $bookmark['link_name']);
						}
						break;
				}
			}
		}
		echo $this->batch_send_table_footer();
		$duplicate_url = admin_url('admin.php?page=cf-ramp-batch&from_batch='.$this->batch->ID);
		echo '
				<div id="duplicate-batch" class="submit">
					<input type="button" name="cfd-duplicate-batch" id="cfd-duplicate-batch" value="'.__('Duplicate Batch', 'cf-deploy').'" data-duplicate-url="'.$duplicate_url.'" />
				</div>
				<p><a href="#batch-transfer-messages" class="_toggle">'.sprintf(__('%sShow%s Transfer Messages', 'cf-deploy'), '<span class="_toggle_action">', '</span>').'</a></p>
				<div id="batch-transfer-messages" style="display: none;">
				';
		foreach ($this->batch_send_messages as $message) {
			$m = $message['message']->get_results();
			echo $this->parse_message_response($m['message']);
		}
		echo '
				</div>';
		echo '	
			</div>';
	}
	
	protected function get_deploy_extras($batch_data, $type) {
		$extras = array();
		if (!empty($this->extra_callbacks)) {
			foreach ($this->extra_callbacks as $id => $callbacks) {
				$result = $this->do_batch_extra($id, $type, $batch_data);
				if (!empty($result)) {
					$extras[$id] = $callbacks;
				}
				unset($result);
			}
		}
		// we don't trust users, just pull the extras and not the data that ran through the filter
		return $extras;
	}
	
	public function admin_batch_send_page() {
		check_admin_referer($this->deploy_batch_action_base);
		
		# make sure we can start our batch
		if (!empty($_REQUEST['batch'])) {
			$batch_id = intval($_REQUEST['batch']);
			try {
				$this->batch = new cfd_batch(array('ID' => $batch_id));
				echo $this->admin_wrapper_open(__('Batch Send Results for: ', 'cf-deploy').$this->batch->title);
				$this->show_admin_messages();
			}
			catch (Exception $e) {
				echo '<div class="error batch-error"><p><b>'.__('Cannot edit batch', 'cf-deploy').':</b> '.$e->getMessage().'</p></div>';
				return;
			}
		}
		else {
			echo '<div class="error batch-error"><p><b>'.__('No batch to edit', 'cf-deploy').'</b></p></div>';
			return;
		}
		
		echo '
			<script type="text/javascript">
				cf_batch_id = '.$this->batch->ID.';
				jQuery(function($) {
					cfd_redirect_url = "'.admin_url('admin.php?page=cf-ramp-batch-history&batch='.$this->batch->ID.'&success=1').'";
					cfd.send_batch(cf_batch_id);
				});
			</script>
			
			<div id="cfd-send-batch-message">
				<div class="warning message">
					<h3>'.__('Batch is being sent. Do not refresh or navigate away from this page until the batch is done sending.', 'cf-deploy').'</h3>
					<p>'.__('Leaving or refreshing this page before the batch is done sending will result in an incomplete batch transfer.', 'cf-deploy').'</p>
				</div>
			</div>
			
			<div id="cfd-send-batch-cancel" class="submit">
				<input type="button" class="button button-primary" name="cfd-cancel-batch-send" id="cfd-cancel-batch-send" value="'.__('Cancel Batch', 'cf-deploy').'" />
			</div>
			
			<div id="cfd-batch-todo">';
			
		$data = $this->batch->get_deploy_data(); 
		
		// give plugins a chance to attach extra batch operations based on the batch data
		$data['extras'] = $this->get_deploy_extras($data, 'send');

		echo $this->batch_send_table_header();
		foreach ($data as $type => $data) {
			if (!empty($data)) {
				switch ($type) {
					case 'post_types':
						foreach ($data as $post_type => $posts) {
							if (!empty($posts)) {
								$p_type = get_post_type_object($post_type);								
								foreach ($posts as $guid => $post) {
									$id = 'post_types-'.$post['post']['ID'];
									$object_type = $post['post']['post_type'];
									echo $this->batch_send_table_row($id, $object_type, $guid, 'pending', $p_type->labels->singular_name, esc_html($post['post']['post_title']));
								}
							}
						}
						break;
					case 'taxonomies':
						foreach ($data as $tax_type => $terms) {
							if (!empty($terms)) {

								if ($tax_type == 'link_category') {
									$taxonomy_name = __('Link Category', 'cf-deploy');
								}
								else {
									$t_type = get_taxonomy($tax_type);
									$taxonomy_name = $t_type->labels->singular_name;
								}
								foreach ($terms as $guid => $term) {
									$id = 'taxonomies-'.$term['term_id'];
									$object_type = $term['taxonomy'];
									echo $this->batch_send_table_row($id, $object_type, $guid, 'pending', $taxonomy_name, $term['name']);
								}
							}
						}
						break;
					case 'users':
						foreach ($data as $guid => $user) {
							echo $this->batch_send_table_row('users-'.$user['ID'], '', $guid, 'pending', __('User', 'cf-deploy'), $user['user_nicename'].' ('.$user['user_login'].')');
						}
						break;
					case 'menus':
						foreach ($data as $guid => $menu) {
							echo $this->batch_send_table_row('menus-'.$menu['menu']['term_id'], '', $guid, 'pending', __('Menu', 'cf-deploy'), $menu['menu']['name']);
						}
						break;
					case 'bookmarks':
						foreach ($data as $guid => $bookmark) {
							echo $this->batch_send_table_row('bookmarks-'.$bookmark['link_id'], '', $guid, 'pending', __('Link', 'cf-deploy'), $bookmark['link_name']);
						}
						break;
					case 'extras':
						foreach ($data as $type => $extra) {
							if (!empty($extra)) {
								echo $this->batch_send_table_row($type, 'extra', $type, 'pending', esc_html($extra['name']), $extra['description']);
							}
						} 
						break;
				}
			}
		}
		echo $this->batch_send_table_footer();
		echo '	
			</div>';
	}
	
	protected function batch_send_table_row($id, $object_type, $guid, $status, $type, $name) {
		$message = null;
		switch ($status) {
			case 'pending':
				$_status = __('Pending', 'cf-deploy');
				$_class = 'pending';
				break;
			case 'sent':
				$_status = __('Sent', 'cf-deploy');
				$_class = 'sent';
				break;
			case '':
				if (!empty($this->batch_send_messages[$guid])) {
					if ($this->batch_send_messages[$guid]['success'] == false) {
						$_status = __('Error', 'cf-deploy');
						$_class = 'error';
						$message = $this->parse_message_response(current($this->batch_send_messages[$guid]['message']));
					}
					else {
						$_status = __('Sent', 'cf-deploy');
						$_class = 'sent';
					}
				}
				else {
					$_status = __('Not Sent', 'cf-deploy');
					$_class = 'error';
				}
				
				break;
		}
		
		echo '
			<tr id="'.$id.'" data-object-type="'.$object_type.'" data-guid="'.htmlspecialchars($guid).'" class="'.$_class.'">
				<td><span class="send-item-status">'.$_status.'</span></td>
				<td><span class="item-type">'.$type.'</span></td>
				<td><span class="item-name">'.$name;
		if (!empty($message)) {
			echo $message;
		}	
		echo '</span></td>
			</tr>';
	}
	
	protected function batch_send_table_header() {
		$html = '
				<table id="send-batch-items" class="widefat batch-items">
					<thead>
						<tr>
							<th class="send-item-status">'.__('Status', 'cf-deploy').'</th>
							<th class="send-item-type">'.__('Type', 'cf-deploy').'</th>
							<th class="item-title">'.__('Name', 'cf-deploy').'</th>
						</tr>
					</thead>
					<tbody>';
		return $html;
	}
	
	protected function batch_send_table_footer() {
		$html = '
					</tbody>
				</table>';
		return $html;
	}
		
	public function admin_preflight_page() {
		check_admin_referer($this->deploy_batch_action_base);
		
		// boost our upper limit
		ini_set('memory_limit', '512M');
		
		# make sure we can start our batch
		if (!empty($_REQUEST['batch'])) {
			$batch_id = intval($_REQUEST['batch']);
			try {
				$this->batch = new cfd_batch(array('ID' => $batch_id));
				echo $this->admin_wrapper_open(__('Preflight results for: ', 'cf-deploy').$this->batch->title);
				$this->show_admin_messages();
			}
			catch (Exception $e) {
				echo '<div class="error batch-error"><p><b>'.__('Cannot edit batch', 'cf-deploy').':</b> '.$e->getMessage().'</p></div>';
				return;
			}
		}
		else {
			echo '<div class="error batch-error"><p><b>'.__('No batch to edit', 'cf-deploy').'</b></p></div>';
			return;
		}
		
		# do preflight
		try {
			$this->preflight_batch();
		}
		catch (Exception $e) {
			echo '<div class="error batch-error"><p><b>'.__('Cannot edit batch', 'cf-deploy').':</b> '.$e->getMessage().'</p></div>';
			$this->admin_wrapper_close();
			return;			
		}
				
		# output		
		echo '
				<form class="cf-form" method="post" name="preflight-form" action="'.admin_url('admin.php?page=cf-ramp-preflight&batch='.intval($_REQUEST['batch'])).'">
					<input type="hidden" name="cf_deploy_action" value="send-batch" />
					<input type="hidden" name="batch_ID" value="'.intval($this->batch->ID).'" />
					'.wp_nonce_field($this->deploy_batch_action_base, '_wpnonce', true, false);

		$data = $this->batch->get_deploy_data(true);
		
		$_extras = $this->batch->get_preflight_extras_data();
		if (!empty($_extras)) {
			$data['extras'] = $_extras;
			unset($extras);
		}
				
		$batch_return_url = admin_url('admin.php?page=cf-ramp-batch&batch='.$this->batch->ID);

		if (!empty($data)) {
			foreach (array('post_types', 'users', 'menus', 'taxonomies', 'bookmarks', 'extras') as $key) {
				if (!empty($data[$key])) {
					switch ($key) {
						case 'post_types':
							echo $this->post_types_preflight_display($data[$key]);
							break;
						case 'users':
							echo $this->user_preflight_display($data[$key]);
							break;
						case 'menus':
							echo $this->menu_preflight_display($data[$key]);
							break;
						case 'taxonomies':
							echo $this->taxonomies_preflight_display($data[$key]);
							break;
						case 'bookmarks':
							echo $this->bookmarks_preflight_display($data[$key]);
							break;
						case 'extras':
							echo $this->extras_preflight_display($data[$key], $data);
							break;
					}
				}
			}

			if ($this->batch->has_error() || !empty($this->admin_messages['__error__'])) {
				echo '
					<div class="warning message">'.__('Batch send is disabled because the batch has errors.', 'cf-deploy').'</div>';
			}

			echo '
				<div class="cf-footer">
					<p class="submit preflight-submit">
						<a class="return batch-screen-return" href="'.$batch_return_url.'">&laquo; '.__('Exit Preflight', 'cf-deploy').'</a>
						<input type="submit" name="batch_preflight_submit" class="button-primary" value="'.__('Send Batch', 'cf-deploy').'" '.($this->batch->has_error() || !empty($this->admin_messages['__error__']) ? 'disabled="disabled" ' : null).'/>&nbsp;
						<input type="submit" name="batch_preflight_refresh" class="button-secondary" value="'.__('Refresh', 'cf-deploy').'" />
					</p>
				</div>';
		}
		else {
			echo '
				<div class="warning empty-batch message"><p>'.__('<b>Empty Batch:</b> Items must be saved to your batch before preflight can happen.', 'cf-deploy').'</p></div>
				<p><a href="'.$batch_return_url.'">&laquo; '.__('Exit Preflight', 'cf-deploy').'</a></p>';
		}

		echo '
			</form>';
				
		echo $this->admin_wrapper_close();
	}
	
	public function extras_preflight_display($extras, $preflight_data) {
		if (!empty($extras)) {
			echo '
				<fieldset class="cf-lbl-pos-left" id="preflight-extras-data">
					<legend>'.__('Extras', 'cf-deploy').'</legend>';
			echo $this->batch_items_table_header('extras', 'extras');
			
			foreach ($extras as $extra_id => $extra_data) {
				if (!empty($extra_data)) {
					$class = ($this->has_preflight_concerns($extra_data) ? ' class="cf-has-concerns"' : '');
					echo '
						<tr'.$class.'>
							<td><b>'.$this->extra_callbacks[$extra_id]['name'].'</b></td>
							<td>'.$this->output_preflight_concerns($extra_data).'</td>
						</tr>';
				}
			}
			
			echo $this->batch_items_table_footer();
			echo '
				</fieldset>';
		}
	}
	
	public function post_types_preflight_display($post_types) {
		foreach ($post_types as $post_type => $objects) {
			$preflight_data = $this->batch->get_preflight_data('post_types', $post_type);
			echo '
				<fieldset class="cf-lbl-pos-left" id="preflight-'.$post_type.'-data">
					<legend>'.__($this->humanize($post_type), 'cf-deploy').'</legend>';
			
			if (!empty($preflight_data['__error__'])) {
				echo $this->comparison_error($preflight_data['__error__']);
			}
			else {
				echo $this->batch_items_table_header($post_type, 'preflight');
				$i = 0;
				foreach ($objects as $object) {
					$i++;
					$p_data = $preflight_data[$object->guid()];
					$class = ($this->has_preflight_concerns($p_data) ? ' class="cf-has-concerns"' : '');
					echo '
							<tr'.$class.'>
								<td>
									<b><a href="'.$object->edit_url().'">'.$object->name().'</a></b>';
					if ($object->post_parent !== 0) {
						echo '<br />Child of: <a href="'.get_edit_post_link($object->post_parent).'">'.get_the_title($object->post_parent).'</a>';
					}
					echo '
								</td>
								<td>'.__(($object->post_date == $object->post_modified ? 'Added' : 'Modified'), 'cf-deploy').': <span class="item-date">'.date('Y-m-d', strtotime($object->post_modified)).'</span>'.
									'<br /><span class="item-time">'.date('g:i a', strtotime($object->post_modified)).'</td>
								<td>
									<b>'.__($this->humanize($p_data['__action__']), 'cf-deploy').'</b>';
					echo $this->output_preflight_concerns($p_data);
					echo '
								</td>
							</tr>';
				}
				echo $this->batch_items_table_footer();
			}
			
			echo '
				</fieldset>';
		}
	}
	
	public function user_preflight_display($users) {
		$preflight_data = $this->batch->get_preflight_data('users');
		echo '
			<fieldset class="cf-lbl-pos-left" id="preflight-user-data">
				<legend>'.__('Users', 'cf-deploy').'</legend>';
		
		if (!empty($preflight_data['__error__'])) {
			echo $this->comparison_error($preflight_data['__error__']);
		}
		else {
			echo $this->batch_items_table_header('users', 'preflight');
			foreach ($users as $user) {
				$p_data = $preflight_data[$user->user_login];
				$class = ($this->has_preflight_concerns($p_data) ? ' class="cf-has-concerns"' : '');
				echo '
						<tr'.$class.'>
							<td>
								<b><a href="'.$this->user_edit_url($user->ID).'">'.$user->user_nicename.'</a></b><br />'.implode(', ', $user->roles).'
							</td>
							<td>'.__('Added').': <span class="item-date">'.mysql2date('Y-m-d', $user->user_registered).'</span><br />
								<span class="item-time">'.mysql2date('g:i a', $user->user_registered).'</td>
							<td>
								<b>'.__($this->humanize($p_data['__action__']), 'cf-deploy').'</b>';
				echo '
							</td>
						</tr>';
			}
			echo $this->batch_items_table_footer();
		}
		
		echo '
			</fieldset>';
	}
	
	public function menu_preflight_display($menus) {
		$preflight_data = $this->batch->get_preflight_data('menus', $post_type);
		echo '
			<fieldset class="cf-lbl-pos-left" id="preflight-user-data">
				<legend>'.__('Menus', 'cf-deploy').'</legend>';
		
		if (!empty($preflight_data['__error__'])) {
			echo $this->comparison_error($preflight_data['__error__']);
		}
		else {
			echo $this->batch_items_table_header('users', 'preflight');
			foreach ($menus as $menu) {
				$p_data = $preflight_data[$menu->guid()];
					$class = ($this->has_preflight_concerns($p_data) ? ' class="cf-has-concerns"' : '');
					echo '
							<tr'.$class.'>
							<td>
								<b><a href="'.$menu->edit_url().'">'.$menu->name().'</a></b>
							</td>
							<td>'.__('Modified', 'cf-deploy').': <span class="item-date">'.mysql2date('Y-m-d', $menu->last_modified()).'</span><br />'.
							  '<span class="item-time">'.mysql2date('g:i a', $menu->last_modified()).'</td>
							<td>
								<b>'.__($this->humanize($p_data['__action__']), 'cf-deploy').'</b>';
				echo $this->output_preflight_concerns($p_data);			
				echo '
							</td>
						</tr>';
			}
			echo $this->batch_items_table_footer();
		}
		echo '
			</fieldset>';		
	}

	protected function bookmarks_preflight_display($bookmarks) {
		$preflight_data = $this->batch->get_preflight_data('bookmarks', $post_type);
		echo '
			<fieldset class="cf-lbl-pos-left" id="preflight-user-data">
				<legend>'.__('Links', 'cf-deploy').'</legend>';
		
		if (!empty($preflight_data['__error__'])) {
			echo $this->comparison_error($preflight_data['__error__']);
		}
		else {
			echo $this->batch_items_table_header('users', 'preflight');	
			foreach ($bookmarks as $bookmark) {
				$p_data = $preflight_data[$bookmark->link_url];
				$class = ($this->has_preflight_concerns($p_data) ? ' class="cf-has-concerns"' : '');
				echo '
						<tr'.$class.'>
							<td><b><a href="'.get_edit_bookmark_link($bookmark->link_id).'">'.esc_html($bookmark->link_name).'</a></b><br /><span class="item-status-text">'.esc_html($bookmark->link_url).'</span></td>
							<td>--</td>
							<td>
								<b>'.__($this->humanize($p_data['__action__']), 'cf-deploy').'</b>';
				echo $this->output_preflight_concerns($p_data);
				echo '
							</td>
						</tr>';
			}
			echo $this->batch_items_table_footer();
		}
		echo '
			</fieldset>';		
	}
	
	public function taxonomies_preflight_display($taxonomies) {
		foreach ($taxonomies as $tax_type => $objects) {
			if (count($objects)) {
				$preflight_data = $this->batch->get_preflight_data('taxonomies', $tax_type);
				echo '
					<fieldset class="cf-lbl-pos-left" id="preflight-'.$key.'-data">
						<legend>'.__($this->humanize($tax_type), 'cf-deploy').'</legend>';
			
				if (!empty($preflight_data['__error__'])) {
					echo $this->comparison_error($preflight_data['__error__']);
				}
				else {
					echo $this->batch_items_table_header($tax_type, 'preflight');
					foreach ($objects as $term) {
						$p_data = $preflight_data[$term->slug];
						$term_edit_link = 'edit-tags.php?action=edit&amp;taxonomy='.$term->taxonomy.'&amp;post_type='.$term->post_type.'&amp;tag_ID='.$term->term_id;
						$class = ($this->has_preflight_concerns($p_data) ? ' class="cf-has-concerns"' : '');
						echo '
								<tr'.$class.'>
									<td><b><a href="'.$term_edit_link.'">'.$term->name.'</a></b></td>
									<td>--</td>
									<td>
										<b>'.__($this->humanize($p_data['__action__']), 'cf-deploy').'</b>';
						echo $this->output_preflight_concerns($p_data);		
						echo '
									</td>
								</tr>';
					}
					echo $this->batch_items_table_footer();
				}
				
				echo '
					</fieldset>';
			}
		}
	}
		
	protected function output_preflight_concerns($data) {
		$ret = '';
		if ($this->has_preflight_concerns($data)) {
			$ret .= '
							<tr class="cf-concerns">
								<td colspan="3">';
			foreach (array('__notice__', '__warning__', '__error__') as $data_type) {
				if (!empty($data[$data_type])) {
					$_data_type = str_replace('_', '', $data_type);
					$ret .= '
									<ol class="cf-'.$_data_type.' '.($_data_type == 'error' ? 'warning' : $_data_type).' message">';
					foreach ($data[$data_type] as $item) {
						$ret .= '
										<li>'.$item.'</li>';
					}
					$ret .= '
									</ol>';
				}
			}
			$ret .= '
								</td>
							</tr>';
		}
		return $ret;
	}
	
	protected function has_preflight_concerns($data) {
		return (bool) (!empty($data['__error__']) || !empty($data['__warning__']) || !empty($data['__notice__']));
	}
	
	protected function comparison_error($errors) {
		$html = '<div class="warning message">
				<p><b>'.__('Errors were found during comparison:', 'cf-deploy').'</b></p>
				<ol>';
		foreach ($errors as $errstring) {
			$html .= '<li>'.$errstring.'</li>';
		}
		$html .= '
				</ol>
			</div>';
		return $html;
	}
	
	public function admin_batch_page() {
		// boost our upper limit
		ini_set('memory_limit', '512M');
			
		# batch start date
		if (!empty($_REQUEST['start_date'])) {
			$batch_args['start_date'] = date('Y-m-d', strtotime($_REQUEST['start_date']));
		}
		elseif(empty($_REQUEST['batch'])) {
			$batch_args['start_date'] = $this->get_last_batch_date();
		}
				
		echo $this->admin_wrapper_open((!empty($_GET['batch']) ? 'Edit' : 'New').' Batch');
		
		if (!$this->sufficiently_configured()) {
			$this->show_admin_messages();
			echo $this->admin_wrapper_close();
			return;
		}
				
		# get batch
		if (empty($this->batch)) {
			# batch id
			if (!empty($_REQUEST['batch'])) {
				$batch_args['ID'] = intval($_REQUEST['batch']);
			}
			
			// pull in duplicate data
			if (!empty($_GET['from_batch']) && is_numeric($_GET['from_batch'])) {
				try {
					$from_batch = new cfd_batch(array(
						'ID' => intval($_GET['from_batch'])
					));
					$batch_args['data'] = $from_batch->get_data();
				}
				catch (Exception $e) {
					// boo!
				}
			}

			try {
				$this->batch = new cfd_batch($batch_args);
			}
			catch (Exception $e) {
				echo '<div class="error batch-error"><p><b>'.__('Cannot edit batch', 'cf-deploy').':</b> '.$e->getMessage().'</p></div>';
				$this->admin_wrapper_close();
				return;
			}
		}
				
		# do comparison
		$this->batch->init_comparison_data();
		$this->do_server_comparison($this->batch);
		$this->show_admin_messages();		
		echo '
			<form class="cf-form" method="post" name="batch-form">
				<input type="hidden" name="batch_ID" value="'.intval($this->batch->ID).'" />
				'.wp_nonce_field($this->deploy_batch_action_base, '_wpnonce', true, false);
		
		# batch details
		echo '
			<div class="form-section">
				<fieldset class="cf-lbl-pos-left" id="batch-details">
					<div class="cf-elm-block cf-elm-width-full">
						<label for="batch-title">'.__('Name', 'cf-deploy').'</label>
						<input type="text" name="batch_title" id="batch-title" class="cf-elm-text" value="'.htmlspecialchars($this->batch->title).'" />';
		if ($this->batch->date) {
			echo '
						<span class="cf-elm-help cf-elm-align-bottom">'.__(sprintf('Created on %s by %s', $this->batch->date, $this->batch->author->user_nicename), 'cf-deploy').'</span>';
		}
		echo '
					</div>
					<div class="cf-elm-block cf-elm-width-full">
						<label for="batch-description">'.__('Description', 'cf-deploy').'</label>
						<textarea name="batch_description" class="cf-elm-textarea" id="batch-description">'.htmlspecialchars($this->batch->description).'</textarea>
					</div>
					<div class="cf-elm-block">
						<label for="batch-start-date">'.__('Start Date', 'cf-deploy').':</label>
						<input type="text" name="batch_items[start_date]" id="batch-start-date" class="cf-elm-text" value="'.date('Y-m-d', strtotime($this->batch->start_date)).'" />
						<input type="button" name="batch_refresh_date" id="batch-refresh-button" class="button-secondary" value="'.__('Refresh', 'cf-deploy').'" />
						<span class="cf-elm-help cf-elm-align-bottom">'.__('YYYY-MM-DD format, default start date is set to the last completed batch', 'cf-deploy').'</span>
					</div>
					<p class="submit">
						<input type="submit" name="cf_deploy_save_batch" id="batch-save-button" class="button-primary" value="'.__('Save Batch', 'cf-deploy').'" /> 
					</p>
				</fieldset>
			</div><!-- .form-section -->
			<div  id="batch-contents">';

		# batch contents: post-types
		$post_types = $this->batch->get_comparison_data('post_types');

		if ($post_types && count($post_types)) {
			foreach($post_types as $type => $objects) {
				$p_type = get_post_type_object($type);
				
				if (!empty($objects['__error__'])) {
					$error = $objects['__error__'];
					unset($objects['__error__']);
				}
				echo '
						<fieldset class="cf-lbl-pos-left">
							<legend>'.$p_type->labels->name.'</legend>';
							if (!empty($error)) {
								echo $this->comparison_error($error);
								unset($error);
							}
				if (count($objects)) {
					echo $this->batch_items_table_header($type);
					foreach ($objects as $object) {
						echo '
								<tr>
									<td class="has-cb">';
						if (empty($object->errors)) {			
							echo '<input class="item-select" type="checkbox" name="batch_items[post_types]['.esc_attr($type).'][]" id="'.esc_attr($type).'-'.$object->id().'" '.
									'value="'.intval($object->id()).'" '.(!empty($object->selected) && $object->selected ? ' checked="checked"' : '').'/>';
						}
						echo '
									</td> 
									<td><b><a href="'.$object->edit_url().'">'.$object->name().'</a></b>';
						if ($object->post_parent !== 0) {
							echo '<br />Child of: <a href="'.get_edit_post_link($object->post_parent).'">'.get_the_title($object->post_parent).'</a>';
						}
						if (!empty($object->modified)) {
							echo '<br /><span class="item-status-text">'.__('Change'.(count($object->modified) > 1 ? 's' : '').' detected', 'cf-deploy').': '.implode(', ', $object->modified).'</span>';
						}
						if (!empty($object->errors)) {
							echo '<div class="cfd-error message">'.sprintf(__('<b>The following errors were encountered with this item and the item cannot be transferred:</b> %s', 'cf-deploy'), implode(', ', $object->errors)).'</div>';
						}
						echo '</td>
									<td>'.__(($object->post_date == $object->post_modified ? 'Added' : 'Modified'), 'cf-deploy').': <span class="item-date">'.date('Y-m-d', strtotime($object->post_modified)).'</span>'.
										'<br /><span class="item-time">'.date('g:i a', strtotime($object->post_modified)).'</span>
									<td>';
						if (!empty($object->status) && !empty($object->status['remote_status'])) {
							echo __(($object->status['remote_status']['post_date'] == $object->status['remote_status']['post_modified'] ? 'Added' : 'Modified'), 'cf-deploy').': '.
								'<span class="item-date">'.date('Y-m-d', strtotime($object->status['remote_status']['post_modified'])).'</span><br />'.
								'<span class="item-time">'.date('g:i a', strtotime($object->status['remote_status']['post_modified'])).'</span>';
							if ($object->status['remote_status']['profile']['post']['post_status'] == 'trash') {
								echo '<br /><span class="item-trash">'.__('Post Status: trash', 'cf-deploy').'</span>';
							}
						}
						else {
							echo '--';
						}
						echo '</td>
								</tr>';
					}
					echo $this->batch_items_table_footer();
				}
				else {
					echo '
							<div class="message cf-mar-top-none"><p>'.__(sprintf('No new or modified %s found.', $p_type->labels->name),'cf-deploy').'</p></div>';
				}
				echo '
						</fieldset><!-- /'.$type.' -->';			
			}
		}		

		# batch contents: menus
		$menus = $this->batch->get_comparison_data('menus');
		echo '
					<fieldset class="cf-lbl-pos-left">
						<legend>'.__('Menus', 'cf-deploy').'</legend>';
		if (!empty($menus)) {
			echo $this->batch_items_table_header('menus');
			foreach ($menus as $menu) {
				echo '
							<tr>
								<td class="has-cb"><input class="item-selected" type="checkbox" name="batch_items[menus][]" id="menus-'.$menu->id().'" value="'.$menu->id().'"'.
								(!empty($menu->selected) && $menu->selected == true ? ' checked="checked"' : '').' /></td>
								<td><b><a href="'.$menu->edit_url().'">'.$menu->name().'</a></b></td>
								<td>'.__('Modified', 'cf-deploy').': <span class="item-date">'.mysql2date('Y-m-d', $menu->last_modified()).'</span><br />'.
								  '<span class="item-time">'.mysql2date('g:i a', $menu->last_modified()).'</td>
								<td>';
				if (!empty($menu->status['remote_status'])) {
					echo __('Modified', 'cf-deploy').': <span class="item-date">'.mysql2date('Y-m-d', $menu->status['remote_status']['last_modified']).'</span><br />'.
					  '<span class="item-time">'.mysql2date('g:i a', $menu->status['remote_status']['last_modified']);
				}
				else {
					echo '--';
				}
				echo '
								</td>
							</tr>
					';
			}
			echo $this->batch_items_table_footer();
		}
		else {
			echo '
						<div class="message cf-mar-top-none"><p>'.__('No new or modified Menus found.', 'cf-deploy').'</p></div>';			
		}
		echo '
					</fieldset><!-- menus -->';

		# batch contents: users
		echo '
					<fieldset class="cf-lbl-pos-left">
						<legend>'.__('Users', 'cf-deploy').'</legend>';
		$users = $this->batch->get_comparison_data('users');
		if ($users && count($users)) {
			echo $this->batch_items_table_header('users');
			foreach($users as $user) {
				echo '
							<tr>
								<td class="has-cb"><input class="item-select" type="checkbox" name="batch_items[users][]" id="user-'.$user->ID.'" value="'.$user->ID.'" '.
								(!empty($user->selected) && $user->selected == true ? ' checked="checked"' : '').'/></td>
								<td><b><a href="'.$this->user_edit_url($user->ID).'">'.$user->user_login.'</a></b><br />'.implode(', ', $user->roles).'</td>
								<td>'.__('Added').': <span class="item-date">'.mysql2date('Y-m-d', $user->user_registered).'</span><br />
									<span class="item-time">'.mysql2date('g:i a', $user->user_registered).'</span></td>
								<td>	';
						if (!empty($user->modified) && $user->modified == 'profile') {
							echo __('Local &amp; Remote Differ', 'cf-deploy');
						}
						elseif (!empty($user->modified) && $user->modified == 'new') {
							echo __('New User', 'cf-deploy');
						}
						else {
							echo '--';
						}
						echo '</td>
							</tr>';
			}			
			echo $this->batch_items_table_footer();
		}
		else {
			echo '
						<div class="message cf-mar-top-none"><p>'.__('No new or modified Users found.','cf-deploy').'</p></div>';
		}
		echo '
					</fieldset><!-- /users -->';

		# That ends the REAL date based stuff, the rest is just wether it exists or not

		# batch contents: taxonomies
		$taxonomies = $this->batch->get_comparison_data('taxonomies');
		if (!empty($taxonomies)) {
			foreach ($taxonomies as $type => $objects) {
				$tax = get_taxonomy($type);
				echo '
						<fieldset class="cf-lbl-pos-left">
							<legend>'.$tax->labels->name.'</legend>';
				if (count($objects)) {
					echo $this->batch_items_table_header($type, 'short');
					foreach ($objects as $term) {
						$term_edit_link = 'edit-tags.php?action=edit&amp;taxonomy='.$type.'&amp;post_type='.$term->post_type.'&amp;tag_ID='.$term->term_id;
						echo '
									<tr>
										<td class="has-cb"><input class="item-select" type="checkbox" name="batch_items[taxonomies]['.esc_attr($type).'][]" id="'.esc_attr($type).'-'.$term->term_id.'" value="'.$term->term_id.'" '.
											(!empty($term->selected) && $term->selected == true ? ' checked="checked"' : '').'/></td>
										<td><b><a href="'.$term_edit_link.'">'.$term->name.'</a></b><br /><span class="item-status-text">Post Count: '.$term->count.'</span>';
						if ($term->parent > 0) {
							$parent = get_term($term->parent, $term->taxonomy);
							$parent_edit_link = 'edit-tags.php?action=edit&amp;taxonomy='.$parent->taxonomy.'&amp;post_type='.$parent->post_type.'&amp;tag_ID='.$parent->term_id;
							echo '<br /><span class="item-status-text">Child of: <a href="'.$parent_edit_link.'">'.$parent->name.'</a></span>';
						}			
						echo			'</td>
										<td><span class="item-status-text">'.($term->modified == 'new' ? __('New', 'cf-deploy') : __('Local &amp; Remote Differ', 'cf-deploy')).'</span></td>
									</tr>';
					}
					echo $this->batch_items_table_footer();

				}
				else {
					echo '
							<div class="message cf-mar-top-none"><p>'.__(sprintf('No new or modified %s found.', $tax->labels->name),'cf-deploy').'</p></div>';
				}
				echo '
						</fieldset><!-- /'.$type.' -->';
			}
		}

		# batch contents: bookmarks
		$bookmarks = $this->batch->get_comparison_data('bookmarks');
		echo '
					<fieldset class="cf-lbl-pos-left">
						<legend>'.__('Links', 'cf-deploy').'</legend>';
		if (!empty($bookmarks)) {
			echo $this->batch_items_table_header('bookmarks', 'short');
			foreach ($bookmarks as $bookmark) {
				$bmark_local_status = $bmark_remote_status = '--';
				echo '
							<tr>
								<td class="has-cb"><input class="item-selected" type="checkbox" name="batch_items[bookmarks][]" id="bookmarks-'.$bookmark->link_id.'" value="'.$bookmark->link_id.'" '.
								(!empty($bookmark->selected) && $bookmark->selected == true ? ' checked="checked"' : '').'/></td>
								<td><b><a href="'.get_edit_bookmark_link($bookmark->link_id).'">'.esc_html($bookmark->link_name).'</a></b><br /><span class="item-status-text">'.esc_html($bookmark->link_url).'</span></td>
								<td><span class="item-status-text">';
				switch (true) {
					case empty($bookmark->status['remote_status']):
						echo __('New', 'cf-deploy');
						break;
					default:
						echo __('Local &amp; Remote differ', 'cf-deploy');
						break;
				}			
				echo '</span></td>
							</tr>';
			}
			echo $this->batch_items_table_footer();
		}
		else {
			echo '
						<div class="message cf-mar-top-none"><p>'.__('No new or modified Links found.', 'cf-deploy').'</p></div>';
		}
		echo '
					</fieldset><!-- bookmarks -->';

		# Informational only items

		# plugins
		$plugins = $this->get_plugin_data();
		echo '
				<fieldset class="cf-lbl-pos-left">
					<legend>'.__('Plugins', 'cf-deploy').'</legend>';
		if (!empty($plugins)) {
			ob_start();
			foreach ($plugins as $plugin) {
				echo '
					<tr>
						<td><b>'.$plugin['Name'].'</b> by '.$plugin['Author'].'<br /><span class="item-status-text">'.$plugin['Description'].'</span></td>
						<td>'.__('Active', 'cf-deploy').'<br /><span class="item-status-text">'.__('Version', 'cf-deploy').': '.$plugin['Version'].'</span></td>
						<td>';
				if (!empty($plugin['remote_status'])) {
					echo 'Active<br /><span class="item-status-text">Version: '.$plugin['remote_status']['Version'].'</span>';
				}
				else {
					echo '<div class="notice message"><p>'.__('Not Active', 'cf-deploy').'</span></div>';					
				}
				echo '</td>
					</td>
					';
			}
			$plugins_rows = ob_get_clean();
			if (!empty($plugins_rows)) {
				echo $this->batch_items_table_header('plugins')
					.$plugins_rows
					.$this->batch_items_table_footer();
			}
			else {
				echo '
						<div class="message cf-mar-top-none"><p>'.__('No Plugin differences found.', 'cf-deploy').'</p></div>';
			}
		}
		else {
			echo '
					<div class="message cf-mar-top-none"><p>'.__('No Plugin differences found.', 'cf-deploy').'</p></div>';
		}
		echo '
				</fieldset><!-- /plugins -->';

		$preflight_disabled = '';
		if (!empty($this->admin_messages['__error__'])) {
			$preflight_disabled = ' disabled="disabled" ';
		}

		# close it out		
		echo '
				</div><!-- #batch-contents -->
				<div class="cf-footer">
					<p class="submit">
						<input type="hidden" name="cf_deploy_action" value="save-batch" />
						<input type="submit" name="cf_deploy_save_batch" id="batch-save-button" class="button-primary" value="'.__('Save Batch', 'cf-deploy').'" />&nbsp;
						<input type="submit" name="cf_deploy_preflight_batch" id="batch-preflight-button" class="button-secondary" value="'.__('Pre-flight Check', 'cf-deploy').'" '.$preflight_disabled.'/>
						<input type="submit" name="cf_deploy_delete_batch" id="batch-delete-button" class="cf-btn-delete batch-delete" value="'.__('Delete Batch', 'cf-deploy').'" />
					</p>
				</div>
			</form>
			';
		echo $this->admin_wrapper_close();		
	}
	
	protected function batch_items_table_header($type, $headers = 'full') {
		$no_cb_types = array(
			'plugins',
			'extras'
		);
		
		$html = '
					<table class="widefat batch-items">
						<thead>
							<tr>';
						
		if ($headers != 'preflight' && !in_array($type, $no_cb_types)) {
			// no checkboxes on preflight page
			$html .='
								<th class="has-cb"><input type="checkbox" name="'.esc_attr($type).'-select-all" class="select-all" value="1" /></th>';
		}
		$html .= '
								<th class="item-title">'.__('Title', 'cf-deploy').'</th>';
		if ($headers == 'short' || $headers == 'preflight') {
			$html .= '
								<th class="item-status">'.__('Status', 'cf-deploy').'</th>';
		}
		elseif ($headers == 'full') {
			$html .= '
								<th class="item-status">'.__('Local Status', 'cf-deploy').'</th>
								<th class="item-status">'.__('Remote Status', 'cf-deploy').'</th>';
		}
		elseif($headers == 'extras') {
			$html .= '
								<th class="extras-messages item-status">'.__('Messages', 'cf-deploy').'</th>';
		}
		if ($headers == 'preflight') {
			$html .= '
								<th class="item-action">'.__('Action', 'cf-deploy').'</th>';
		}
		$html .= '
							</tr>
						</thead>
						<tbody>';
		return $html;
	}
	
	protected function batch_items_table_footer() {
		return '
						</tbody>
					</table>';
	}
	
// Delete batch

	public function delete_batch() {
		check_admin_referer($this->deploy_batch_action_base);
		$del = wp_delete_post($_POST['batch_ID'], true);
		return $del === false ? false : true;
	}
		
// Save batch

	public function save_batch() {
		check_admin_referer($this->deploy_batch_action_base);
				
		$post_array = array(
			'ID' => !empty($_POST['batch_ID']) ? intval($_POST['batch_ID']) : 0,
			'post_type' => CF_DEPLOY_POST_TYPE,
			'post_title' => (!empty($_POST['batch_title']) ? $_POST['batch_title'] : 'New Batch '.date('Y-m-d', time())),
			'post_excerpt' => $_POST['batch_description'],
			'post_content' => serialize($_POST['batch_items'])
		);
		
		return wp_insert_post($post_array);
	}
	
	public function preflight_batch() {				
		$batch_items = $this->batch->get_deploy_data(false, true);
		if (empty($batch_items)) {
			return false;
		}
		
		// give plugins a chance to attach extra data for comparison
		$extras = $this->get_preflight_extras();
		if (!empty($extras)) {
			$batch_items['extras'] = $extras;
			unset($extras);
		}
		
		cfd_tmp_dbg('preflight_send_data.txt', $batch_items);

		$params = array(
			'server' => $this->options['remote_server'][0]['address'],
			'auth_key' => $this->options['remote_server'][0]['key'],
			'method' => 'preflight',
			'args' => array(
				'batch_items' => $batch_items
			)
		);
		
		$ret = $this->send($params);
		
		if (!$ret->success) {
			echo '<div class="error batch-error message"><p><b>'.__('Fatal Error: Batch Preflight Failed', 'cf-deploy').'</b></p></div>';
			echo $ret->message;
			$this->add_admin_message('__error__', $ret->message);
			return false;
		}
		else {
			$this->batch->parse_preflight_data($ret->message);
			return true;
		}
	}
	
	protected function get_preflight_extras() {
		$extras = array();
		
		if (!empty($this->extra_callbacks)) {
			$batch_data = $this->batch->get_deploy_data();
			foreach (array_keys($this->extra_callbacks) as $extra_id) {
				$method = $this->get_extra_callback_method($extra_id, 'preflight_send');
				$_ret = call_user_func($method, $batch_data);
				if (!empty($_ret)) {
					$extras[$extra_id] = $_ret;
				}
				unset($_ret);
			}
		}

		return $extras;
	}

// Settings

	public function validate_settings($settings) {
		foreach ($settings['remote_server'] as &$server) {
			$server['address'] = esc_url($server['address']);
		}
		return $settings;
	}

// Ajax 

	public function ajax_handler() {
		$this->in_ajax = true;
		if (method_exists($this, 'ajax_'.strval($_POST['cfd_action']))) {
			$method = 'ajax_'.strval($_POST['cfd_action']);
			
			if (!isset($_POST['args'])) {
				$_POST['args'] = array();
			} 
			if (isset($_POST['args']) && is_array($_POST['args'])) {
				$args = $_POST['args'];
			}
			else {
				parse_str($_POST['args'], $args);
			}
			
			try {
				$result = $this->$method($args);
			}
			catch(cfd_exception $e) {
				$result = new cfct_message(array(
					'success' => false,
					'message' => $e->getHTML(),
					'type' => $e->getMessage()
				));
			}
			catch(Exception $e) {
				$result = new cfd_message(array(
					'success' => false,
					'message' => '<div class="cfd-error message error"><p>'.__('An Error has occurred:', 'cf-deploy').' '.$e->getMessage().'</p></div>',
					'type' => 'unknown-ajax-error'
				));
			}
		}
		else {
			$result = new cfd_message(array(
				'success' => false,
				'type' => __('invalid function call: function does not exist','cf-deploy')
			));			
		}

		$result->send();
	}

	protected function ajax_generate_key() {
		return new cfd_message(array(
			'success' => true,
			'message' => $this->generate_key(),
			'type' => 'generate-key'
		));
	}
	
	/**
	 * Test communications between servers
	 * Will fail if:
	 * - cannot modify local ram limit
	 * - cannot write test file to uploads dir
	 * - remote server does not respond
	 * - remote server can't load test file from uploads dir
	 * - remote server can't modify the memory limit
	 *
	 * @see cf_deploy_client::ixr_comms_test() for remote tests
	 * @param string $args 
	 * @return void
	 */
	protected function ajax_test_comms_settings($args) {
		// test this servers' memory limit
		$old_memory_limit = ini_get('memory_limit');
		@ini_set('memory_limit', '512M');
		if(ini_get('memory_limit') != '512M') {
			return new cfd_message(array(
				'success' => false,
				'type' => 'test-comms-settings-results',
				'message' => '<div class="cfd-error message"><p>'.sprintf(__('Could not modify ram limit on the server "%s". RAMP requires the ability to raise the ram limit during batch operations.', 'cf-deploy'), get_bloginfo('url')).'<p></div>'
			));
		}
		else {
			ini_set('memory_limit', $old_memory_limit);
		}
		
		// put a test file in the uploads folder
		$uploads = wp_upload_dir();
		$written = file_put_contents(trailingslashit($uploads['basedir']).'test.txt', 'foo');
		
		if ($written < 1) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'test-comms-settings-results',
				'message' => '<div class="message cfd-error"><p>'.sprintf(__('Could not write test file to uploads dir on server (%s).', 'cf-deploy'), get_bloginfo('url')).'</p></div>'
			));
		}

		// prep call
		$server = $args['server'];
		$auth_key = $args['key'];
		
		$params = array(
			'server' => $server,
			'auth_key' => $auth_key,
			'method' => 'ixr_comms_test',
			'args' => array(
				'calling_from' => trailingslashit($uploads['baseurl']).'test.txt'
			)
		);
		
		$response = $this->send($params);
		
		// remove test file
		unlink(trailingslashit($uploads['basedir']).'test.txt');
		
		if ($response->success) {
			// communication with remote end successful, now lets check the test results
			$ret = $response->message;
			if (!empty($ret->is_cfd_message) && $ret->is_cfd_message) {
				if (!$ret->success) {
					$ret->message = '<div class="message cfd-error">'.$ret->message.'</div>';
					return $ret;
				}
				else {
					return new cfd_message(array(
						'success' => true,
						'type' => 'test-comms-settings-results',
						'message' => 'OK'
					));
				}
			}
		}
		else {
			return new cfd_message(array(
				'success' => $response->success,
				'type' => 'test-comms-settings-result',
				'message' => ($response->success ? 'OK' : $response->message)
			));
		}
	}
	
// Messages

	protected function show_messages() {
		$html = '';
		
		switch (true) {
			case !empty($_GET['updated']):
				$html = '<p>'.__('Settings Updated', 'cf-deploy').'</p>';
				$class = 'updated';
				break;
		}
		
		if (!empty($html)) {
			$html = '<div id="message" class="'.$class.' fade">'.$html.'</div>';
		}
		
		return $html;
	}

// Utility

	// if (empty($plugin['remote_status']) || $plugin['remote_status']['Version'] != $plugin['Version']) {

	protected function get_plugin_data() {
		$plugins = $this->get_plugins();
		$remote_plugins = $this->send(array(
			'server' => $this->options['remote_server'][0]['address'],
			'auth_key' => $this->options['remote_server'][0]['key'],
			'method' => 'get_plugins',
			'args' => array()
		));
		$plugins_diff = array();
		if (!empty($remote_plugins->message) && is_array($remote_plugins->message)) {
			foreach ($plugins as $key => $plugin) {
				if (empty($remote_plugins->message[$key]) || $plugin['Version'] != $remote_plugins->message[$key]['Version'] || isset($plugin['Active']) != isset($remote_plugins->message[$key]['Active']) || (isset($plugin['Active']) && $plugin['Active'] != $remote_plugins->message[$key]['Active'])) {
					$_plugin = $plugin;
					$_plugin['remote_status'] = $remote_plugins->message[$key];
					$plugins_diff[$key] = $_plugin;
				}
			}			
		}
		return $plugins_diff;
	}

	protected function user_edit_url($user_id) {
		return admin_url('user-edit.php?user_id='.intval($user_id));
	}

	protected function do_server_comparison($batch) {
		$c_data = $batch->get_batch_status_data();
		$params = array(
			'server' => $this->options['remote_server'][0]['address'],
			'auth_key' => $this->options['remote_server'][0]['key'],
			'method' => 'compare',
			'args' => array(
				'c_data' => $c_data
			)
		);
		
		$ret = $this->send($params);

		if (!$ret->success) {
			$this->add_admin_message('__error__', '<b>' . __('Fatal Error: Batch Comparison Failed', 'cf-deploy') . '</b>');
			$this->add_admin_message('__important__', __('The remote server address and auth key in the <a href="' . admin_url("admin.php?page=cf-ramp-settings") . '">RAMP Settings</a> may need to be corrected to continue.', 'cf-deploy'));
			$this->add_admin_message('__unformatted__', $ret->message);
			return false;
		}
		else {
			$batch->parse_status_data($ret->message);
			return $ret->true;
		}
	}

	protected function batch_table($batches) {
		$page = $_GET['page'] == 'cf-deploy-history' ? 'history' : 'active';
		
		$header_row = '<tr>
						<th class="batch-title manage-column">'.__('Batch Name', 'cf-deploy').'</th>';
		if ($page == 'history') {
			$header_row .= '
						<th class="batch-destination manage-column">'.__('Destination', 'cf-deploy').'</th>';
		}	
		$header_row .= '
						<th class="batch-date manage-column">'.__('Date', 'cf-deploy').'</th>
						<th class="batch-author manage-column">'.__('Created By', 'cf-deploy').'</th>
					</tr>';
		
		echo '
			<table class="widefat fixed '.$page.'">
				<thead>
					'.$header_row.'
				</thead>
				<tbody>';
			
		if (count($batches)) {
			$authors = array();
			foreach ($batches as $batch) {
				if (!isset($authors[$batch->post_author])) {
					$authors[$batch->post_author] = new WP_User($batch->post_author);
				}
				$author_link = esc_url(add_query_arg('wp_http_referer', urlencode(esc_url(stripslashes($_SERVER['REQUEST_URI']))), 'user-edit.php?user_id='.$batch->post_author));
				
				echo '
					<tr>
						<td><a href="'.admin_url('admin.php?page=cf-ramp-batch'.($page == 'history' ? '-history' : '').'&batch='.$batch->ID).'">'.
							apply_filters('the_title', $batch->post_title).'</a><br /><span class="item-status-text">'.esc_html($batch->post_excerpt).'</span>';
				if ($page == 'history') {
					$batch_send_complete = get_post_meta($batch->ID, '_batch_export_complete', true);
					if (empty($batch_send_complete)) {
						echo '<div class="cfd-error message">'.__('An error occured during batch send. Batch export incomplete.', 'cf-deploy').'</div>';
					}
					elseif ($batch_send_complete == 2) {
						echo '<div class="cfd-error message">'.__('Batch export cancelled', 'cf-deploy').'</div>';
					}
					$rolled_back = get_post_meta($batch->ID, '_batch_rolled_back', true);
					if (!empty($rolled_back)) {
						echo '<div class="notice message">'.__('Batch was rolled back on destination', 'cf-deploy').'</div>';
					}
				}		
				echo '</td>';
				if ($page == 'history') {
					$destination = esc_url(get_post_meta($batch->ID, '_batch_destination', true));
					echo '
						<td><a href="'.$destination.'">'.$destination.'</a></td>';
				}
				echo '
						<td><span class="item-date">'.mysql2date('Y-m-d', $batch->post_date).'</span><br /><span class="item-time">'.mysql2date('g:i a', $batch->post_date).'</span></td>
						<td><a href="'.$author_link.'">'.$authors[$batch->post_author]->user_nicename.'</a></td>
					</tr>';
			}
			echo '
				</tbody>';
			if (count($batches) > 15) {
				echo '
				<tfoot>
					'.$header_row.'
				</tfoot>';
			}
		}
		else {
			echo '
					<tr>
						<td colspan="3" class="batch-none">
							'.__('No batches to display.', 'cf-deploy').'
						</td>
					</tr>
				</tbody>';
		}
		
		echo '
			</table>';
	}
	
	public function batch_new_url() {
		return admin_url('admin.php?page=cf-ramp-batch');
	}
	
	public function batch_history_url() {
		return admin_url('admin.php?page=cf-ramp-history');
	}
	
	protected function admin_wrapper_open($title = '', $product_name = true) {
		if ($product_name) {
			$title = CF_DEPLOY_TITLE_NAME.' '.$title;
		}
		$html = '
			<div id="cfd-wrap" class="wrap">
				<div id="cfd-messages" style="display: none;"></div>
				'.screen_icon().'<h2>'.__($title, 'cf-deploy').'</h2>
				'.$this->show_messages().'
				<div id="cf">
			';
		return $html;
	}
	
	protected function admin_wrapper_close() {
		return '
				</div> <!-- #cf -->
			</div><!-- #cfd-wrap -->';
	}
	
	protected function generate_key() {
		return substr(crypt(md5(time().get_bloginfo('url'))), 0, 32);
	}
	
	/**
	 * Generic message response parser
	 * 
	 * Assumes: array('__message_type__' => array('message', 'message'), ...);
	 *
	 * @param array $message
	 * @return string
	 */
	protected function parse_message_response($message, $header = '') {
		$msg = '';
	
		// if message is a string then convert to a notice
		if (!is_array($message)) {
			$message = array(
				'__notice__' => array(
					'messages' => array($message)
				)
			);
		}
		
		if (is_array($message)) {
			foreach ($message as $type => $messages) {
				if ($type == '_note') {
					// _note to be used for internal purposes only
					continue;
				}
				if (!empty($messages)) {
					$class = (in_array($type, array('__notice__', '__warning__', '__error__')) ? str_replace('_', '', $type) : 'notice');
					$msg .= '
						<div class="'.strtolower($class).' message">';
				
					$msg .= '
							<p>'.$this->humanize($header).' '.$this->humanize(str_replace('_', '', $type)).':</p>';
				
					$msg .= '
							'.$this->parse_message_response_array($messages).'
						</div>';
				}
			}
		}
		return $msg;
	}
	
	protected function parse_message_response_array($messages) {
		$ret = '';
		if (!empty($messages)) {
			$ret = '<ul>';
			if (is_array($messages)) {
				foreach ($messages as $key => $message) {
					if ($key != 'message' && is_array($message)) {
						$ret .= '
							<li>';
						if (!is_numeric($key)) {
							$ret .= '
								<p>'.$this->humanize($key).':</p>';
						}					

						$ret .= $this->parse_message_response_array($message);
					
						$ret .= '
							</li>';
					}
					elseif ($key == 'message' && is_array($message)) {
						foreach ($message as $messg) {
							$ret .= '<li>'.$messg.'</li>';
						}
					}
					else {
						$ret .= '
								<li>'.$message.'</li>';
					}
				}
			}
			else {
				$ret .= '
						<li>'.$messages.'</li>';
			}
			$ret .= '</ul>';
		}
		return $ret;
	}
	
}

?>