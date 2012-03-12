<?php

abstract class cfd_item_base extends cfd_common {
	public $profile;
	public $modified;
	public $selected;
	public $errors;
	
	protected $ignored_meta_items;
	
	public function __construct() {
		// These are postmeta items that we don't care about for our purposes
		$this->ignored_meta_items = array(
			'_wp_old_slug',
			'_edit_lock',
			'_edit_last'
		);
	}
	
// Methods that must be redefined by sub-class
	
	abstract public function id();
	abstract public function name();
	abstract public function guid();
	
	abstract public function edit_url();
	
	abstract public function profile();
	
	abstract public function get_data_for_transit();
}

?>