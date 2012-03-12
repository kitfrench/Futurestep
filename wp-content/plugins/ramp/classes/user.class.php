<?php

class cfd_user {
	protected $profile_fields = array(
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
	
	/**
	 * Constructor
	 *
	 * $args contains 3 optional parameters, but at least 1 must be defined
	 * - user_id: load userdata by ID
	 * - user_login: load userdata by user_login
	 * - user_email: load userdata by user_email
	 * 
	 * @param array $args 
	 */
	public function __construct($args) {
		if (!empty($args['user_id'])) {
			$this->user = new WP_User($args['user_id']);
		}
		elseif (!empty($args['user_login'])) {
			$u = get_user_by('login', $args['user_login']);
			$this->user = new WP_User($u->ID);
			unset($u);
		}
		elseif (!empty($args['user_email'])) {
			$u = get_user_by('email', $args['user_email']);
			$this->user = new WP_User($u->ID);
			unset($u);
		}
		
		if (empty($this->user)) {
			throw new Exception(__('Could not start user', 'cf-deploy').': '.print_r($args, true));
		}
		
		// add any additional profile fields as needed
		foreach (_wp_get_user_contactmethods() as $contact_method => $contact_method_name) {
			$this->profile_fields[$contact_method] = null;
		}
	}
	
	public function profile() {
		$profile = array();
		foreach ($this->profile_fields as $key => &$arg) {
		
			if ($key == 'role') {
				if (property_exists($this->user->data, 'roles')) {
					$profile[$key] = $this->user->data->roles[0];
				}
			}
			else {
				$profile[$key] = $this->user->data->$key;
			}
		}
		
		ksort($profile); // we always need alpha sorting to get a proper md5 match
		return $profile;
	}
}

?>