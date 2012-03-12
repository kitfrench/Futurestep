<?php

class cfd_batch extends cfd_common {
	private $post;			# our custom post type object (if is existing batch)	
	private $field_trans = array( # translation table of batch-terms to post-terms
		'ID' => 'ID',
		'date' => 'post_date',
		'data' => 'post_content',
		'title' => 'post_title',
		'description' => 'post_excerpt'
	);
	
	protected $title;		# post->post_title
	protected $description;	# post->post_excerpt
	protected $start_date;	# post->post_modified
	protected $date;		# post->post_date 
	protected $ID;			# post->ID
	protected $data;		# post->post_content
	protected $remote_data;	# no local post equivalent
	protected $author; 		# our custom post type author object
	
	protected $c_data;		# compiled local comparison data
	protected $s_data;		# compiled status data
	protected $r_data;		# returned status data
	protected $p_data; 		# returned preflight data
	
	protected $post_type_batch_ids; 	# helper for pulling out of date scope batch items
	protected $has_preflight_error;		# flag for wether errors were found in preflight routine

	protected $_deploy_data;			# transient var for gathering deploy data
	protected $_sorted_post_ids;		# transient var for sorting post_types by parent
	protected $_sorted_term_ids;		# transient var for sorting terms by parent
	
	/**
	 * Kick it off
	 * 
	 * @throws Exception if insufficient data is given 
	 * @param string $args 
	 */
	public function __construct($args) {
		if (empty($args['start_date']) && !is_int($args['ID'])) {
			throw new Exception(__('Insufficient data to start batch.', 'cf-deploy'));
		}
		
		if (!empty($args['start_date'])) {
			$this->start_date = date('Y-m-d', strtotime($args['start_date']));
		}
		
		if (!empty($args['ID'])) {
			$this->ID = intval($args['ID']);
			$this->get_batch_post();
		}
		
		if (!empty($args['data'])) {
			$this->data = $args['data'];
		}
		
		if (empty($args['ID']) && (isset($args['data']) && empty($args['data']))) {
			throw new Exception(__('Insufficient batch data to start batch', 'cf-deploy'));
		}
		
	}
	
	public function init_comparison_data() {
		# get modified posts
		foreach ($this->get_post_types() as $type) {
			$this->c_data['post_types'][$type] = $this->get_post_type_objects_since($type, $this->start_date);
		}
		
		# get new users
		$this->c_data['users'] = $this->get_users_since($this->start_date);
		
		# taxonomies
		$this->c_data['taxonomies'] = $this->get_taxonomies();
		
		# links
		$this->c_data['bookmarks'] = $this->get_bookmarks();
		
		# menus
		$this->c_data['menus'] = $this->get_menus();
	}
	
// Batch Data Operations
	
	/**
	 * Parse out a post into its parts across the deploy data array
	 * Prevents duplicate data from being sent & helps prioritize import
	 *
	 * @throws Exception if post_id isn't a valid post
	 * @param int $post_id 
	 * @param bool $raw 
	 * @return bool
	 */
	protected function parse_post_for_deploy($post, $raw) {
		$post_type = $post->post_type;
		$guid = $post->guid();
		
		if (!$raw) {
			$post = $post->get_data_for_transit();

			// put his author in the users array
			$author_key = $post['author']->user_login;
			$this->_deploy_data['users'][$author_key] = $post['author'];
			$post['author'] = $author_key;
		
			// put the taxonomies in place
			$taxes = $post['taxonomies'];
			$post['taxonomies'] = array();
			if (!empty($taxes)) {
				foreach ($taxes as $tax_type => $terms) {
					if (!empty($terms)) {
						// store just the slugs with the post data
						$post['taxonomies'][$tax_type] = array_keys($terms);
						// add out terms to the appropriate taxonomies section
						foreach ($terms as $key => $term) {
							$this->_deploy_data['taxonomies'][$tax_type][$key] = $term;
						}
					}
				}
			}
			
			// put the attachments in place
			if (!empty($post['attachments'])) {
				$attachments = $post['attachments'];
				$post['attachments'] = array();
				foreach ($attachments as $att_guid => $attachment) {
					$post['attachments'][$att_guid] = array(
						'guid' => $att_guid,
						'url' => wp_get_attachment_url($attachment['post']['ID']),
						'post_name' => $attachment['post']['post_name']
					);
					try {
						$attachment = new cfd_post($attachment['post']['ID']);
						$this->parse_post_for_deploy($attachment, $raw);
					}
					catch(Exception $e) {
						// foo
					}
				}
			}			
		}
		
		$this->_deploy_data['post_types'][$post_type][$guid] = $post;
	}
	
	public function get_deploy_item_data($args) {
		$data = $this->get_deploy_data();

		extract($args);
		if (!empty($args['object_type'])) {
			$ret[$type][$object_type][$guid] = $data[$type][$object_type][$guid];
		}
		else {
			$ret[$type][$guid] = $data[$type][$guid];
		}
		
		return $ret;
	}
	
	/**
	 * Gather the data that will be deployed
	 *
	 * @param string $raw - doesn't optimize the deploy data for transit
	 * @return array
	 */
	public function get_deploy_data($raw = false, $preflight = false) {
		$this->_deploy_data = array(
			'taxonomies' => array(),
			'users' => array(),
			'post_types' => array(),
			'menus' => array(),
			'bookmarks' => array()
		);
		foreach ($this->data as $key => $group) {
			switch ($key) {
				case 'post_types':
					foreach ($group as $post_type => $post_ids) {
						if (!empty($post_ids)) {
							// start all our post objects
							foreach ($post_ids as $post_id) {
								try {
									$posts[$post_id] = new cfd_post($post_id);
								}
								catch (Exception $e) {
									// no real error handling yet
									error_log('cfd_post object error: '.$e->getMessage().' - '.__FILE__.'::'.__LINE__);
								}
							}
							
							// sort the post objects
							$post_type_object = get_post_type_object($post_type);
							if ($post_type_object->hierarchical) {
								$posts = $this->sort_posts_by_parent($posts);
							}
							
							//  process the post objects
							foreach ($posts as $post) {
								// pull post and distribute parts across the deploy data
								$this->parse_post_for_deploy($post, $raw);	
							}
							
							// cleanup
							unset($posts);
						}
					}
					break;
				case 'users':
					if (!empty($group)) {
						$this->_deploy_data['users'] = array_merge($this->_deploy_data['users'], $this->get_users_by_ids($group));
					}
					break;
				case 'taxonomies':
					if (!empty($group)) {
						foreach ($group as $tax_type => $term_ids) {
							if (!empty($term_ids)) {
								foreach ($this->get_terms($tax_type, $term_ids) as $slug => $term) {
									$this->_deploy_data['taxonomies'][$tax_type][$slug] = $term;
								}
							}
						}
					}
					break;
				case 'bookmarks':
					if (!empty($group)) {
						foreach($group as $bookmark_id) {
							$bookmark = $this->get_bookmark($bookmark_id);
							if (!empty($bookmark->link_category)) {
								$link_cats = $bookmark->link_category;
								$bookmark->link_category = array();
								foreach ($link_cats as $key => $term) {
									$bookmark->link_category[] = $term->slug;
									$this->_deploy_data['taxonomies']['link_category'][$term->slug] = $term;
								}
							}
							$this->_deploy_data['bookmarks'][$bookmark->link_url] = $bookmark;
						}
					}
					break;
				case 'menus':
					if (!empty($group)) {
						foreach ($group as $menu_id) {
							try {
								$menu = new cfd_menu($menu_id);
								if ($raw) {
									$this->_deploy_data['menus'][$menu->guid()] = $menu;
								}
								else {
									$this->_deploy_data['menus'][$menu->guid()] = $menu->get_data_for_transit();
								}
							}
							catch (Exception $e) {
								// no real error handling yet
								error_log('cfd_menu object error: '.$e->getMessage.' - '.__FILE__.'::'.__LINE__);
							}
						}
					}
					break;
			}
		}
		
		// we have to wait to reorder taxonomies since post_types & bookmarks can add to the list of terms
		foreach ($this->_deploy_data['taxonomies'] as $tax_type => &$terms) {
			$taxonomy = get_taxonomy($tax_type);
			if ($taxonomy->hierarchical) {
				$terms = $this->sort_terms_by_parent($terms);
			}
		}
		
		// raw returns objects, not-raw returns data ready for transit
		if (!$raw) {
			array_walk_recursive($this->_deploy_data, array($this, 'object_to_array'));
			#array_walk_recursive($this->_deploy_data, array($this, 'trim_scalar'));
		}

		if ($preflight) {
			foreach ($this->_deploy_data['post_types'] as $post_type => $objects) {
				if (!empty($objects)) {
					foreach ($objects as $key => $post_obj) {
						$this->_deploy_data['post_types'][$post_type][$key]['post']['post_content'] = md5($post_obj['post']['post_content']);
						if (!empty($post_obj['post']['post_excerpt'])) {
							$this->_deploy_data['post_types'][$post_type][$key]['post']['post_excerpt'] = md5($post_obj['post']['post_excerpt']);
						}
					}
				}
			}
		}

		// make absolutely sure that attachments are last in the post_types array
		if (!empty($this->_deploy_data['post_types']['attachment'])) {
			$a = $this->_deploy_data['post_types']['attachment'];
			unset($this->_deploy_data['post_types']['attachment']);
			$this->_deploy_data['post_types']['attachment'] = $a;
		}
		
		return $this->_deploy_data;
	}

	/**
	 * Sort terms by parent dependency
	 *
	 * @param string $terms 
	 * @return void
	 */
	protected function sort_terms_by_parent($terms) {
		$this->_sorted_term_ids = array();
		$terms_by_id = array();
		
		foreach ($terms as $slug => $term) {
			$this->_sorted_term_ids[$term->parent][] = $term->term_id;
			$terms_by_id[$term->term_id] = $term;
		}
		ksort($this->_sorted_term_ids);
		$sorted = array();
		foreach ($this->_sorted_term_ids as $id => $parent_level) {
			$sorted = array_merge($sorted, $this->_find_term_sort_children($id));
		}

		$sorted_terms = array();
		foreach ($sorted as $_id) {
			$sorted_terms[$terms_by_id[$_id]->slug] = $terms_by_id[$_id];
		}
		return $sorted_terms;
	}
	
			/**
			 * recursive function helper for sort_terms_by_parent
			 *
			 * This code has been extra indented on purpose
			 *
			 * @param int $id 
			 * @return array
			 */
			protected function _find_term_sort_children($id) {
				$sorted = array();
				if (isset($this->_sorted_term_ids[$id]) && count($this->_sorted_term_ids[$id])) {
					foreach ($this->_sorted_term_ids[$id] as $_id) {
						$sorted[] = $_id;
						$sorted = array_merge($sorted, $this->_find_term_sort_children($_id));
						unset($this->_sorted_term_ids[$_id]);
					}
				}
				return $sorted;
			}

	/**
	 * Sort posts by parent dependency
	 *
	 * @param string $posts 
	 * @param string $ascending 
	 * @return void
	 */
	protected function sort_posts_by_parent($posts) {
		$this->_sorted_post_ids = array();
		
		foreach ($posts as $key => $post) {
			$this->_sorted_post_ids[$post->post->post_parent][] = $post->post->ID;
		}
		ksort($this->_sorted_post_ids);

		$sorted = array();
		foreach ($this->_sorted_post_ids as $id => $parent_level) {
			$sorted = array_merge($sorted, $this->_find_post_sort_children($id));
		}		

		$sorted = array_flip($sorted);
		foreach ($posts as $post) {
			$sorted[$post->post->ID] = $post;
		}
		
		return $sorted;
	}
			/**
			 * recursive function helper for sort_posts_by_parent
			 *
			 * This code has been extra indented on purpose
			 *
			 * @param int $id 
			 * @return array
			 */
			function _find_post_sort_children($id) {
				$sorted = array();
				if (isset($this->_sorted_post_ids[$id]) && count($this->_sorted_post_ids[$id])) {
					foreach ($this->_sorted_post_ids[$id] as $_id) {
						$sorted[] = $_id;
						$sorted = array_merge($sorted, $this->_find_post_sort_children($_id));
						unset($this->_sorted_post_ids[$_id]);
					}
				}
				return $sorted;
			}

	/**
	 * Return display data for batch display screen
	 *
	 * @param string $type 
	 * @return array
	 */
	public function get_comparison_data($type) {
		$ret = false;
		
		switch ($type) {
			case 'post_types':
				if (count($this->c_data['post_types'])) {
					foreach ($this->c_data['post_types'] as $post_type => $post_objects) {
						$ret[$post_type] = array();
						if (count($post_objects) && count($this->s_data['post_types'][$post_type])) {
							foreach ($post_objects as $id => $post) {
								if (!empty($this->s_data['post_types'][$post_type][$id])) {
									$post->status = $this->s_data['post_types'][$post_type][$id];
									$post->modified = null;
									$post->errors = null;
									$post->profile();

									if (empty($post->status['remote_status'])) {
										$post->modified[] = __('new', 'cf-deploy');
									}
									elseif (!empty($post->status['remote_status'])) {
										// modified time
										if (strtotime($post->post_modified) > strtotime($post->status['remote_status']['post_modified'])) {
											$post->modified[] = __('local newer', 'cf-deploy');
										}
										elseif (strtotime($post->post_modified) < strtotime($post->status['remote_status']['post_modified'])) {
											$post->modified[] = __('remote newer', 'cf-deploy');
										}
										
										// meta profile
										if (md5(serialize($post->profile['meta'])) != md5(serialize($post->status['remote_status']['profile']['meta']))) {
											$post->modified[] = __('meta', 'cf-deploy');
										}
										
										// attachments: we need to prune attachments a bit to get an accurate comparison. prune: post_parent, post_name
										$atts = array(
											'a' => $post->profile['attachments'],
											'b' => $post->status['remote_status']['profile']['attachments']
										);
										foreach ($atts as &$attachments) {
											if (!empty($attachments)) {
												foreach ($attachments as &$attachment) {
													unset($attachment['post']['post_parent'], $attachment['post']['post_name']);
												}
											}
										}
										if (md5(serialize($atts['a'])) != md5(serialize($atts['b']))) {
											$post->modified[] = __('attachments', 'cf-deploy');
										}
										unset($atts);
										
										// taxonomies
										if (md5(serialize($post->profile['taxonomies'])) != md5(serialize($post->status['remote_status']['profile']['taxonomies']))) {
											$post->modified[] = __('taxonomies', 'cf-deploy');
										}
																				
										// attachment content
										if ($post_type == 'attachment') {
											foreach ($post->profile['post'] as $key => $value) {
												if ($post->status['remote_status']['profile']['post'][$key] !== $value) {
													$post->modified[] = 'local and remote differ';
													break;
												}
											}
										}
									}
									if (!empty($post->modified) || $post->force_in_batch) {
										if (!empty($this->data['post_types'][$post_type]) && in_array($post->ID, $this->data['post_types'][$post_type])) {
											$post->selected = true;
										}
										$ret[$post_type][$id] = $post;
									}
								}
							}
							if (!empty($this->s_data['post_types'][$post_type]['__error__'])) {
								$ret[$post_type]['__error__'] = $this->s_data['post_types'][$post_type]['__error__'];
							}
						}
					}
				}
				break;
			case 'users':
				if (count($this->c_data[$type])) {
					foreach($this->c_data[$type] as $id => $object) {
						if (!empty($this->s_data[$type][$id])) {
							$object->status = $this->s_data[$type][$id];
							$_u = new cfd_user(array('user_id' => $object->ID));
							$local_profile = md5(serialize($_u->profile()));
							$object->modified = null;
							
							if (empty($object->status['remote_status'])) {
								$object->modified = 'new';
							}
							else {
								$registered_difference = ($object->user_registered != $object->status['remote_status']['user_registered']);
								$profile_difference = ($local_profile != $object->status['remote_status']['profile']);
								$in_batch = (!empty($this->data['users']) && in_array($object->ID, $this->data['users']));
								
								if (empty($object->status['remote_status']) || $registered_difference || $profile_difference || $in_batch) {
										$object->modified = 'profile';
								}
							}
							
							if (!is_null($object->modified)) {
								if (!empty($this->data[$type]) && in_array($object->ID, $this->data[$type])) {
									$object->selected = true;
								}
								$ret[$id] = $object;
							}
						}
					}
					if (!empty($this->s_data[$type]['__error__'])) {
						$ret['__error__'] = $this->s_data[$type]['__error__'];
					}
				}
				break;
			case 'taxonomies':
				if (count($this->c_data[$type])) {
					foreach($this->c_data[$type] as $tax_type => $objects) {
						if (count($objects)) {
							foreach ($objects as $id => $object) {
								$object->status = $this->s_data[$type][$tax_type][$id];
								$object->modified = null;

								if (empty($object->status['remote_status'])) {
									$object->modified = 'new';
								}
								elseif (!empty($object->status['remote_status'])) {
									if ($object->name != $object->status['remote_status']['name']) {
										$object->modified[] = 'name';
									}
									if ($object->description != $object->status['remote_status']['description']) {
										$object->modified[] = 'description';
									}
									if ($object->parent != $object->status['remote_status']['parent']) {
										$object->modified[] = 'parent';
									}
								}

								$in_batch = (!empty($this->data[$type]) && in_array($object->term_id, $this->data[$type][$tax_type]));
								
								if (!empty($object->modified) || $in_batch) {
									if (!empty($this->data[$type][$tax_type]) && in_array($object->term_id, $this->data[$type][$tax_type])) {
										$object->selected = true;
									}
									$ret[$tax_type][$id] = $object;
								}
							}
							if (!empty($this->s_data[$type][$tax_type]['__error__'])) {
								$ret[$tax_type]['__error__'] = $this->s_data[$type][$tax_type]['__error__'];
							}
						}
					}
				}
				break;
			case 'bookmarks':
				if (count($this->c_data[$type])) {
					foreach ($this->c_data[$type] as $id => $object) {
						$object->status = $this->s_data[$type][$id];
						
						$status_difference = (!empty($object->status['remote_status']['link_hash']) && $object->status['link_hash'] != $object->status['remote_status']['link_hash']);
						$in_batch = (!empty($this->data[$type]) && in_array($object->link_id, $this->data[$type]));
						
						if (empty($object->status['remote_status']) || $status_difference || $in_batch) {
							if (!empty($this->data[$type]) && in_array($object->link_id, $this->data[$type])) {
								$object->selected = true;
							}
							$ret[$id] = $object;
						}
					}
					if (!empty($this->s_data[$type]['__error__'])) {
						$ret['__error__'] = $this->s_data[$type]['__error__'];
					}
				}
				break;
			case 'menus':
				if (count($this->c_data[$type])) {
					foreach ($this->c_data[$type] as $id => $object) {
						$object->status = $this->s_data[$type][$id];
						
						$modified_difference = ($object->last_modified() != $object->status['remote_status']['last_modified']);
						$in_batch = (!empty($this->data[$type]) && in_array($object->id(), $this->data[$type]));
						
						if (empty($object->status['remote_status']) || $modified_difference || $in_batch) {
							if (!empty($this->data[$type]) && in_array($object->id(), $this->data[$type])) {
								$object->selected = true;
							}
							$ret[$id] = $object;
						}
					}
					if (!empty($this->s_data[$type]['__error__'])) {
						$ret['__error__'] = $this->s_data[$type]['__error__'];
					}
				}
				break;
			default:
				throw new Exception(__('Unknown type requested: ', 'cf-deploy').$type);
				break;
		}
		return $ret;
	}

	/**
	 * Return a lightweight version of the batch data 
	 * for use in comparing local server data against remote server data
	 *
	 * @return array
	 */	
	public function get_batch_status_data() {
		$this->s_data = array();
		
		foreach($this->c_data as $key => $group) {
			switch ($key) {
				case 'post_types':
					$this->s_data['post_types'] = array();
					if (count($group)) {
						foreach ($group as $post_type => $post_type_objects) {
							if (count($post_type_objects)) {
								foreach ($post_type_objects as $id => $object) {
									$this->s_data['post_types'][$post_type][$id] = array(
										'guid' => $object->guid,
										'post_name' => $object->post_name
									);
								}
							}
						}
					}
					break;
				case 'users':
					$this->s_data[$key] = array();
					if (count($group)) {
						foreach ($group as $id => $user) {
							$this->s_data[$key][$id] = array(
								'user_login' => $user->user_login,
								'user_email' => $user->user_email
							);
						}
					}
					break;
				case 'taxonomies':
					$this->s_data['taxonomies'] = array();
					if (count($group)) {
						foreach ($group as $tax_type => $tax_object_group) {
							if (count($tax_object_group)) {
								foreach ($tax_object_group as $object) {
									$this->s_data['taxonomies'][$tax_type][$object->slug] = array(
										'slug' => $object->slug,
										'name' => $object->name,
										'parent' => $object->parent
									);
								}
							}
						}
					}
					// @TODO
					break;
				case 'bookmarks':
					$this->s_data[$key] = array();
					if (count($group)) {
						foreach ($group as $id => $bmark) {
							$_b = clone $bmark;
							if (!empty($_b->categories)) {
								foreach ($_b->categories as &$cat) {
									$cat = $cat->slug;
								}
							}
														
							unset($_b->link_id, $_b->link_updated);
							
							$this->s_data[$key][$id] = array(
								'link_hash' => md5(serialize($_b))
							);
						}
					}
					break;
				case 'menus':
					$this->s_data[$key] = array();
					if (count($group)) {
						foreach ($group as $id => $menu) {
							$this->s_data[$key][$id] = $menu->profile();
						}
					}
					break;
			}
		}
		
		return $this->s_data;
	}
	
	/**
	 * Merge remote comparison data in with local data
	 *
	 * @param array $data 
	 * @return bool
	 */
	public function parse_status_data($data) {
		$this->r_data = $data;
		
		if (!empty($this->r_data)) {
			foreach ($this->r_data as $key => $group) {
				if ($group !== false) {
					switch ($key) {
						case 'post_types':
						case 'taxonomies':
							if (count($group)) {
								foreach ($group as $sub_type => $type_arrays) {
									if (count($type_arrays)) {
										foreach ($type_arrays as $id => $array) {
											$this->s_data[$key][$sub_type][$id]['remote_status'] = $array;
										}
									}
								}
							}
							break;
						case 'menus':
						case 'bookmarks':
						case 'users':
						default:
							if (count($group)) {
								foreach ($group as $id => $group_data_array) {
									$this->s_data[$key][$id]['remote_status'] = $group_data_array;
								}
							}
							break;				
					}
				}
			}
		}
		
		return true;
	}

// Preflight

	public function parse_preflight_data($data) {
		update_post_meta($this->post->ID, '_preflight_data', array(
			'date' => date('c'),
			'user' => get_current_user_id(),
			'data' => $data
		));
		
		if (!empty($data['extras'])) {
			global $cfd_admin;
			foreach ($data['extras'] as $extra_id => $extra_data) {
				$method = $cfd_admin->get_extra_callback_method($extra_id, 'preflight_display');
				$data = call_user_func($method, $data);
			}
		}
		
		$this->p_data = $data;
	}
	
	// protected function merge_extras_data($batch_data, $extras_data) {
	// 	foreach ($extras_data as $extra_id => $extra_data) {
	// 		$method = $this->get_extra_callback_method($extra_id, 'preflight_display');
	// 		$batch_preflight_data = $this->batch->get_preflight_data();
	// 		if (!empty($method)) {
	// 			$batch_preflight_data = call_user_func($method, $batch_preflight_data, $batch_data, $extras_data[$extra_id]);
	// 		}
	// 	}
	// 	return $batch_preflight_data;
	// }
	
	public function get_preflight_extras_data() {
		if (!empty($this->p_data['extras'])) {
			return $this->p_data['extras'];
		}
		return null;
	}
	
	function check_for_errors($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				// yes, strval is needed. At least in php 5.3 if a string and 
				// integer are compared the string will always be converted to 
				// an integer no matter if it is first or second in the statement
				if ('__errors__' === strval($key) || '__error__' == strval($key)) {
					$this->has_preflight_error = true;
				}
				if (is_array($value)) {
					$this->check_for_errors($value);
				}
			}
		}
	}
	
	public function get_preflight_data($key = null, $type = null) {
		if (!empty($this->p_data[$key])) {
			if (!empty($type)) {
				$ret = $this->p_data[$key][$type];
			}
			else {
				$ret = $this->p_data[$key];
			}
		}
		else {
			$ret = $this->p_data;
		}
		return $ret;
	}
	
	public function has_error() {
		if (!is_bool($this->has_preflight_error)) {
			$this->check_for_errors($this->p_data);
		}
		return ($this->has_preflight_error === true);
	}

// Data Gathering

	public function get_post_types() {
		$types = get_post_types(array(
			'public' => 1
		), 'names');
		return apply_filters('cf-deploy-post-types', $types);
	}

	/**
	 * Filter to pull posts from a certain date
	 * This filter is added dynamically as needed
	 *
	 * @param string $where 
	 * @return string
	 */
	public function post_date_filter($where) {
		$where .= ' AND post_modified >= "'.$this->start_date.'"';
		
		// add in post ids that are saved to the batch so that out of date scope items are 
		// included if they've been saved as part of the batch
		if (!empty($this->post_type_batch_ids)) {
			$where .= ' OR ID IN ('.$this->post_type_batch_ids.')';
		}

		return $where;
	}

	public function get_post_type_objects_since($type, $since_date) {
		$this->post_filter_date = date('Y-m-d', strtotime($since_date));

		if (!empty($this->data['post_types'][$type])) {
			$post_type_batch_ids = $this->data['post_types'][$type];
			$this->post_type_batch_ids = implode(',', $this->data['post_types'][$type]);
		}
		
		add_filter('posts_where_paged', array($this, 'post_date_filter'));
		$args = array(
			'post_type' => $type,
			'post_status' => ($type == 'attachment' ? 'inherit' : 'publish'),
			'order' => 'ASC',
			'showposts' => -1
		);
		$q = new WP_Query($args);

		remove_filter('posts_where_paged', array($this, 'post_date_filter'));
		$this->post_type_batch_ids = null;

		$posts = array();
		if (count($q->posts)) {
			foreach ($q->posts as $post) {
				if ($type == 'attachment' && $post->post_parent != 0) {
					continue; 
				}
				
				try {
					$class = ($type == 'attachment' ? 'cfd_attachment' : 'cfd_post');
					$_p = new $class($post->ID);
					if (!empty($post_type_batch_ids) && in_array($_p->id(), $post_type_batch_ids)) {
						$_p->force_in_batch = true;
					}
					$posts[$post->guid] = $_p;
				}
				catch (Exception $e) {
					// no handling yet
					error_log('cfd_post object error: '.$e->getMessage().' - '.__FILE__.'::'.__LINE__);
				}
			}
		}
		
		return $posts;
	}

	public function get_users_since($since_date) {
		global $wpdb;
		$since_date = '00:00:0000 00:00:00';
		$date = date('Y-m-d', strtotime($since_date));
		$user_ids = $wpdb->get_col($wpdb->prepare('SELECT '.$wpdb->users.'.ID FROM '.$wpdb->users.' WHERE `user_registered` >= %s AND user_login <> "admin" ORDER BY `user_registered` ASC', $date));
		return $this->get_users_by_ids($user_ids);
	}
	
	public function get_users_by_ids($user_ids) {
		if (!empty($this->data['users'])) {
			array_merge($user_ids, $this->data['users']);
			array_unique($user_ids);
		}
		
		if (!is_array($user_ids)) {
			$user_ids = array($user_ids);
		}
		
		$users = array();
		if (count($user_ids)) {
			foreach($user_ids as $uid) {
				$user = new WP_User($uid);
				// contributors and higher only
				if ($user->has_cap('edit_posts')) {
					$users[$user->user_login] = $user;
				}
			}
		}
		return $users;
	}
	
	public function get_taxonomies() {
		$tax_type_objects = get_taxonomies(array(
			'public' => true
		), 'objects');

		$taxonomies = array();
		foreach ($tax_type_objects as $tax_type) {
			if ($tax_type->name == 'nav_menu' || !$tax_type->hierarchical) { continue; } // we don't need to handle these here
			$taxonomies[$tax_type->name] = $this->get_terms($tax_type->name);
		}
		return $taxonomies;
	}
	
	public function get_terms($tax_type, $term_ids = null) {
		$args = array();
		
		if (!is_object($tax_type)) {
			$tax_type = get_taxonomy($tax_type);
		}
		
		if (!empty($term_ids)) {
			if (!is_array($term_ids)) {
				$term_ids = array($term_ids);
			}
			$args['include'] = $term_ids;
		}
		$terms = get_terms($tax_type->name, $args);

		$taxonomy_terms = array();		
		if (!empty($terms)) {
			$taxonomy_terms = array();
			foreach ($terms as $term) {
				if (!empty($term->parent)) {
					$parent = get_term($term->parent, $term->taxonomy);
					$term->term_parent_slug = $parent->slug;
				}
				// add the post type for building the edit link
				$term->post_type = $tax_type->object_type[0]; // this is a "best guess" until proven otherwise
				$taxonomy_terms[$term->slug] = $term;
			}
		}
		
		
		
		return $taxonomy_terms;
	}
	
	public function get_bookmarks() {
		$args = array();
		$bmarks = get_bookmarks($args);

		$bookmarks = array();
		if (!empty($bmarks)) {
			foreach ($bmarks as $bmark) {
				$b_cats = wp_get_object_terms( $bmark->link_id, 'link_category', array('fields' => 'all') );
				foreach ($b_cats as $cat) {
					$bmark->categories[] = $cat;
				}
				$bookmarks[$bmark->link_url] = $bmark;
			}
		}
		return $bookmarks;
	}
	
	public function get_bookmark($bookmark_id) {
		$bookmark = get_bookmark($bookmark_id);
		if (!empty($bookmark->link_category)) {
			foreach ($bookmark->link_category as &$link_category) {
				$link_category = get_term($link_category, 'link_category');
			}
		}
		
		return $bookmark;
	}
	
	public function get_menus() {
		$menu_args = array(
			'hide_empty' => true,
			'orderby' => 'name'
		);
		$_menus = wp_get_nav_menus($menu_args);

		$menus = array();
		foreach ($_menus as $_menu) {
			try {
				$menu = new cfd_menu($_menu->slug);
				if (($menu->last_modified() != null && strtotime($menu->last_modified()) > strtotime($this->start_date)) || (!empty($this->data['menus']) && in_array($_menu->term_id, $this->data['menus']))) {
					$menus[$_menu->slug] = new cfd_menu($_menu->slug);	
				}
			}
			catch (Exception $e) {
				// no error handling yet
				error_log('cfd_menu object error: '.$e->getMessage().' - '.__FILE__.'::'.__LINE__);
			}
		}

		return $menus;
	}
		
// Batch Post Operations

	/**
	 * Grab our batch post
	 * 
	 * @throws Exception if valid post is not found
	 * @return void
	 */
	public function get_batch_post() {
		$this->post = get_post($this->ID);
		if (!empty($this->post)) {
			if ($this->post->post_type != CF_DEPLOY_POST_TYPE) {
				throw new Exception('Invalid batch ID "'.$this->ID.'"');
			}
			
			foreach(array_flip($this->field_trans) as $post_field => $batch_field) {
				$this->$batch_field = $this->post->$post_field;

				if ($batch_field == 'data') {
					$this->data = unserialize($this->$batch_field);
					if (empty($this->start_date) && !empty($this->data['start_date'])) {
						$this->start_date = $this->data['start_date'];
					}
				}
			}
			$this->author = new WP_User(intval($this->post->post_author));
		}
		else {
			throw new Exception(__('Batch post does not exist', 'cf-deploy'));
		}
	}
	
// Getters and setters

	public function set_data($data) {
		return $this->data = $data;
	}
	
	public function get_data() {
		return $this->data;
	}

	public function batch_data($type = '') {
		if (!empty($type) && isset($this->c_data[$type])) {
			return $this->c_data[$type];
		}
		else {
			return $this->c_data;
		}
	}
	
	public function __get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		}
		elseif (isset($this->c_data[$var])) {
			return $this->c_data[$var];
		}
		return false;
	}
	
	public function __set($var, $val) {
		if (isset($this->$var)) {
			return $this->$var = $val;
		}
		elseif (isset($this->c_data[$var])) {
			return $this->c_data[$var] = $val;
		}
		return false;
	}	
}

?>