<?php

class cfd_menu extends cfd_item_base {
	public $menu;
	public $items;
	public $modified;
	
	protected $last_modified_date;
	
	public function __construct($menu_term_slug) {
		parent::__construct();
		$menu = wp_get_nav_menu_object($menu_term_slug);
		
		if (!empty($menu)) {
			$this->menu = $menu;
			$nav_menu_items = wp_get_nav_menu_items($this->menu->term_id);

			cfd_tmp_dbg('nav_menu_items_raw.txt', $nav_menu_items, 'print');

			foreach ($nav_menu_items as $item) {
				$menu_item = wp_setup_nav_menu_item($item);
				
				$menu_item->metadata = get_metadata('post', $item->ID);
				foreach ($menu_item->metadata as $key => &$value) {
					$value[0] = maybe_unserialize($value[0]);
				}
				
				if ($menu_item->type == 'post_type') {
					$menu_item->parent = get_post($menu_item->metadata['_menu_item_object_id'][0]);
				}
				elseif ($menu_item->type == 'taxonomy' && $menu->object != 'custom') {
					$menu_item->term = get_term($menu_item->metadata['_menu_item_object_id'][0], $menu_item->metadata['_menu_item_object'][0]);
				}
				
				$this->items[] = $menu_item;
			}
		}
		else {
			throw new Exception(__('Invalid menu id', 'cf-deploy').': '.esc_attr($menu_term_slug));
		}
	}
	
	public function id() {
		return $this->menu->term_id;
	}
	
	public function name() {
		return $this->menu->name;
	}
	
	public function guid() {
		return $this->menu->slug;
	}
	
	public function edit_url() {
		return admin_url('nav-menus.php?action=edit&menu='.$this->menu->term_id);
	}
	
	public function last_modified() {
		if (empty($this->last_modified_date) && !empty($this->items)) {
			foreach($this->items as $item) {
				if (strtotime($item->post_modified) > strtotime($this->last_modified_date)) {
					$this->last_modified_date = $item->post_modified;
				}
			}
		}
		
		return $this->last_modified_date;
	}
	
	public function profile() {
		if (empty($this->profile)) {
			$this->profile = array(
				'guid' => $this->guid(),
				'last_modified' => $this->last_modified()
			);
		}
		return $this->profile;
	}
	
	public function get_data_for_transit() {
		$data = array(
			'menu' => $this->menu,
			'items' => array()
		);
		
		if (!empty($this->items)) {
			foreach ($this->items as $item) {
				switch ($item->type) {
					case 'post_type':
						$parent = $item->parent;
						$item->parent = array(
							'post_type' => $parent->post_type,
							'post_title' => $parent->post_title,
							'guid' => $parent->guid
						);
						break;
					case 'taxonomy':
						$term = $item->term;
						$item->term = array(
							'slug' => $term->slug,
							'taxonomy' => $term->taxonomy
						);
						break;
				}
				$data['items'][] = $item;
			}
		}
		
		array_walk_recursive($data, array($this, 'object_to_array'));
		array_walk_recursive($data, array($this, 'trim_scalar'));
		
		return $data;	
	}
}

?>