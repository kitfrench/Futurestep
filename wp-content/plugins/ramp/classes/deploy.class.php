<?php

class cf_deploy extends cf_deploy_client {
	protected $options;
	protected $batches;
	protected $batch_import_messages = array();
	protected $batch_import_id;
	
	protected $extra_callbacks;
	
	protected $post_date_import_filter_data;		// holds data for post_date_import_filter routine
	
	protected $in_rollback = false;
	
	public function __construct() {
		$this->get_options();
	}
	
	public function get_options() {
		$this->options = maybe_unserialize(get_option(CF_DEPLOY_SETTINGS, array(
			'auth_key' => null,
			'remote_server' => null
		)));
	}
		
// Post Data Getters

	public function get_full_post_data($post_id) {
		try {
			$post = new cfd_post($post_id);
		}
		catch(Exception $e) {
			$post = new WP_Error('get_full_post', $e->getMessage());
		}

		return $post;
	}

// Data Comparison

	/**
	 * Compare incoming data from IXR to local data & report back differences
	 *
	 * @param array $args 
	 * @return array
	 */
	protected function compare($args) {
		// boost our upper limit
		ini_set('memory_limit', '512M');
		
		cfd_tmp_dbg('cf-compare-received.txt', $args, 'print');
		
		$c_data = $args['c_data'];
		foreach ($c_data as $type => &$data) {
			if (count($data)) {
				switch ($type) {
					case 'post_types':
						foreach ($data as $post_type => $items) {
							$data[$post_type] = $this->compare_post_types($post_type, $items);
						}
						break;
					case 'users':
						$data = $this->compare_users($data);
						break;
					case 'taxonomies':
						foreach ($data as $tax_type => $items) {
							$data[$tax_type] = $this->compare_taxonomies($tax_type, $items);
						}
						break;
					case 'bookmarks':
						$data = $this->compare_bookmarks($data);
						break;
					case 'menus':
						$data = $this->compare_menus($data);
						break;
				}
			}
		}
		
		cfd_tmp_dbg('cf-compare-returned.txt', $c_data, 'print');
		
		return $c_data;
	}
	
	protected function get_post_ids_by_guid($guids, $post_type) {
		global $wpdb;
		
		$posts = array();

		if (is_array($guids) && !empty($guids)) {
			array_map(array($wpdb, 'escape'), $guids);
			$_guids = '"'.implode('", "', $guids).'"';
			$base_query = 'SELECT ID, post_name, post_type, guid, post_date, post_modified FROM '.$wpdb->posts.' WHERE guid IN (%s) AND post_type = "%s"';
			$posts = $wpdb->get_results(sprintf($base_query, $_guids, $wpdb->escape($post_type)), ARRAY_A);
		}
		
		return $posts;
	}
	
	protected function compare_post_types($type, $data) {
		global $wpdb;
		
		// return failure if post-type doesn't exist
		if (!post_type_exists($type)) {
			return array(
				'__error__' => sprintf(__('Post Type "%s" does not exist on destination. Post types must be managed manually before objects in the type can be transferred.', 'cf-deploy'), $type)
			);
		}
		
		$names = $guids = array();
		foreach ($data as $object) {
			$guids[] = $object['guid'];
		}
		$q_posts = $this->get_post_ids_by_guid($guids, $type);

		$posts = array();
		if (count($q_posts)) {
			// edit_uri cobbled together from parts of `get_edit_post_link()`
			$post_type_object = get_post_type_object($type);
			foreach($q_posts as $post) {
				$class = ($type == 'attachment' ? 'cfd_attachment' : 'cfd_post');
				$_p = new $class(intval($post['ID']));
				$post['profile'] = $_p->profile();
				$post['edit_uri'] = $_p->edit_url();
				$posts[$post['guid']] = $post;
			}
		}		
		return $posts;		
	}
	
	protected function compare_users($data) {
		global $wpdb;

		$user_logins = array();
		foreach ($data as $user) {
			$user_logins[] = $wpdb->escape($user['user_login']);
		}

		$_logins = '"'.implode('", "', $user_logins).'"';
		$query = sprintf('SELECT ID, user_login, user_email, user_registered FROM '.$wpdb->users.' WHERE user_login IN (%s)', $_logins);
		$_users = $wpdb->get_results($query, ARRAY_A);

		$users = array();

		foreach ($_users as $user) {
			$_u = new cfd_user(array('user_id' => $user['ID']));
			$user['profile'] = md5(serialize($_u->profile()));
			$users[$user['user_login']] = $user;
		}

		return $users;
	}
	
	protected function compare_taxonomies($tax_type, $data) {
		$tax_terms = get_terms($tax_type, array(
			'hide_empty' => false
		));
		
		$terms = array();
		if (count($tax_terms)) {
			foreach ($tax_terms as $term) {
				if (!empty($data[$term->slug])) {
					$terms[$term->slug] = array(
						'slug' => $term->slug,
						'name' => $term->name,
						'description' => $term->description,
						'parent' => $term->parent
					);
				}
			}
		}
		return $terms;
	}
	
	protected function compare_bookmarks($data) {
		$bookmarks = array();
		$b_marks = get_bookmarks();

		if (!empty($b_marks)) {
			foreach ($b_marks as $b_mark) {
				if (isset($data[$b_mark->link_url])) {
					
					$b_cats = wp_get_object_terms( $b_mark->link_id, 'link_category', array('fields' => 'all') );
					foreach ($b_cats as $cat) {
						$b_mark->categories[] = $cat->slug;
					}
					
					unset($b_mark->link_updated, $b_mark->link_id);
					
					$bookmarks[$b_mark->link_url] = array(
						'link_hash' => md5(serialize($b_mark))
					);
				}
			}
		}
		return $bookmarks;
	}
	
	protected function compare_menus($data) {
		$menus = array();
		if (count($data)) {
			foreach ($data as $menu) {
				try {
					$m = new cfd_menu($menu['guid']);
					$menus[$m->guid()] = array(
						'guid' => $m->guid(),
						'last_modified' => $m->last_modified()
					);
				}
				catch (Exception $e) {
					// doesn't exist, which is fine here
				}
			}
		}
		return $menus;
	}

// Send Batch Item

	protected function ajax_open_batch_send($args) {
		// reset failure status
		delete_post_meta($args['batch_id'], '_batch_export_failed');
		
		if (get_post_meta($args['batch_id'], '_batch_export_complete', true)) {
			$history_url = admin_url('admin.php?page=cf-ramp-batch-history&batch='.$args['batch_id']);
			return new cfd_message(array(
				'success' => false,
				'type' => 'batch-send-complete',
				'message' => '
					<div class="error message">
						<h3>'.__('Batch has already been sent', 'cf-deploy').'</h3>
						<p>'.sprintf(__('The batch already been sent. You cannot resend a batch. <a href="%s">Click here to view the batch&rsquo;s history page</a>.', 'cf-deploy'), $history_url).'</p>
					</div>'
			));
		}
		
		cfd_tmp_dbg('single-item-data.txt', '', 'print');
		
		$batch_session_token = get_post_meta($args['batch_id'], '_batch_session_token', true);
		if (!empty($batch_session_token)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'session-in-progress',
				'message' => '
					<div class="error message">
						<h3>'.__('Session Already In Progress', 'cf-deploy').'</h3>
						<p>'.__('The batch already has a session in progress. Cannot send batch.', 'cf-deploy').'</p>
					</div>'
			));
		}
		
		wp_update_post(array(
			'ID' => $args['batch_id'],
			'post_status' => 'publish'
		));
		update_post_meta($args['batch_id'], '_batch_deploy_messages', array());
		
		$batch_session_token = substr(md5(time()), 0, 12);
		add_post_meta($args['batch_id'], '_batch_session_token', $batch_session_token);
		add_post_meta($args['batch_id'], '_batch_destination', $this->options['remote_server'][0]['address']);
		add_post_meta($args['batch_id'], '_batch_send_user', get_current_user_id());
		
		$batch = new cfd_batch(array(
			'ID' => intval($args['batch_id'])
		));
		$params = array(
			'server' => $this->options['remote_server'][0]['address'],
			'auth_key' => $this->options['remote_server'][0]['key'],
			'method' => 'import_batch_open',
			'args' => array(
				'batch_id' => $args['batch_id'],
				'batch_title' => $batch->title,
				'source' => get_bloginfo('url')
			)
		);
		
		$response = $this->send($params);
		
		if ($response->success === true) {
			return new cfd_message(array(
				'success' => true,
				'type' => 'batch-send-open',
				'message' => array(
					'batch_session_token' => $batch_session_token,
					'batch_import_id' => $response->message
				)
			));		
		}
		else {
			add_post_meta($args['batch_id'], '_batch_export_failed', 1);
			return new cfd_message(array(
				'success' => false,
				'type' => 'batch-send-open',
				'message' => $response->message
			));
		}
	}
	
	protected function ajax_close_batch_send($args) {
		delete_post_meta($args['batch_id'], '_batch_session_token', $args['batch_session_token']);
		add_post_meta($args['batch_id'], '_batch_export_complete', 1);
		
		$params = array(
			'server' => $this->options['remote_server'][0]['address'],
			'auth_key' => $this->options['remote_server'][0]['key'],
			'method' => 'import_batch_close',
			'args' => array(
				'batch_id' => $args['batch_id'],
				'batch_import_id' => $args['batch_import_id']
			)
		);
		
		$response = $this->send($params);
		
		if ($response->success === true) {
			$message = '
				<div class="success message">
					<h3>'.__('Transfer Complete', 'cf-deploy').'</h3>
					<p>'.sprintf(__('All items were successfully transfered. You may now leave this page. <a href="%s">Click here to visit the remote site</a>', 'cf-deploy'), esc_url($this->options['remote_server'][0]['address'])).'</p>
				</div>';
			return new cfd_message(array(
				'success' => true,
				'type' => 'batch-send-close',
				'message' => $message
			));
		}
		else {
			return new cfd_message(array(
				'success' => false,
				'type' => 'batch-send-close',
				'message' => $response->message
			));
		}
	}
	
	protected function ajax_cancel_batch_send($args) {
		delete_post_meta($args['batch_id'], '_batch_session_token');
		delete_post_meta($args['batch_id'], '_batch_export_complete');
		add_post_meta($args['batch_id'], '_batch_export_complete', 2);
		
		return new cfd_message(array(
			'success' => true,
			'type' => 'batch-cancel-confirm',
			'message' => '
				<div class="warning message">
					<h3>'.__('Batch Cancelled', 'cf-deploy').'</h3>
					<p>'.sprintf(__('Batch session cancelled. The batch on the remote server is incomplete but can be rolled back. <a href="%s">Click here to visit the remote site</a>', 'cf-deploy'), esc_url($this->options['remote_server'][0]['address'])).'</p>
				</div>'
		));
	}
	
	protected function ajax_send_batch_item($args) {
		$item['object_type'] = $args['batch_item_object_type'];
		$item['guid'] = $args['batch_item_guid'];
		list($item['type'], $item['id']) = explode('-', $args['batch_item_id']);

		// make sure we're in one session only
		$session_token = get_post_meta($args['batch_id'], '_batch_session_token', true);
		if ($session_token != $args['batch_session_token']) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'batch-session-token-mismatch',
				'message' => '
					<div class="error message">
						<h3>'.__('Session Token Mismatch', 'cf-deploy').'</h3>
						<p>'.__('The batch session token does not match.', 'cf-deploy').'</p>
					</div>'
			));
		}
		
		// get deploy data
		$this->batch = new cfd_batch(array(
			'ID' => intval($args['batch_id'])
		));
		
		if ($args['batch_item_object_type'] == 'extra') {
			$deploy_data[$args['batch_item_id']] = $this->do_batch_extra($args['batch_item_id'], 'send', $this->batch->get_deploy_data());
		}
		else {
			$deploy_data = $this->batch->get_deploy_item_data($item);
		}
		cfd_tmp_dbg('single-item-data.txt', print_r($deploy_data, true).PHP_EOL.PHP_EOL.PHP_EOL, 'print', true);
				
		// set deploy data to send
		$params = array(
			'server' => $this->options['remote_server'][0]['address'],
			'auth_key' => $this->options['remote_server'][0]['key'],
			'method' => 'import_batch'.($args['batch_item_object_type'] == 'extra' ? '_extra' : ''),
			'args' => array(
				'batch_id' => $args['batch_id'],
				'batch_import_id' => $args['batch_import_id'],
				'batch_session_token' => $args['batch_session_token'],
				'source' => get_bloginfo('url'),
				'batch' => $deploy_data
			)
		);
		
		$response = $this->send($params);
		
		// set messages
		$_messages = get_post_meta($args['batch_id'], '_batch_deploy_messages', true);
		if (!is_array($_messages)) {
			$_messages = array();
		}
		$_messages[$item['guid']] = array(
			'success' => $response->success,
			'message' => $response->message
		);
		update_post_meta($args['batch_id'], '_batch_deploy_messages', $_messages);
		
		// respond
		if ($response->success == true) {
			$message_args = array(
				'success' => true,
				'type' => $args['batch_item_id'],
				'message' => __('Item transfer successful', 'cf-deploy')
			);
		}
		else {
			delete_post_meta($args['batch_id'], '_batch_session_token');
			add_post_meta($args['batch_id'], '_batch_export_failed', 1);
			if (is_array($response->message)) {
				$key = key($response->message);
				if (in_array($key, array('post_types', 'taxonomies'))) {
					$error_message = current($response->message[$key]);
				}
				else {
					$error_message = current($response->message[$key]);
				}
				$error_message = $this->parse_message_response($error_message);
			}
			else {
				$error_message = $response->message;
			}
			$message_args = array(
				'success' => false,
				'type' => $args['batch_item_id'],
				'message' => $error_message
			);
		}
		
		return new cfd_message($message_args);
	}

// Preflight loop

	protected function preflight($args) {
		// boost our upper limit
		ini_set('memory_limit', '512M');
		
		$ret = array();
		
		$this->batch_items = $args['batch_items'];

		foreach ($this->batch_items as $key => $group) {
			if (!empty($group)) {
				$ret[$key] = array();
				switch ($key) {
					case 'post_types':
						$ret = array_merge_recursive($ret, $this->preflight_post_types($group));
						break;
					case 'users':
						$ret = array_merge_recursive($ret, $this->preflight_users($group));
						break;
					case 'taxonomies':
						$ret['taxonomies'] = array_merge_recursive($ret['taxonomies'], $this->preflight_taxonomies($group));
						break;
					case 'bookmarks':
						$ret['bookmarks'] = array_merge_recursive($ret['bookmarks'], $this->preflight_bookmarks($group));
						break;
					case 'menus':
						$ret['menus'] = array_merge_recursive($ret['menus'], $this->preflight_menus($group));
						break;
				}
			}
		}
		
		// extras always last
		if (!empty($this->batch_items['extras'])) {
			$ret['extras'] = array_merge_recursive($ret['extras'], $this->preflight_extras($this->batch_items['extras']));
		}
		
		cfd_tmp_dbg('preflight-return-data.txt', $ret, 'print');

		return !empty($ret) ? $ret : false;
	}
	
// Preflight Extras

	protected function preflight_extras($extras) {
		$ret = array();
		foreach ($extras as $extra_id => $data) {
			$callback = $this->get_extra_callback_method($extra_id, 'preflight_check');
			if (!empty($callback)) {
				$ret[$extra_id] = call_user_func($callback, $data, $this->batch_items);
			}
		}
		return $ret;
	}
	
// Preflight Post

	protected function preflight_post_types($post_types) {
		$ret = array();
		
		foreach($post_types as $post_type => $objects) {
			if (!post_type_exists($post_type)) {
				$ret['post_types'][$post_type]['__error__'][] = sprintf(__('Post Type "%s" does not exist on destination. Post types must be managed manually before objects in the type can be transferred.', 'cf-deploy'), $post_type);
				continue;
			}
			if (!empty($objects)) {
				foreach ($objects as $id => $post) {
					$ret['post_types'][$post_type][$id] = $this->preflight_post($post);
				}
			}
		}

		return $ret;
	}

	protected function preflight_post($post) {
		$ret = array();
		
		// check post
		try {
			$local_post = new cfd_post(array('guid' => $post['post']['guid']));
			$ret['__action__'] = 'update';
			if (strtotime($local_post->post_modified) > strtotime($post['post']['post_modified'])) {
				$ret['__warning__'][] = __('Remote post is newer.', 'cf-deploy');
			}
		}
		catch (Exception $e) {
			// this is ok, the post is allowed to not exist
			$ret['__action__'] = 'create';
		}
		
		// check author
		$author_preflight = $this->preflight_user($this->batch_items['users'][$post['author']]);
		switch ($author_preflight['__action__']) {
			case 'create':
				$ret['__notice__'][] = __('Author does not exist on remote system. Author will automatically be created.', 'cf-deploy');
				break;
			case 'update':
				// no notices on this one
				break;
		}
		
		// check taxonomies
		if (!empty($post['taxonomies'])) {
			foreach ($post['taxonomies'] as $tax_type => $terms) {
				if (!empty($terms)) {
					foreach ($terms as $term) {
						$_term = $this->batch_items['taxonomies'][$tax_type][$term];
						$term_preflight = $this->preflight_term($_term);
						switch ($term_preflight['__action__']) {
							case 'create':
								$ret['__notice__'][] = sprintf(__('%s "%s" does not exist on remote system. Taxonomy object will be automatically created.', 'cf-deploy'), $tax_type, $_term['name']);
								break;
							case 'update':
								$ret['__notice__'][] = sprintf(__('Term "%s" differs on remote system and will be updated', 'cf-deploy'), $_term['name']);
								break;
						}
					}
				}
			}
		}
											
		// check post parent
		if (!empty($post['parent'])) {
			try {
				$parent = new cfd_post(array(
					'guid' => $post['parent']['guid']
				));
			}
			catch (Exception $e) {
				$parent_found_in_batch = false;
				foreach ($this->batch_items['post_types'] as $_post_type) {
					if (array_key_exists($post['parent']['guid'], $_post_type)) {
						$parent_found_in_batch = true;
					}
				}
				if (!$parent_found_in_batch) {
					$ret['__error__'][] = __('Remote parent does not exist and parent is not part of this batch', 'cf-deploy');
				}
			}
		}

		// attachment duties
		if ($post['post']['post_type'] == 'attachment' || !empty($post['attachments'])) {
			// verify write access to uploads folder
			$uploads_dir = wp_upload_dir();
			if (!is_dir($uploads_dir['basedir']) || !is_writable($uploads_dir['basedir'])) {
				$error_base = 'Uploads directory is not writable.';
				if ($post_type == 'attachment') {
					$ret['__error__'][] = __($error_base.' File cannot be transferred.', 'cf-deploy');
				}
				else {
					$ret['__error__'][] = __($error_base.' Item&rsquo;s attachments cannot be transfered.', 'cf-deploy');
				}
			}

			// make sure that file responds on other end
			$files = array();
			if ($post['post']['post_type'] == 'attachment') {
				$files[] = $post['file']['url'];
			}
			else {
				foreach ($post['attachments'] as $att) {
					//$files[] = $att['guid'];
					$files[] = $att['url'];
				}
			}

			if (!empty($files)) {
				foreach ($files as $file) {
					// just pull the response headers to make sure the file is present and readable
					// $response = wp_remote_head($file);
					
					// we have no handling of insecure certificates during actual import, use this only when insecure certs
					// are properly handled during import (which will require some fancy-pants filter to change the wp_http class)
					$response = wp_remote_head($file, array(
						'sslverify' => false
					));
					
					if (!empty($response) && !is_wp_error($response) && !empty($response['response'])) {
						if (!empty($response['response']['code']) && !in_array($response['response']['code'], array(200, 201, 202, 300, 302))) {
							$ret['__error__'][] = sprintf(__('A problem was encountered with attachment file "%s". %s: %s', 'cf-deploy'), basename($file), $response['response']['code'], $response['response']['message']);
						}
					}
					elseif (is_wp_error($response)) {
						$ret['__error__'][] = sprintf(__('A problem was encountered with attachment file "%s". Error: %s', 'cf-deploy'), basename($file), $response->get_error_message());
					}
				}
			}
		}
		
		// Check Attachments
		if (!empty($post['attachments'])) {
			foreach ($post['attachments'] as $attachment) {
				if (!empty($this->batch_items['post_types']['attachment'][$attachment['guid']])) {
					$attachment = $this->batch_items['post_types']['attachment'][$attachment['guid']];
					try {
						$attmnt = new cfd_post(array(
							'guid' => $attachment['post']['guid'],
							'shallow_fetch' => true
						));
						$ret['__notice__'][] = sprintf(__('Attachment "%s" found and will be updated if necessary', 'cf-deploy'), $attachment['post']['post_title']);
					}
					catch (Exception $e) {
						$ret['__notice__'][] = sprintf(__('Attachment "%s" does not exist on remote system and will be created', 'cf-deploy'), $attachment['post']['post_title']);
					}
				}
				else {
					$ret['__error__'][] = sprintf(__('Attachment "%s" does not exist on remote system. Item will not transfer.', 'cf-deploy'), $attachment['post_name']);
				}
			}
		}
		
		// Check Featured Image (_thumbnail_id)
		if (!empty($post['featured_image'])) {
			if (empty($post['attachments'][$post['featured_image']['guid']]) && empty($this->batch_items['post_types']['attachment'][$post['featured_image']['guid']])) {
				// check other post types to see if it is attached there
				foreach ($this->batch_items['post_types'] as $post_type => $objects) {
					if ($post_type != 'attachment' && !empty($objects)) {
						foreach ($objects as $object) {
							if (!empty($object['attachments']) && !empty($object['attachments'][$post['featured_image']['guid']])) {
								$fi_found = true;
							}
						}
					}
					if (!empty($fi_found)) {
						break;
					}
				}
				
				if (empty($fi_found)) {
					// see if it already exists on this system
					try {
						$fi = new cfd_attachment(array('guid' => $post['featured_image']['guid']));
					}
					catch(Exception $e) {
						$ret['__error__'][] = sprintf(__('The featured image "%s" on this %s does not exist on remote system and is not attached. %s will not be transfered. You must send the image&rsquo;s parent to send this item.', 'cf-deploy'), 
											basename($post['featured_image']['guid']), $post['post']['post_type'], ucfirst($post['post']['post_type']));
					}
				}
			}
		}

		return $ret;
	}

// Preflight user

	protected function preflight_users($users) {
		$ret = array();
		
		foreach ($users as $id => $user) {
			$ret['users'][$id] = $this->preflight_user($user);
		}

		return $ret;
	}
	
	protected function preflight_user($user) {
		$ret = array();
		
		$u = get_user_by('login', $user['user_login']);
		if (empty($u)) {
			$ret['__action__'] = 'create';
			// check for user email conflict for creating new user
			$u = get_user_by('email', $user['user_email']);
			if (!empty($u)){
				$ret['__error__'][] = sprintf(__('Cannot create user. A user named "%s" already exists on the remote system with the same email address "%s".', 'cf-deploy'), 
									$u->user_nicename, $user['user_email']);
			}
		}
		else {
			$ret['__action__'] = 'update';
		}
		
		return $ret;
	}

// Preflight Taxonomies

	protected function preflight_taxonomies($taxonomies) {
		$ret = array();
		
		foreach ($taxonomies as $tax_type => $terms) {
			if (!taxonomy_exists($tax_type)) {
				$ret[$tax_type]['__error__'] = sprintf(__('Taxonomy "%s" does not exist on remote system. Taxonomies must be managed manually. '.
													'Please update remote system before transfer can occur.', 'cf-deploy'), $tax_type);
				continue;
			}
			if (!empty($terms)) {
				$ret[$tax_type] = array();
				foreach ($terms as $id => $term) {
					$ret[$tax_type][$id] = $this->preflight_term($term);
				}
			}
		}
		
		return $ret;
	}
	
	protected function preflight_term($term) {
		$ret = array();
		
		if (term_exists($term['slug'])) {
			$local_term = get_term_by('slug', $term['slug'], $term['taxonomy']);
			if ($term['name'] != $local_term->name || $term['description'] != $local_term->description) {
				$ret['__action__'] = 'update';
			}
			else {
				$ret['__action__'] = 'no_change';
			}
		}
		else {
			$ret['__action__'] = 'create';
		}
		
		return $ret;
	}

// Preflight Bookmarks

	protected function preflight_bookmarks($bookmarks) {
		$ret = array();
		
		foreach ($bookmarks as $id => $bookmark) {
			$ret[$id] = $this->preflight_bookmark($bookmark);
		}
		
		return $ret;
	}
	
	protected function preflight_bookmark($bookmark) {
		$ret = array();

		$local_bookmark = $this->get_bookmark_by_url($bookmark['link_url']);							
		if (!empty($local_bookmark)) {
			$ret['__action__'] = 'update';
		}
		else {
			$ret['__action__'] = 'create';
		}
		
		// check bookmark categories
		if (!empty($bookmark['link_category'])) {
			foreach ($bookmark['link_category'] as $cat_slug) {
				$category = $this->batch_items['taxonomies']['link_category'][$cat_slug];
				if (term_exists($category['slug'], 'link_category')) {
					$local_term = get_term_by('slug', $category['slug'], 'link_category');
					if ($category['name'] != $local_term->name || $category['description'] != $local_term->description) {
						$ret['__notice__'][] = sprintf(__('Link category "%s" will be updated.', 'cf-deploy'), $category['name']); 
					}
				}
				else {
					$ret['__notice__'][] = sprintf(__('Link category "%s" does not exist and will be created.', 'cf-deploy'), $category['name']);
				}
				
			}
		}
		
		return $ret;
	}
	
// Preflight Menus

	protected function preflight_menus($menus) {
		$ret = array();
		
		foreach ($menus as $id => $menu) {
			if (is_nav_menu($id)) {
				$local_menu = wp_get_nav_menu_object($id);
				if ($menu['menu']['name'] != $local_menu->name || $menu['menu']['description'] != $local_menu->description || $menu['count'] != $local_menu->count) {
					$ret[$id]['__action__'] = 'update';
				}
			}
			else {
				$ret[$id]['__action__'] = 'create';
			}
			
			// check menu item targets
			if (!empty($menu['items'])) {
				if (defined('RAMP_DEBUG') && RAMP_DEBUG) {
					cfd_tmp_dbg('menu_items.txt', $menu['items'], 'print');
				}
				foreach ($menu['items'] as $item) {
					$ret[$id] = array_merge($ret[$id], $this->preflight_menu_item($item));
				}
			}
			else {
				$ret[$id]['__notice__'][] = __('Menu has no menu items.', 'cf-deploy');
			}
		}

		return $ret;
	}
	
	protected function preflight_menu_item($item) {
		$ret = array();
		
		switch ($item['type']) {
			case 'post_type':
				try {
					$parent = new cfd_post(array(
						'guid' => $item['parent']['guid']
					));
				}
				catch (Exception $e) {
					$parent_found_in_batch = false;
					if (!empty($this->batch_items['post_types'])) {
						foreach ($this->batch_items['post_types'] as $_post_type) {
							if (array_key_exists($item['parent']['guid'], $_post_type)) {
								$parent_found_in_batch = true;
							}
						}
					}
					if (!$parent_found_in_batch) {
						$ret['__error__'][] = sprintf(__('Menu item target %s "%s" does not exist and parent is not part of this batch', 'cf-deploy'),
						 								$item['parent']['post_type'], $item['parent']['post_title']);
					}
				}
				break;
			case 'taxonomy':
				if (!taxonomy_exists($item['term']['taxonomy'])) {
					$ret['__error__'][] = sprintf(__('Taxonomy "%s" does not exist on remote system. Taxonomies must be managed manually. '.
													'Please update remote system before transfer can occur.', 'cf-deploy'), $item['term']['taxonomy']);
					continue;
				}	
				
				if (!term_exists($item['term']['slug'], $item['term']['taxonomy']) && empty($this->batch_items['taxonomies'][$item['term']['taxonomy']][$item['term']['slug']])) {
					$ret['__error__'][] = sprintf(__('Term "%s" (%s) does not exist on remote system and is not part of this batch. Menu will not transfer.', 'cf-deploy'), $item['term']['slug'], $item['term']['taxonomy']);
				}
				
				break;
			case 'custom':
				// no checks needed?
				break;
		}
		
		return $ret;
	}

// Import Messages

	/**
	 * Log an import message
	 *
	 * @param string $group 
	 * @param string $type 
	 * @param string $message 
	 * @return bool
	 */
	public function add_import_message($group, $type, $message) {
		$_msg[$type] = array($message);

		if (strpos($group, '.') != false) {
			// take period separated string as the definition of a sub-array
			$parts = array_reverse(explode('.', $group));
			foreach ($parts as $key => $part) {
				if ($key+1 == count($parts)) {
					$group = $part;
				}
				else {
					$_msg = array($part => $_msg);
				}
			}
		}		

		if (empty($this->batch_import_messages[$group])) {
			$this->batch_import_messages[$group] = array();
		}

		return ($this->batch_import_messages[$group] = array_merge_recursive($this->batch_import_messages[$group], $_msg));
	}
	
	public function get_import_messages() {
		return $this->batch_import_messages;
	}
	
// Store rollback state

	/**
	 * Log an item's rollback state to the import post's post_content
	 *
	 * @param array $state_change 
	 * @return bool/int - bool false on failure, int post-id on success
	 */
	public function log_item_change($state_change) {
		if (empty($this->batch_import_id)) {
			return false;
		}

		$import_post = get_post($this->batch_import_id);

		$rollback_state = unserialize($import_post->post_content);
		if (!is_array($rollback_state)) {
			$rollback_state = array();
		}
		$rollback_state = array_merge_recursive($rollback_state, $state_change);
	
		cfd_tmp_dbg('rollback-state.txt', $rollback_state, 'print');
	
		return wp_update_post(array(
			'ID' => $import_post->ID,
			'post_content' => serialize($rollback_state)
		));
	}

// Rollback Import

	/**
	 * Receive a notification that an import was rolled back
	 * Add meta to the batch that sent the data
	 *
	 * @param array $args 
	 * @return object cfd_message
	 */
	protected function rollback_notify_source($args) {		
		$result = add_post_meta(intval($args['batch_id']), '_batch_rolled_back', array(
			'import_id' => intval($args['import_id']),
			'server' => esc_url($args['batch_import_server'])
		));
		
		if ($result) {
			$ret_args = array(
				'success' => true,
				'type' => 'rollback-source-notify',
				'message' => 'Source notified'
			);
		}
		else {
			$ret_args = array(
				'success' => false,
				'type' => 'rollback-source-notify',
				'message' => 'Source notification failed'
			);
		}
		return new cfd_message($ret_args);
	}

	/**
	 * Ajax response method to rollback batch
	 *
	 * @param array $args 
	 * @return object cfd_message
	 */
	protected function ajax_rollback_import($args) {
		$import_id = intval($args['import_id']);
		
		if (empty($import_id)) {
			return new cfd_message(array(
				'success' => false,
				'type' => 'ajax-rollback-import',
				'message' => '<div class="error message"><p>'.__('Empty import_id. Cannot perform rollback.', 'cf-deploy').'</p></div>'
			));
		}
		
		$ret = $this->rollback_import($import_id);
		
		// simplify return message
		if ($ret->success == true) {
			$source = get_post_meta($import_id, '_batch_source', true);
			$batch_id = get_post_meta($import_id, '_batch_id', true);
			
			$params = array(
				'server' => $this->options['remote_server'][0]['address'],
				'auth_key' => $this->options['remote_server'][0]['key'],
				'method' => 'rollback_notify_source',
				'args' => array(
					'batch_id' => $batch_id,
					'batch_import_id' => $import_id,
					'batch_import_server' => get_bloginfo('url')
				)
			);

			$response = $this->send($params);

			if ($response->success) {
				$source_notified = true;
			}
			
			wp_delete_post($import_id, true);
			if (empty($source_notified)) {
				$ret->message = '<div class="message warning"><p>'.__('Batch Rollback Successful, but notification to source server failed.', 'cf-deploy').'</p></div>';
			}
			else {
				$ret->message = '<div class="message notice"><p>'.__('Batch Rollback Successful', 'cf-deploy').'</p></div>';
			}
		}
		else {
			$ret->message = '<div class="message error"><p>'.__('Batch Rollback Failed: ', 'cf-deploy').$this->parse_message_response($ret->message).'</p></div>';
		}
		return $ret;
	}
	
	/**
	 * Rollback an import
	 *
	 * @param array $import_id 
	 * @return object cfd_message
	 */
	protected function rollback_import($import_id) {
		$this->in_rollback = true;
		$import_post = get_post($import_id);
		
		if (empty($import_post)) {
			return cfd_message(array(
				'success' => false,
				'type' => 'rollback-import',
				'message' => '<div class="message error"><p>'.sprintf(__('Import history not found for import_id "%s".', 'cf-deploy'), $import_id).'</p></div>'
			));
		}

		$rollback_success = true;
		$previous_state = unserialize($import_post->post_content);

		// lets do this backwards to get rid of dependents before getting rid of dependencies
		if (!empty($previous_state)) {
			$_previous_state = array_reverse($previous_state);
			foreach ($_previous_state as $group_type => $objects) {
				if (!empty($objects)) {
					$method = 'rollback_'.$group_type;
					if (method_exists($this, $method)) {
						if ($this->$method($objects) === false) {
							$rollback_success = false;
						}
					}
				}
			}
		}
		else {
			$rollback_success = false;
			return new cfd_message(array(
				'success' => false,
				'type' => 'rollback-batch',
				'message' => '<div class="message error"><p>'.sprintf(__('No items to rollback in import "%s".', 'cf-deploy'), $import_id).'</p></div>'
			));
		}
		
		cfd_tmp_dbg('import_rollback_messages.txt', $this->batch_import_messages, 'print');
		
		return new cfd_message(array(
			'success' => $rollback_success,
			'type' => 'rollback-import',
			'message' => $this->get_import_messages()
		));
	}
	
	protected function rollback_post_types($post_types) {
		$success = true;
		
		foreach ($post_types as $post_type => $posts) {
			if (!empty($posts)) {
				// reverse the array so that we delete children first
				$_posts = array_reverse($posts);
				foreach ($_posts as $guid => $post) {
					if ($post == 'new') {
						$_post = new cfd_post(array(
							'guid' => $guid
						));
						wp_delete_post($_post->post->ID, true);
					}
					else {
						if ($post['post']['post_type'] == 'attachment') {
							if ($this->import_post($post) == false) {
								$success = false;
								$this->add_import_message('post_types.'.$post_type, '__error__', sprintf(__('Unknown error. Unable to revert post "%s" to revision "%s".', 'cf-deploy'), $guid, $post));
							}
						}
						else {
							$result = wp_restore_post_revision($post);
							if (empty($result)) {
								$success = false;
								$this->add_import_message('post_types.'.$post_type, '__error__', sprintf(__('Unknown error. Unable to revert post "%s" to revision "%s".', 'cf-deploy'), $guid, $post));
							}
						}
					}
				}
			}
		}
		
		return $success;
	}
	
	protected function rollback_taxonomies($taxonomies) {
		$success = true;
		
		foreach ($taxonomies as $tax_type => $terms) {
			if (!empty($terms)) {
				// reverse the array so that we delete children first
				#$_terms = array_reverse($terms);
				foreach ($terms as $term_slug => $term) {
					if ($term == 'new') {
						$_term = get_term_by('slug', $term_slug, $tax_type);
						wp_delete_term($_term->term_id, $tax_type, array());
					}
					elseif ($this->import_term($term) == false) {
						$success = false;
					}
				}
			}
		}
		
		return $success;
	}
	
	protected function rollback_users($users) {
		$success = true;
		
		foreach ($users as $user_login => $user) {
			if ($user == 'new') {
				$user = get_user_by('login', $user_login);
				// reassign posts to user 0, just to be safe and so that 
				// we can get removal confirmation later in this process
				wp_delete_user($user->ID, 0); 
			}
			else {
				if ($this->import_user($user) === false) {
					$success = false;
				}
			}
		}
		
		return $success;
	}
	
	protected function rollback_menus($menus) {
		$success = true;
		
		foreach ($menus as $menu_id => $menu) {
			if ($menu == 'new') {
				$_menu = new cfd_menu($menu_id);
				wp_delete_nav_menu($_menu->menu->term_id);
			}
			else {
				if ($this->import_menu($menu) == false) {
					$success = false;
				}
			}
		}
		
		return $success;
	}
	
	protected function rollback_bookmarks($bookmarks) {
		$success = true;
		
		foreach ($bookmarks as $bookmark_url => $bookmark) {
			if ($bookmark == 'new') {
				$_bookmark = $this->get_bookmark_by_url($bookmark_url);
				wp_delete_link($_bookmark->link_id);
			}
			else {
				if ($this->import_bookmark($bookmark) === false) {
					$success = false;
				}
			}
		}
		
		return $success;
	}

// Import Batch

	protected function import_batch_open($args) {
		$post_array = array(
			'ID' => 0,
			'post_type' => CF_DEPLOY_POST_TYPE,
			'post_title' => $args['batch_title'],
			'post_status' => 'import'
		);
		$import_id = wp_insert_post($post_array);
		
		if (!empty($import_id) && !is_wp_error($import_id)) {
			add_post_meta($import_id, '_batch_id', $args['batch_id']);
			add_post_meta($import_id, '_batch_source', $args['source']);
			return new cfd_message(array(
				'success' => true,
				'type' => 'batch-import-id',
				'message' => $import_id
			));
		}
		else {
			if (is_wp_error($import_id)) {
				$errorstring = $import_id->get_error_message();
			}
			else {
				$errorstring = sprintf(__('An unknown error has occured. Could not start batch import for batch_id "%s".', 'cf-deploy'), $args['batch_id']);
			}	
			return new cfd_message(array(
				'success' => false,
				'type' => 'batch-import-id',
				'message' => $errorstring
			));
		}
	}
	
	protected function import_batch_close($args) {
		$import_id = wp_update_post(array(
			'ID' => $args['batch_import_id']['message']
		));
		add_post_meta($args['batch_import_id']['message'], '_batch_import_complete', true);
		
		if (!empty($import_id) && !is_wp_error($import_id)) {
			return new cfd_message(array(
				'success' => true,
				'type' => 'import-batch-close',
				'message' => __('Batch import complete', 'cf-deploy')
			));
		}
		else {
			if (is_wp_error($import_id)) {
				$errorstring = $import_id->get_error_message();
			}
			else {
				$errorstring = sprintf(__('An unknown error has occured. Could not close batch import for batch_id "%s".', 'cf-deploy'), $args['batch_id']);
			}
			return new cfd_message(array(
				'success' => false,
				'type' => 'import-batch-close',
				'message' => $errorstring
			));
		}
	}

	protected function import_batch($args) {
		cfd_tmp_dbg('import-data-received.txt', $batch_data, 'print');
		
		$success = true;

		$this->batch_import_id = (!empty($args['batch_import_id']['message']) ? $args['batch_import_id']['message'] : $args['batch_import_id']);
		$this->batch_session_token = $args['batch_session_token'];
		
		if (!empty($args['batch'])) {
			$this->batch_items = $args['batch'];
			
			foreach ($args['batch'] as $group_type => $batch_group) {
				$method = 'import_'.$group_type;
				if (method_exists($this, $method) && !empty($batch_group)) {
					if ($this->$method($batch_group) === false) {
						$success = false;
					}
				}
				elseif (empty($batch_group)) {
					$this->add_import_message($group_type, '_note', sprintf(__('No items of "%s" to process', 'cf-deploy'), $group_type));
				}
				elseif (!method_exists($this, $method)) {
					$success = false;
					$this->add_import_message($group_type, '__error__', sprintf(__('<b>!!!</b> Method not found to import "%s" data.', 'cf-deploy'), $group_type));
				}
			}
		}
		
		if (!$success) {
			add_post_meta($args['batch_import_id'], '_batch_import_complete', false);
			add_post_meta($args['batch_import_id'], '_batch_import_messages', $this->get_import_messages());
		}
		
		cfd_tmp_dbg('import_messages.txt', $this->batch_import_messages, 'print');
		
		$ret = new cfd_message(array(
			'success' => $success,
			'type' => 'import-batch-resopnse',
			'message' => $this->get_import_messages()
		));
		
		return $ret;
	}

// Import Post Types
	
	protected function import_post_types($post_types) {		
		$messages = array();

		if (!empty($post_types)) {
			foreach ($post_types as $post_type => $posts) {
				if (!post_type_exists($post_type)) {
					$error = sprintf(__('Post Type "%s" does not exist. Post types must be managed manually first. No "%s" posts imported.', 'cf-deploy'), $post_type, $post_type);
					$this->add_import_message('post_types.'.$post_type, '__error__', $error);
					continue;
				}
				
				if (empty($posts)) {
					// this code will most likely never be reached
					$notice = sprintf(__('No posts to insert for "%s" post type.', 'cf-deploy'), $post_type);
					$this->add_import_message('post_types.'.$post_type, '__notice__', $notice);
					$processed++;
					continue;
				}

				if ($this->import_posts($post_type, $posts)) {
					$processed++;
				}
			}
		}

		return ($processed == count($post_types));
	}
	
	protected function import_posts($post_type, $posts) {
		$processed = 0;
		
		foreach ($posts as $post) {
			if ($this->import_post($post)) {
				$processed++;
			}
		}

		return ($processed == count($posts));
	}
	
	/**
	 * Import/Update post
	 * Will determine wether import or update is best option
	 *
	 * @param array $args 
	 * @return post_id
	 */
	protected function import_post($post) {
		cfd_tmp_dbg('post-import.txt', $post, 'print');
				
		try {
			$local_post = new cfd_post(array('guid' => $post['post']['guid']));
			$update = true;
		}
		catch (Exception $e) {
			// s'okay
			$update = false;
		}
				
		// Parent
		if (!empty($post['parent'])) {
			try {
				$post_parent = new cfd_post(array(
					'guid' => $post['parent']['guid'],
					'shallow_fetch' => true
				));
			}
			catch (Exception $e) {
				$error = sprintf(__('%s "%s" could not be inserted. Parent post does not exist.', 'cf-deploy'), $this->humanize($post['post']['post_type']), esc_html($post['post']['post_title']));
				$this->add_import_message('post_types.'.$post['post']['post_type'], '__error__', $error);
				return false;
			}
		}

		// Author
		$local_user = get_user_by('login', $post['author']);
		if (empty($local_user) && !empty($this->batch_items['users'][$post['author']])) {
			// once we start doing imports in parts this'll not be possible
			$author_id = $this->import_user($this->batch_items['users'][$post['author']]);			
		}
		else {
			$author_id = $local_user->ID;
		}
		
		if (empty($author_id)) {
			$error = sprintf(__('User "%s" does not exist on remote server. Post cannot be imported', 'cf-deploy'), $post['author']);
			$this->add_import_message('post_types.'.$post['post']['post_type'], '__error__', $error);
			return false;
		}

		// Taxonomies - this is a little redundant to the full tax-import routine, but needed for post specific messaging
		$post_category = $tax_input = array();
		if (!empty($post['taxonomies'])) {
			foreach ($post['taxonomies'] as $tax_type => $tax_terms) {
				if (!taxonomy_exists($tax_type)) {
					$error = sprintf(__('Post "%s" cannot be inserted. Taxonomy "%s" does not exist on this server.', 'cf-deploy'), esc_html($post['post_title']), $tax_type);
					$this->add_import_message('post_types.'.$post['post']['post_types'], '__error__', $error);
					return false;
				}
				$taxonomy = get_taxonomy($tax_type);
				if ($tax_type != 'category') {
					// heirarchal taxonomy types are imported as arrays, non hierarchal as comma separated strings
					$tax_input[$taxonomy->name] = ($taxonomy->hierarchical == true ? array() : '');
				}
				
				if (!empty($tax_terms)) {
					foreach ($tax_terms as $term) {
						if (!term_exists($term, $tax_type)) {
							if (!empty($this->batch_items['taxonomies'][$tax_type][$term])) {
								$ret = $this->import_term($this->batch_items['taxonomies'][$tax_type][$term]);
								if (is_wp_error($ret)) {
									$error = sprintf(__('Post "%s" cannot be inserted. Term "%s" could not be imported.', 'cf-deploy'), esc_html($post['post']['post_title']), $term);
									$this->add_import_message('post_types.'.$post['post']['post_type'], '__error__', $error);
									return $false;
								}
							}
							else {
								$error = sprintf(__('Post "%s" cannot be inserted. Term "%s" does not exist on remote system', 'cf-deploy'), esc_html($post['post']['post_title']), $term);
								$this->add_import_message('post_types.'.$post['post']['post_type'], '__error__', $error);
								return false;
							}
						}
						$term = get_term_by('slug', $term, $tax_type);

						if ($tax_type == 'category') {
							$post_category[] = $term->term_id;
						}
						elseif ($taxonomy->hierarchical == true) {
							$tax_input[$taxonomy->name][] = $term->term_id;
						}
						else {
							$tax_input[$taxonomy->name] .= (!empty($tax_input[$taxonomy->name]) ? ',' : '').$term->slug;
						}
					}
				}
			}
		}
		
		// Insert Post
		$_post = $post['post'];
		unset($_post['ID']);
		$_post['post_author'] = $author_id;
		if (!empty($post_parent)) {
			$_post['post_parent'] = $post_parent->ID;
		}
		$_post['post_category'] = $post_category;
		$_post['tax_input'] = $tax_input;
		
		$this->add_post_date_filter(array(
			'post_date' => $_post['post_date'],
			'post_date_gmt' => $_post['post_date_gmt'],
			'post_modified' => $_post['post_modified'],
			'post_modified_gmt' => $_post['post_modified_gmt']			
		));
		
		if ($update) {
			$_post['ID'] = $local_post->ID;
			$insert_post_id = wp_update_post($_post);
		}
		else {
			$insert_post_id = wp_insert_post($_post);
		}
		
		$this->remove_post_date_filter();

		// Attachment Duties
		if ($post['post']['post_type'] == 'attachment') {
			// don't mess with actual files on rollback
			if (!$this->in_rollback) {
				// determine upload folder date. It doesn't seem that we can rely on the post-date to correlate to 
				// the upload folder date, so if the files were organized by date then grab that date from the 
				// pathinfo of the _wp_attached_file postmeta value
				$upload_folder_date = null;
				$pathinfo = pathinfo($post['meta']['_wp_attached_file']);
				if (preg_match('|^\d{4}/\d{2}$|', $pathinfo['dirname'])) {
					$upload_folder_date = $pathinfo['dirname'];
				}
				
				// Transfer file
				$uploaded = $this->import_file($post, $insert_post_id, $upload_folder_date);
				if (is_wp_error($uploaded)) {
					$error = sprintf(__('Import of file "%s" failed: ', 'cf-deploy'), $post['file']['url']).$uploaded->get_error_message();
					$this->add_import_message('post_types.'.$post['post']['post_type'], '__error__', $error);
					return false;
				}
			}
			
			// handle featured image
			$featured_image_spots = $this->get_featured_image_data($post['post']['guid']);
			if (!empty($featured_image_spots)) {
				foreach ($featured_image_spots as $fi_post_data) {
					update_post_meta($fi_post_data['local_post_id'], '_thumbnail_id', $insert_post_id);
				}
			}
		}


		if (is_wp_error($insert_post_id)) {
			$error = sprintf(__('Import of post "%s" failed. Error: %s', 'cf-deploy'), esc_html($post['post']['post_title']), $insert_post_id->get_error_message());
			$this->add_import_message('post_types.'.$post['post']['post_type'], '__error__', $error);
			return false;
		}
		
		// Postmeta
		if ($update) {
			// get existing to sniff for deletions
			$meta = get_metadata('post', $insert_post_id);
			if (!empty($meta)) {
				foreach ($meta as $key => $value) {
					if (!array_key_exists($key, $post['meta'])) {
						delete_post_meta($insert_post_id, $key);
					}
				}
			}
		}
		
		if (!empty($post['meta'])) {
			// add/update from source
			foreach ($post['meta'] as $key => $value) {
				if (!get_post_meta($insert_post_id, $key)) {
					add_post_meta($insert_post_id, $key, $value);
				}
				else {
					update_post_meta($insert_post_id, $key, $value);
				}
			}
		}
		
		if (!empty($post['featured_image'])) {
			// check to see if the item already exists and associate it if we can,
			// else shove it in a transient for later so we can process it when the
			// attachments come in
			try {
				$featured_image = new cfd_attachment(array(
					'guid' => $post['featured_image']['guid'],
					'shallow_fetch' => true
				));
				update_post_meta($insert_post_id, '_thumbnail_id', $featured_image->id());				
			}
			catch(Exception $e) {
				$saved = $this->hold_featured_image_data($insert_post_id, $post['featured_image']);
			}
		}
				
		$item_change['post_types'][$post['post']['post_type']][$post['post']['guid']] = 'new';
		if (!empty($local_post)) {
			if ($post['post']['post_type'] == 'attachment') {
				$revision = array($local_post->get_data_for_transit());
				array_walk_recursive($revision, array($this, 'object_to_array'));
				$revision = current($revision);
				
				$revision['author'] = $revision['author']['user_login'];
				// put the taxonomies in place
				$taxes = $revision['taxonomies'];
				$revision['taxonomies'] = array();
				if (!empty($taxes)) {
					foreach ($taxes as $tax_type => $terms) {
						if (!empty($terms)) {
							// store just the slugs with the post data
							$revision['taxonomies'][$tax_type] = array_keys($terms);
						}
					}
				}
				
				// We're not gonna try to rollback or handle differences in the actual file, so put the new data in the revision content
				$revision['file'] = $post['file'];
				$item_change['post_types'][$post['post']['post_type']][$post['post']['guid']] = $revision;
			}
			else {
				// revision
				$revision = wp_get_post_revisions($local_post->post->ID, array('showposts' => 1));
				$item_change['post_types'][$post['post']['post_type']][$post['post']['guid']] = current($revision)->ID;
				
				// handle differences in attachments, disassociate attachments that have been removed from this post
				if (!empty($local_post->attachments) && count($local_post->attachments)) {
					foreach ($local_post->attachments as $guid => $attachment) {
						if (!array_key_exists($guid, $post['attachments'])) {
							// attachment is no longer part of this post, clear its parent association
							wp_update_post(array(
								'ID' => $attachment->post->ID,
								'post_parent' => 0
							));
						}
					}
				}
			}
		}
		
		$this->log_item_change($item_change);
		$this->add_import_message('post_types.'.$post['post']['post_type'], '__notice__', sprintf(__('Post "%s" successfully imported', 'cf-deploy'), esc_html($post['post']['post_title'])));
		
		// cleanup
		if (!empty($local_post)) {
			unset($local_post);
		}
		
		return true;
	}
	
	protected function hold_featured_image_data($local_post_id, $data) {
		$transient = $this->get_featured_image_transient();
		if (empty($transient)) {
			$transient = array();
		}
		$transient[$data['guid']][] = compact('local_post_id', 'data');
		$result = set_transient('cfd_featured_images_'.$this->batch_session_token, $transient, 60*5);
		return $result;
	}
	
	protected function get_featured_image_data($attachment_guid) {
		$transient = $this->get_featured_image_transient();
		$data = false;
		if (!empty($transient[$attachment_guid])) {
			$data = $transient[$attachment_guid];
		}
		return $data;
	}

	protected function get_featured_image_transient() {
		return get_transient('cfd_featured_images_'.$this->batch_session_token);
	}
	
	protected function import_file($post, $insert_post_id, $date) {
		$upload = wp_upload_dir($date);

		// nuke the old file so the new one can claim its name
		$existing_file_check = trailingslashit($upload['basedir']).$post['meta']['_wp_attached_file'];
		if (is_file($existing_file_check)) {
			@unlink($existing_file_check);
		}
		
		$url = $post['file']['url'];
		$file_name = basename($post['meta']['_wp_attached_file']);
		$upload = wp_upload_bits($file_name, 0, '', $date);
				
		/* -- taken from wp-importer --*/
		if ( $upload['error'] ) {
			return new WP_Error( 'upload_dir_error', $upload['error'] );
		}

		// fetch the remote url and write it to the placeholder file
		$headers = wp_get_http($url, $upload['file']);

		//Request failed
		if ( ! $headers ) {
			@unlink($upload['file']);
			return new WP_Error( 'import_file_error', __('Remote server did not respond', 'wordpress-importer') );
		}
		
		// make sure the fetch was successful
		if ( $headers['response'] != '200' ) {
			@unlink($upload['file']);
			return new WP_Error( 'import_file_error', sprintf(__('Remote file returned error response %1$d %2$s', 'wordpress-importer'), $headers['response'], get_status_header_desc($headers['response']) ) );
		}
		elseif ( isset($headers['content-length']) && filesize($upload['file']) != $headers['content-length'] ) {
			@unlink($upload['file']);
			return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'wordpress-importer') );
		}

		$max_size = $this->max_attachment_size();
		if ( !empty($max_size) and filesize($upload['file']) > $max_size ) {
			@unlink($upload['file']);
			return new WP_Error( 'import_file_error', sprintf(__('Remote file is too large, limit is %s', size_format($max_size), 'wordpress-importer')) );
		}
		/*-- end taken from wp-importer --*/
		wp_update_attachment_metadata($insert_post_id, wp_generate_attachment_metadata($insert_post_id, $upload['file']));
		return true;
	}
	
	function max_attachment_size() {
		// can be overridden with a filter - 0 means no limit
		return apply_filters('import_attachment_size_limit', 0);
	}

// Import Bookmarks
	
	/**
	 * Import a series of bookmarks
	 *
	 * @param array $bookmarks 
	 * @return array
	 */
	protected function import_bookmarks($bookmarks) {
		$processed = 0;
		if (!empty($bookmarks)) {
			foreach ($bookmarks as $bookmark) {
				if ($this->import_bookmark($bookmark) === true) {
					$processed++;
				}
			}
		}
		return ($processed == count($bookmarks));
	}
	
	/**
	 * Import a single bookmark
	 *
	 * @param array $bookmark 
	 * @return array
	 */
	protected function import_bookmark($bookmark) {
		$local_bookmark = $this->get_bookmark_by_url($bookmark['link_url']);
		
		// process categories
		if (!empty($bookmark['link_category'])) {
			foreach ($bookmark['link_category'] as &$link_category) {
				$cat = get_term_by('slug', $link_category, 'link_category');

				// all taxonomies are imported before bookmarks, so this condition should never come up
				// even if it does, once these go atomic this'll be moot
				if (empty($cat) && !empty($this->batch_items['taxonomies']['link_category'][$link_category])) {
					// link-category not present, perform import
					$cat_args = $this->import_term($link_category);
					$cat = get_term($cat_args['term_id'], $link_category['taxonomy']);
				}
								
				if (empty($cat)) {
					$error = sprintf(__('Could not insert term category "%s" for bookmark "%s".', 'cf-deploy'), $link_category['name'], $bookmark['link_name']);
					$this->add_import_message('bookmarks', '__error__', $error);
					return false;
				}
				else {
					$link_category = $cat->term_id;
				}
			}
		}
				
		if (!empty($local_bookmark)) {
			$bookmark['link_id'] = $local_bookmark->link_id;
			$result = wp_update_link($bookmark);
		}
		else {
			unset($bookmark['link_id']);
			$result = wp_insert_link($bookmark);
		}
		
		$item_change['bookmarks'][$bookmark['link_url']] = 'new';
		if (!empty($local_bookmark)) {
			foreach ($local_bookmark->link_category as &$_link_cat) {
				$_cat = get_term($_link_cat, 'link_category');
				$_link_cat = $_cat->slug;
			}
			$item_change['bookmarks'][$bookmark['link_url']] = get_object_vars($local_bookmark);
		}
		$this->log_item_change($item_change);
		
		if (!is_wp_error($result)) {
			$this->add_import_message('bookmarks', '__notice__', sprintf(__('Bookmark "%s" imported.', 'cf-deploy'), $bookmark['link_name']));
			return true;
		}
		else {
			$this->add_import_message('bookmarks', '__error__', sprintf(__('Bookmark "%s" not imported. Error: %s', 'cf-deploy'), $bookmark['link_name'], $result->get_error_message()));
			return false;
		}
	}

// Import Users
	
	/**
	 * Import a series of users
	 *
	 * @param array $users 
	 * @return array
	 */
	protected function import_users($users) {
		$processed = 0;
		if (!empty($users)) {
			foreach ($users as $user) {
				if ($this->import_user($user)) {
					$processed++;
				}
			}
		}
		
		return ($processed == count($users));
	}
	
	/**
	 * Import a single user
	 * 
	 * @param array $user
	 * @return array
	 */
	protected function import_user($user) {
		$local_user = get_user_by('login', $user['user_login']);
		$local_user_object = new WP_User($local_user->ID);
		
		$update = !empty($local_user) ? true : false;
		
		if (!function_exists('wp_insert_user')) {
			include_once(ABSPATH.'wp-includes/registration.php');
		}
		
		// args used by wp_insert_user & wp_update_user
		// makes for an easy merge and a reminder of just what is handled at that time
		$insert_user_args = array(
			'user_login' => null,
			'user_nicename' => null,
			'user_url' => null,
			'user_email' => null,
			'display_name' => null,
			'nickname' => null,
			'first_name' => null,
			'last_name' => null,
			'description' => null,
			'rich_editing' => null,
			'user_registered' => null,
			'role' => null,
			'use_ssl' => 0,
			'admin_color' => null,
			'comment_shortcuts' => null,
		);
		
		foreach (_wp_get_user_contactmethods() as $contact_method => $contact_method_name) {
			$insert_user_args[$contact_method] = null;
		}
		
		foreach ($insert_user_args as $key => &$arg) {
			if ($key == 'role') {
				$arg = $user['roles'][0];
			}
			else {
				$arg = $user['data'][$key];
			}
		}
		
		if ($update) {
			$local_userdata = get_object_vars(get_userdata($local_user->ID));
			$insert_user_args = array_merge($local_userdata, $insert_user_args);
			unset($insert_user_args['user_pass']);
			$user_id = wp_update_user($insert_user_args);		
		}
		else {
			if (email_exists($user['user_email'])) {
				$this->add_import_message('users', '__error__', sprintf(__('Email address "%s" already exists for another user', 'cf-deploy'), $user['user_email']));
				return false;
			}
			// set generic password for new user
			$insert_user_args['user_password'] = time();
			$user_id = wp_insert_user($insert_user_args);
		}

		if (empty($user_id) || is_wp_error($user_id)) {
			$errstring = sprintf(__('Import failed for user "%s".', 'cf-deploy'), $user['user_nicename']);
			if (is_wp_error($user_id)) {
				$errstring .= ' '.__('Error:', 'cf-deploy').' '.$user_id->get_error_message();
			}
			$this->add_import_message('users', '__error__', $errstring);
			$ret = false;
		}
		else {
			// Set/Update Capabilities & Roles
			$u = new WP_User($user_id);
		
			// set roles, remove all existing and replace with what is being brought in
			foreach ($u->roles as $role) {
				$u->remove_role($role);
			}
			
			foreach ($user['roles'] as $role) {
				$u->add_role($role);
			}
		
			// set caps, remove all existing caps before setting them anew
			$u->remove_all_caps();
			foreach ($user['data'][$user['cap_key']] as $cap => $value) {
				$u->add_cap($cap, (bool) $value);
			}

			$this->add_import_message('users', '__notice__', sprintf(__('User "%s" successfully imported.', 'cf-deploy'), $user['user_login']));
			$ret = true;
		}
		
		$item_change['users'][$user['user_login']] = 'new';
		if (!empty($local_user)) {
			$log_users = array($local_user_object);
			array_walk_recursive($log_users, array($this, 'object_to_array'));
			$item_change['users'][$user['user_login']] = current($log_users);
		}
		$this->log_item_change($item_change);
		
		return $ret;
	}

// Import taxonomies
	
	protected function import_taxonomies($args) {
		$processed = 0;
		if (count($args)) {
			foreach ($args as $tax_type => $terms) {
				if (!taxonomy_exists($tax_type)) {
					$error = sprintf(__('Taxonomy "%s" does not exist on this server. Terms cannot be inserted.', 'cf-deploy'), $tax_type);
					$this->add_admin_message('taxonomy.'.$tax_type, '__error__', $error);
					continue;
				}
				if($this->import_taxonomy_terms($tax_type, $terms)) {
					$processed++;
				}
			}
		}
		
		return ($processed == count($args));
	}
	
	/**
	 * Import a series of terms from a taxonomy
	 *
	 * @param string $taxonomy 
	 * @param array $terms 
	 * @return array
	 */
	protected function import_taxonomy_terms($taxonomy, $terms) {
		$processed = 0;
		if (!empty($terms)) {
			foreach ($terms as $term) {
				if ($this->import_term($term)) {
					$processed++;
				}
			}
		}

		return ($processed == count($terms));
	}
	
	protected function import_term($term) {
		$args = array(
			'parent' => null,
			'slug' => null,
			'description' => null,
		);
		
		if (term_exists($term['slug'])) {
			$local_term = get_term_by('slug', $term['slug'], $term['taxonomy']);
			$args['parent'] = $local_term->parent;
			$args['slug'] = $local_term->slug;
			$args['description'] = $local_term->description;
		}

		if (!empty($term['parent'])) {
			if (!term_exists($term['term_parent_slug'], $term['taxonomy'])) {
				$this->add_import_message('taxonomies.'.$term['taxonomy'], '__error__', sprintf(__('Could not import term "%s" because term parent "%s" is missing.', 'cf-deploy'), $term['name'], $term['term_parent_slug']));
				return false;
			}
			
			$parent = get_term_by('slug', $term['term_parent_slug'], $term['taxonomy']);
			$term['parent'] = $parent->term_id;
		}

		$args = array(
			'parent' => (!empty($parent) ? $parent->term_id : 0),
			'slug' => $term['slug'],
			'description' => $term['description']
		);
		
		if (!empty($local_term)) {
			$args['name'] = $term['name'];
			$result = wp_update_term($local_term->term_id, $term['taxonomy'], $args);
		}
		else {
			$result = wp_insert_term($term['name'], $term['taxonomy'], $args);
		}
		
		// log rollback state
		$item_change['taxonomies'][$term['taxonomy']][$term['slug']] = 'new';
		if (!empty($local_term)) {
			$local_term->term_parent_slug = $parent->slug;
			$item_change['taxonomies'][$term['taxonomy']][$term['slug']] = get_object_vars($local_term);
		}
		$this->log_item_change($item_change);
		
		if (!empty($result) && !is_wp_error($result)) {
			$this->add_import_message('taxonomy.'.$term['taxonomy'], '__notice__', sprintf(__('Imported term "%s".', 'cf-deploy'), $term['name']));		
			return true;
		}
		else {
			$errstring = sprintf(__('Term import failed for term "%s".', 'cf-deploy'), $term['name']);
			if (is_wp_error($result)) {
				$errstring .= ' '.__('Error:', 'cf-deploy').' '.$result->get_error_string();
			}
			$this->add_import_message('taxonomy.'.$term['taxonomy'], '__error__', $errstring);
			return false;
		}
	}

// Import Menus
	
	protected function import_menus($menus) {
		$processed = 0;
		if (!empty($menus)) {
			foreach ($menus as $menu) {
				if($this->import_menu($menu)) {
					$processed++;
				}
			}
		}
		
		return ($processed == count($menus));
	}
	
	protected function import_menu($menu) {
		$_menu = $menu['menu'];
		if (is_nav_menu($menu['menu']['slug'])) {
			$local_menu = wp_get_nav_menu_object($menu['menu']['slug']);
			$menu_id = $local_menu->term_id;
			$update = true;
			
			if (!empty($local_menu)) {
				$local_menu = new cfd_menu($menu['menu']['slug']);
			}
		}
		else {
			$menu_id = 0;
			$update = false;
		}

		$insert_menu_id = wp_update_nav_menu_object($menu_id, array(
			'menu-name' => $menu['menu']['name'],
			'description' => $menu['menu']['description'],
			'slug' => $menu['menu']['slug']
		));
		
		if (is_wp_error($insert_menu_id)) {
			$this->add_import_message('menus', '__error__', sprintf(__('Menu import failed for menu "%s". Error: ', 'cf-deploy'), $menu['menu']['name']).$insert_menu_id->get_error_message());
			return false;
		}
		
		// nuke existing menu items, trust me, its easier this way
		if ($update) {
			// Taken directly from wp_delete_nav_menu, wp-includes/nav-menu.php
			$menu_objects = get_objects_in_term($insert_menu_id, 'nav_menu');
			if ( ! empty( $menu_objects ) ) {
				foreach ( $menu_objects as $item ) {
					wp_delete_post( $item );
				}
			}
		}
		
		// handle menu items
		if (!empty($menu['items'])) {
			$this->menu_item_parent_map = array();
			$processed_items = 0;
			foreach ($menu['items'] as $item) {
				if($this->import_menu_item($item, $insert_menu_id)) {
					$processed_items++;
				}
			}
		}
		
		// log rollback state
		$item_change['menus'][$menu['menu']['slug']] = 'new';
		if (!empty($local_menu)) {
			$item_change['menus'][$menu['menu']['slug']] = $local_menu->get_data_for_transit();
		}
		$this->log_item_change($item_change);

		return ($processed_items == count($menu['items']));
	}
	
	protected function import_menu_item($item, $menu_id) {
		$defaults = array(
			'menu-item-db-id' => 0,
			'menu-item-object-id' => 0,
			'menu-item-object' => $item['object'],
			'menu-item-parent-id' => 0,
			'menu-item-position' => $item['menu_order'],
			'menu-item-type' => $item['type'],
			'menu-item-title' => $item['title'],
			'menu-item-url' => '',
			'menu-item-description' => $item['description'],
			'menu-item-attr-title' => $item['attr_title'],
			'menu-item-target' => $item['target'],
			'menu-item-classes' => (is_array($item['classes']) ? implode(' ', $item['classes']) : ''),
			'menu-item-xfn' => $item['xfn'],
			'menu-item-status' => $item['post_status']
		);
		
		switch ($item['type']) {
			case 'post_type':
				try {
					$local_post = new cfd_post(array(
						'guid' => $item['parent']['guid']
					));
					$args = array(
						'menu-item-object-id' => $local_post->ID,
					);
				}
				catch (Exception $e) {
					$this->add_import_message('menus', '__error__', sprintf(__('Post type item "%s" does not exist. Menu item not imported.', 'cf-deploy'), $item['title']));
					return false;
				}
				break;
			case 'taxonomy':
				$local_term = get_term_by('slug', $item['term']['slug'], $item['term']['taxonomy']);
				if (empty($local_term)) {
					$this->add_import_message('menus', '__error__', sprintf(__('Menu item target term "%s" (%s) not present on remote system. Menu item not imported', 'cf-deploy'), $item['title'], $item['object']));
					return false;
				}				
				$args = array(
					'menu-item-object-id' => $local_term->term_id,
				);
				break;
			case 'custom':
				$args = array(
					'menu-item-url' => $item['url']
				);
				break;
		}
		
		if (!empty($item['menu_item_parent'])) {
			$args['menu-item-parent-id'] = $this->menu_item_parent_map[$item['menu_item_parent']];
		}
		
		$menu_item_args = array_merge($defaults, $args);
		
		$menu_item_insert_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_args);
		
		// set proper post_date & post_modified here
		$this->add_post_date_filter(array(
			'post_date' => $item['post_date'],
			'post_date_gmt' => $item['post_date_gmt'],
			'post_modified' => $item['post_modified'],
			'post_modified_gmt' => $item['post_modified_gmt']			
		));
		$update = wp_update_post(array(
			'ID' => $menu_item_insert_id,
			'post_date' => $item['post_date'],
			'post_date_gmt' => $item['post_date_gmt'],
			'post_modified' => $item['post_modified'],
			'post_modified_gmt' => $item['post_modified_gmt']
		));
		$this->remove_post_date_filter();
		
		// we need to know what the new parent IDs are
		if (!is_wp_error($menu_item_insert_id)) {
			$this->menu_item_parent_map[$item['ID']] = $menu_item_insert_id;
			$this->add_import_message('menus', '__notice__', sprintf(__('Menu item "%s" imported.', 'cf-deploy'), $item['title']));
			return true;
		}
		else {
			$this->add_import_message('menus', '__error__', sprintf(__('Menu item "%s" not imported. Error: ', 'cf-deploy'), $item['title']).$menu_item_insert_id->get_error_message());
			return false;
		}		
	}

// Post Date Import Filter

	/**
	 * wp_update_post will ALWAYS set its own post_modified & post_modified_gmt dates
	 * Set a filter so that we can put our own dates in place
	 * For paranoia-cha-cha-cha reasons we also allow post_date & post_date_gmt items to be set as well
	 *
	 * @param array $args 
	 * @return void
	 */
	protected function add_post_date_filter($args) {
		$this->post_date_import_filter_data = $args;
		add_filter('wp_insert_post_data', array($this, 'post_date_import_filter'), 99, 2);
	}
	
	protected function remove_post_date_filter() {
		$this->post_date_import_filter_data = null;
		remove_filter('wp_insert_post_data', array($this, 'post_date_import_filter'), 99, 2);		
	}
	
	public function post_date_import_filter($data, $postarray) {
		if (!empty($this->post_date_import_filter_data['post_date'])) {
			$data['post_date'] = $this->post_date_import_filter_data['post_date'];
		}
		if (!empty($this->post_date_import_filter_data['post_date_gmt'])) {
			$data['post_date_gmt'] = $this->post_date_import_filter_data['post_date_gmt'];
		}
		if (!empty($this->post_date_import_filter_data['post_modified'])) {
			$data['post_modified'] = $this->post_date_import_filter_data['post_modified'];
		}
		if (!empty($this->post_date_import_filter_data['post_modified_gmt'])) {
			$data['post_modified_gmt'] = $this->post_date_import_filter_data['post_modified_gmt'];
		}
		return $data;
	}

// Data helpers

	/**
	 * Get a bookmark by its url (which this system is using as a guid)
	 * Bookmark APIs aren't that robust so we have to go for the direct SQL
	 * 
	 * @param string $bookmark_url 
	 * @return mixed object/false
	 */
	public function get_bookmark_by_url($bookmark_url) {
		global $wpdb;
		$bookmark = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->links WHERE link_url = %s LIMIT 1", $bookmark_url));
		return get_bookmark($bookmark->link_id);
	}

// Utility

	public function get_plugins($args = array()) {
		$plugins = get_plugins();
		foreach ($plugins as $key => $plugin) {
			if (!is_plugin_active($key)) {
				unset($plugins[$key]);
			}
		}
		return $plugins;
	}

	public function get_last_batch_date() {
		$last = $this->get_batches(array(
			'status' => 'history',
			'posts_per_page' => 1
		));

		$date = date('Y-m-d', strtotime('today - 1 week'));
		if (!empty($last)) {
			$date = mysql2date('Y-m-d', $last[0]->post_date);
		}

		return $date;
	}
	
	public function humanize($str, $titlecase = true, $replace_extras = array()) {
		$find = array('_');
		if (is_array($replace_extras) && !empty($replace_extras)) {
			$find = array_merge($find, $replace_extras);
		}
		$str = str_replace($find, ' ', $str);
		if ($titlecase) {
			$str = ucwords($str);
		}
		return $str;
	}

	/**
	 * Get a list of batches by type
	 *
	 * @param string $type 
	 * @return array
	 */
	protected function get_batches($params = array()) {
		$args = array_merge(array(
			'posts_per_page' => 0,
			'status' => 'active'
		), $params);
		
		switch ($args['status']) {
			case 'history':
				$post_status = 'publish';
				break;
			case 'active':
			default:
				$post_status = 'draft';
				break;
		}
		
		$batches = get_posts(array(
			'post_type' => 'cf-deploy',
			'post_status' => $post_status,
			'posts_per_page' => $args['posts_per_page']
		));
		
		return $batches;
	}
	
// 3rd Party Extras
	
	/**
	 * Consistent method for making callback IDs
	 *
	 * @param string $name 
	 * @return string
	 */
	protected function make_callback_id($name) {
		return sanitize_title_with_dashes($name);
	}

	/**
	 * Register extra callback methods from 3rd party plugins
	 * Accepts both string funcname & array class:methodname syntax
	 *
	 * @param string $id 
	 * @param mixed string/array $callbacks 
	 * @return bool
	 */
	public function register_deploy_callback($name, $description, $callbacks) {
		foreach ($callbacks as $type => $callback) {
			if (is_array($callback) && !method_exists($callback[0], $callback[1])) {
				error_log('Can not register non-existant function for '.$type.': '.implode('::', $callback));
				return false;				
			}
			elseif (!is_array($callback) && !function_exists($callback)) {
				error_log('Can not register non-existant function for '.$type.': '.$callback);
				return false;
			}

			if (!is_callable($callback)) {
				error_log('Can not register non-callable function for '.$type.': '.(is_array($callback) ? implode('::', $callback) : $callback));
				return false;
			}
		}

		return $this->extra_callbacks[$this->make_callback_id($name)] = array_merge(array(
			'name' => $name,
			'description' => $description
		), $callbacks);
	}
	
	/**
	 * Remove an extra callback registered by self::register_deploy_callback()
	 *
	 * @param string $id 
	 * @return bool
	 */
	public function deregister_deploy_callback($name) {
		$id = $this->make_callback_id($name);
		if (!empty($this->extra_callbacks[$id])) {
			unset($this->extra_callbacks[$id]);
			return true;
		}
		return false;
	}
	
	/**
	 * Do import side of extra callbacks
	 *
	 * @param array $args 
	 * @return object cfd_message
	 */
	protected function import_batch_extra($args) {
		list($extra_id, $extra_data) = each($args['batch']);

		$result = $this->do_batch_extra($extra_id, 'receive', $extra_data);

		return new cfd_message(array(
			'success' => $result['success'],
			'type' => $extra_id,
			'message' => $result['message']
		));
	}
	
	protected function do_batch_extra($id, $type, $batch_data) {
		$callback = $this->get_extra_callback_method($id, $type);

		$send_data = array();
		if (!empty($callback)) {
			$send_data = call_user_func($callback, $batch_data);
		}
		
		return $send_data;
	}
	
	/**
	 * Retrieve the relevant callback method for the process that we're running
	 *
	 * @param string $id 
	 * @param string $type 
	 * @return mixed string/array
	 */
	public function get_extra_callback_method($id, $type) {
		$type = $type.'_callback';
		$method = false;
		
		if (!empty($this->extra_callbacks[$id]) && !empty($this->extra_callbacks[$id][$type])) {
			if (is_array($this->extra_callbacks[$id][$type]) && method_exists($this->extra_callbacks[$id][$type][0], $this->extra_callbacks[$id][$type][1])) {
				$_method = $this->extra_callbacks[$id][$type];
			}
			elseif (function_exists($this->extra_callbacks[$id][$type])) {
				$_method = $this->extra_callbacks[$id][$type];
			}
			
			if (is_callable($_method)) {
				$method = $_method;
			}
		}

		return $method;
	}
}

?>