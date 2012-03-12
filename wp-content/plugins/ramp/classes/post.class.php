<?php

class cfd_post extends cfd_item_base {
	protected $data;
	protected $shallow_fetch = false;

	public function __construct($post_id) {
		parent::__construct();

		if (is_numeric($post_id)) {
			$_post = get_post(intval($post_id));
		}
		elseif (is_object($post_id)) {
			$_post = $post_id;
		}
		elseif (is_array($post_id)) {
			if (isset($post_id['shallow_fetch'])) {
				$this->shallow_fetch = (bool) $post_id['shallow_fetch'];
			}
			if (!empty($post_id['guid'])) {
				// accept an array that specifies the guid
				$_post = $this->get_post_by_guid($post_id['guid']);
			}
		}
		
		if (!is_object($_post) || empty($_post->post_type)) {
			throw new Exception(sprintf(__('Could not retrieve post data for post: %s', 'cf-deploy'), print_r($post_id, true)));
		}
		
		$this->data['post'] = $_post;
		unset($_post, $post_id);
		$this->gather();
	}
	
	public function gather() {
		$this->meta = $this->get_metadata();
		$this->taxonomies = $this->get_taxonomies();
		$this->author = $this->get_author();
		
		if (!$this->shallow_fetch) {
			$this->attachments = $this->get_attachments();
			$this->parent = $this->get_parent();
			
			if ($this->post_type == 'attachment') {
				$this->data['file'] = array(
					'path' => get_attached_file($this->ID, false),
					'url' => wp_get_attachment_url($this->ID)
				);
			}
		}
	}

// Data Pullers

	protected function get_metadata() {
		$meta = get_metadata('post', $this->post->ID);

		$postmeta = array();
		if (!empty($meta)) {
			foreach (array_keys($meta) as $meta_key) {
				if ($meta_key == '_edit_lock') {
					continue;
				}
				$postmeta[$meta_key] = get_post_meta($this->post->ID, $meta_key, true);
				if ($meta_key == '_thumbnail_id' && !$this->shallow_fetch) {
					$thumbnail = new cfd_attachment($postmeta[$meta_key]);
					$this->data['featured_image'] = array(
						'id' => $thumbnail->id(),
						'slug' => $thumbnail->post_name,
						'guid' => $thumbnail->guid()
					);
					$postmeta[$meta_key] = $thumbnail->post_name;					
					unset($thumbnail);
				}
			}
		}
		ksort($postmeta);
		return $postmeta;
	}

	protected function get_taxonomies() {
		$taxonomy_types = get_object_taxonomies($this->post);
		$taxonomies = false;
		foreach($taxonomy_types as $taxonomy) {
			$taxonomy_objects = wp_get_post_terms($this->post->ID, $taxonomy);
			if (!empty($taxonomy_objects)) {
				foreach ($taxonomy_objects as $object) {
					if (!empty($object->parent)) {
						$parent = get_term($object->parent, $object->taxonomy);
						$object->term_parent_slug = $parent->slug;
					}
					$taxonomies[$taxonomy][$object->slug] = $object;
				}
			}
		}
		return $taxonomies;
	}

	protected function get_author() {
		$author = new WP_User($this->post->post_author);
		if (!empty($author) && is_object($author)) {
			return $author;
		}
		else {
			throw new Exception(sprintf(__('Could not retrieve author data for post_author `%s` when constructing %s', 'cf-deploy'), $this->post->post_author, __CLASS__));
		}			
	}
	
	protected function get_attachments() {
		$atts = get_posts(array(
			'posts_per_page' => 0,
			'post_type' => 'attachment', 
			'post_status' => 'inherit',
			'order' => 'ASC',
			'post_parent' => $this->post->ID
		));
				
		if (!empty($atts)) {
			$attachments = array();
			foreach($atts as $att) {
				$attachment = new cfd_attachment($att);
				$attachments[$attachment->guid()] = $attachment;
				unset($attachment);
			}
			ksort($attachments);
			return $attachments;
		}
		else {
			return false;
		}
	}
	
	protected function get_parent() {
		$parent = 0;
		if ($this->post->post_parent != 0) {
			try {
				$p = get_post($this->post->post_parent);
				if (!empty($p)) {
					$parent = array(
						'ID' => $p->ID,
						'slug' => $p->post_name,
						'guid' => $p->guid
					);
				}
				unset($p);
			}
			catch (Exception $e) {
				throw new Exception(sprintf(__('Could not start post_parent (%s) object for post ', 'cf-deploy'), $this->post->post_parent, $this->post->ID));
			}
		}
		return $parent;
	}
	
	protected function get_post_by_guid($guid) {
		return cfd_get_post_by_guid($guid);
	}
	
// Define Mandatory Abstract Methods

	public function id() {
		return $this->data['post']->ID;
	}

	public function name() {
		return $this->data['post']->post_title;
	}

	public function guid() {
		return $this->data['post']->guid;
	}

	/**
	 * Bastardization of get_edit_post_link to be a bit more compatible in our situation
	 *
	 * @return void
	 */
	public function edit_url() {
		$post_type_object = get_post_type_object($this->data['post']->post_type);
		return admin_url(sprintf($post_type_object->_edit_link.'&amp;action=edit', $this->data['post']->ID));
	}

	public function profile() {
		$this->profile = array();
		foreach($this->data as $key => $data) {
			switch ($key) {
				case 'meta':
					$this->profile['meta'] = array();
					if (!empty($data)) {
						foreach ($data as $key => $value) {
							if (!in_array($key, $this->ignored_meta_items)) {
								$this->profile['meta'][$key] = $value;
							}
						}
					}
					break;
				case 'attachments':
					$this->profile['attachments'] = array();
					if (!empty($data)) {
						foreach ($data as $id => $object) {
							$this->profile['attachments'][$object->guid] = $object->profile();
						}
					}
					ksort($this->profile['attachments']);
					break;
				case 'taxonomies':
					$this->profile['taxonomies'] = array();
					if (!empty($data)) {
						foreach ($data as $tax_type => $tax_objects) {
							if (is_array($tax_objects)) {
								foreach ($tax_objects as $object) {
									$this->profile['taxonomies'][$tax_type][$object->slug] = array(
										'slug' => $object->slug, 
										'name' => $object->name,
										'description' => $object->description
									);
								}
							}
						}
					}
					break;
				case 'post':
					$this->profile['post']['post_status'] = $data->post_status;
					break;
				case 'author':
					// we don't care about these in this context yet
					// can be used later for easy detection of changes
					break;
			}
		}
		return $this->profile;
	}
	
// Getters - allows us a more free structure within the $data var instead of hard-coded members
	
	public function __get($var) {
		foreach($this->data['post'] as $key => $value) {
			if ($var == $key) {
				return $value;
			}
		}
		
		if (!empty($this->data[$var])) {
			return $this->data[$var];
		}
		
		return null;
	}
	
	public function __set($var, $val) {
		return $this->data[$var] = $val;
	}
	
	public function __isset($var) {
		return isset($this->data[$var]);
	}
	
	public function __unset($var) {
		if (!empty($this->data[$var])) {
			unset($this->data[$var]);
		}
		return empty($this->data[$var]);
	}

	public function get_data() {
		return $this->data;
	}
		
	public function get_data_for_transit() {
		$data = $this->get_data();
		if (!empty($data['attachments'])) {
			foreach ($data['attachments'] as &$attachment) {
				$attachment = $attachment->get_data_for_transit();
			}
		}
		#array_walk_recursive($data, array($this, 'object_to_array'));
		#array_walk_recursive($data, array($this, 'trim_scalar'));
		return $data;
	}
}

?>