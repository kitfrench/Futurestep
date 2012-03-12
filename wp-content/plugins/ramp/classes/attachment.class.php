<?php

class cfd_attachment extends cfd_post {
	public function __construct($attachment) {
		parent::__construct($attachment);
	}
	
	/**
	 * We need to inspect the post on attachments to get
	 * description and title changes
	 *
	 * @return array
	 */
	public function profile() {
		parent::profile();
		$tmp['post'] = clone $this->data['post'];
		
		array_walk_recursive($tmp, array($this, 'object_to_array'));
		$this->profile['post'] = $tmp['post'];
		unset($tmp);
		
		unset($this->profile['post']['ID']);
		return $this->profile;
	}
	
	/**
	 * Need to do slightly less with attachments
	 *
	 * @return array
	 */
	public function get_data_for_transit() {
		$data = $this->get_data();
		array_walk_recursive($data, array($this, 'object_to_array'));
		array_walk_recursive($data, array($this, 'trim_scalar'));
		return $data;
	}
}

?>