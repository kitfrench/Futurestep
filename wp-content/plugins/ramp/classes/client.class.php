<?php

class cf_deploy_client extends cfd_common {
	protected $use_compression = CF_DEPLOY_USE_COMPRESSION;
	
	public function __construct() {
		$this->options = maybe_unserialize(get_option(CF_DEPLOY_SETTINGS, array()));
	}

	/**
	 * Generate a sekret key for request validation
	 *
	 * - The request args are first sorted by key to ensure that they're always in the same order
	 *   for key generation.
	 * - The query vars are converted to an http request string
	 * - The user, method and query string are concatenated
	 * - The concatenated string is then hashed with sha1
	 * 
	 * The sending server generates a hash by using the receiving server's auth key
	 * The receiving server should be able to replicate the result by using its own auth key
	 * 
	 * @param string $auth_key - the receiving server's authentication key
	 * @param string $method 
	 * @param array $args 
	 * @param string $user 
	 * @return string
	 */
	public function sekret($auth_key, $method, $args, $user) {
		ksort($args);
		$query_string = $user.$method.http_build_query($args);
		return hash_hmac('sha1', $query_string, $auth_key);
	}
	
// Encoding & compression

	protected function encode($data) {
		if ($this->use_compression) {
			$data = $this->compress($data);
		}
		$data = base64_encode($data);
		return $data;
	}

	protected function decode($data) {
		$data = base64_decode($data);
		if ($this->use_compression) {
			$data = $this->uncompress($data);
		}
		return $data;
	}
	
	protected function compress($data) {
		return gzcompress($data);
	}
	
	protected function uncompress($data) {
		return gzuncompress($data);
	}

// send 
	
	/**
	 * Build data for XML-RPC Request & hand off to query function for actual send
	 * Builds validation hash of $params['args'] for quick integrity check on remote end
	 *
	 * @param array $params 
	 * @return object cfd_message
	 */
	function send($params) {
		if (empty($params['server']) || empty($params['auth_key'])) {
			$errors = array();
			if (empty($params['server'])) {
				$errors[] = '<p>'.__('<b>No remote server defined.</b> A remote server address should be defined in the <a href="' . admin_url("admin.php?page=cf-ramp-settings") . '">RAMP Settings</a> to continue.', 'cf-deploy').'</p>';
			}
			if (empty($params['auth_key'])) {
				$errors[] = '<p>'.__('<b>No remote server auth key defined.</b> A remote server auth key should be defined in the <a href="' . admin_url("admin.php?page=cf-ramp-settings") . '">RAMP Settings</a> to continue.', 'cf-deploy').'</p>';
			}
			return new cfd_message(array(
				'success' => false,
				'type' => 'config-error',
				'message' => '<div class="cfd-message error">'.implode('', $errors).'</div>'
			));
		}
		
		$current_user = wp_get_current_user();
		
		$send_args = array(
			'cfd.receive',																					    // listener
			'1',																							    // blog_id, leave for future expansion
			$current_user->user_login,																		    // username for capabilities
			'omgtheykilledkenny!',																			    // just like it says...
			$params['method'],																				    // remote method to call
			$this->encode(serialize($params['args'])),															// args
			$this->sekret($params['auth_key'], $params['method'], $params['args'], $current_user->user_login),	// sekret
			md5(serialize($params['args']))																		// cheap way to help validate complete transmission of data
		);
		
		cfd_tmp_dbg('cf-data-sent.txt', array_slice($send_args, 1), 'print');
		
		try {
			$response = $this->query($params['server'], $send_args);
		}
		catch(Exception $e) {
			$ret = array(
				'success' => false,
				'response' => 'Error ('.$e->getCode().'): '.$e->getMessage()
			);
		}

		$response['response'] = unserialize($this->decode($response['response']));		

		if (is_array($response['response']) && !empty($response['response']['is_cfd_message'])) {
			$ret = new cfd_message($response['response']);
		}
		elseif ($response['response'] instanceof IXR_Error) {
			$ret = new cfd_message(array(
				'success' => false,
				'type' => 'ixr-communication-error',
				'message' => '<div class="cfd-message error"><p>'.$response['response']->message.'</p></div>'
			));
		}
		else {
			// try a json decode on the response, if so, process response as message
			$_ret = false;
			if (is_scalar($response['response'])) {
				$_ret = json_decode($response['response'], true);
			}
			
			if (!empty($_ret) && is_array($_ret)) {
				$ret = new cfd_message($_ret);
			}
			else {
				$ret = new cfd_message(array(
					'success' => $response['success'],
					'type' => $params['method'].'-response',
					'message' => $response['response']
				));
			}
		}

		return $ret;
	}
	
	/**
	 * Perform the remove XML-RPC request
	 *
	 * @param string $server 
	 * @param array $params 
	 * @return array
	 */
	public function query($server, $params) {
		include_once(ABSPATH.WPINC.'/class-IXR.php');
		
		$rpc = @new IXR_Client(trailingslashit($server).'xmlrpc.php');
		$status = call_user_func_array(array($rpc, 'query'), $params);

		if( !$status ) {
			$success = false;
		    $response = $this->encode(serialize('<div class="error"><p><strong>Error ('.$rpc->getErrorCode().'):</strong> '.$rpc->getErrorMessage().' - on host: '.$server.'</p></div>'));
		}
		else {
			$success = true;
			$response = $rpc->getResponse();
		}

		return compact('success', 'response');
	}
	
// receive 

	/**
	 * Receive an IXR message
	 *
	 * @param array $args
	 * @return mixed
	 */
	public function receive($args) {
		$args[4] = unserialize($this->decode($args[4]));

		try {
			$params = $this->parse_request($args);

			if ($args[3] == 'import_batch_open') {
				cfd_tmp_dbg('import.txt', '', 'print');
			}
			elseif($args[3] == 'import_batch' || $args[3] == 'import_batch_extra') {
				cfd_tmp_dbg('import.txt', $params, 'print', true);
			}
			
			if (!($params instanceof IXR_Error)) {
				extract($params);
				$ret = $this->$method($args);
			}
			else {
				$ret = $params;
			}
		}
		catch(cfd_xmlrpc_exception $e) {
			$ret = $e->getIXRError();
		}
		catch(Exception $e) {
			$ret = new IXR_Error($e->getCode(), $e->getMessage());
		}

		cfd_tmp_dbg('receive-result.txt', $ret, 'print');
		
		return $this->encode(serialize($ret));
	}
	
	/**
	 * Monster validation routine prior to accepting any data
	 *
	 * @param array $args 
	 * @return mixed
	 */
	public function parse_request($params) {
		cfd_tmp_dbg('cf-data-received.txt', $params, 'print');
		
		list($blog_id, $username, $auth_key, $method, $args, $sekret, $val_hash) = $params;		
		
		# sekret key validation
		if($sekret != $this->sekret($this->options['auth_key'], $method, $args, $username)) {
			return new IXR_Error('401', __('Unauthorized: key match failure', 'cf-deploy'));
		}
		
		# key validation
		if (!method_exists($this, $method)) {
			return new IXR_Error('400', sprintf(__('Invalid Request: method `%s` does not exist', 'cf-deploy'), $method));
		}
		elseif (empty($this->options['auth_key'])) {
			return new IXR_Error(401, __('Unauthorized: auth key not configured', 'cf-deploy'));
		}
		
		# hash validation, cheap way to ensure that the data safely made it from there to here intact
		if (md5(serialize($args)) != $val_hash) {
			cfd_tmp_dbg('cf-data-received.txt', PHP_EOL.PHP_EOL.'---- calculated local hash: '.md5(serialize($args)), '', true);
			return new IXR_Error('400', __('Validation hash mismatch', 'cf-deploy'));
		}

		# user validation
		if (!$user = get_userdatabylogin($username)) {
			cfd_tmp_dbg('cf-data-received.txt', PHP_EOL.PHP_EOL.'---- local get_userdatabylogin: '.PHP_EOL.PHP_EOL.print_r($user, true), '', true);
			return new IXR_Error(401, __('Invalid Username', 'cf-deploy'));
		}
		$user = new WP_User($user->ID);
		if (!$user->has_cap(apply_filters('cf-deploy-user-permissions', CF_DEPLOY_CAPABILITIES))) {
			cfd_tmp_dbg('/tmp/cf-data-received.txt', PHP_EOL.PHP_EOL.'---- local user object: '.PHP_EOL.PHP_EOL.print_r($user, true), '', true);
			return new IXR_Error(401, __('Unauthorized: not allowed', 'cf-deploy'));
		}
		
		// user is ok, set as current user
		wp_set_current_user($user->ID, $user->user_login);
		
		return compact('args', 'method', 'blog_id');
	}

// std test func
	
	/**
	 * Test communications back to calling server
	 * Will fail if
	 * - can't load test file from remote server (url passed in args)
	 * - can't modify the memory limit on this server
	 *
	 * @see cf_deploy_admin::ajax_test_comms_settings() for calling method
	 * @param string $args 
	 * @return void
	 */
	protected function ixr_comms_test($args) {
		$message = new cfd_message(array(
			'success' => true,
			'message' => '',
			'type' => 'comms-test-message'
		));
		
		// test memory limit modifications
		$old_memory_limit = ini_get('memory_limit');
		@ini_set('memory_limit', '512M');
		if(ini_get('memory_limit') != '512M') {
			$message->success = false;
			$message->message .= '<p>'.sprintf(__('Could not modify ram limit on the server "%s". RAMP requires the ability to raise the ram limit during batch operations.', 'cf-deploy'), get_bloginfo('url')).'<p>';
		}
		else {
			ini_set('memory_limit', $old_memory_limit);
		}
		
		// test communications with calling server (for image pull & the like)
		$f = @wp_remote_get(esc_url($args['calling_from']));
		if ($f['response']['code'] >= 400) {
			$message->success = false;
			$message->message .= '<p>'.sprintf(__('Could not reach calling server (%s). Status: %s - %s'), esc_url($args['calling_from']), $f['response']['code'], $f['response']['message']).'</p>';
		}
		
		return $message;
	}
	
	/**
	 * Testing, testing, is this thing on?
	 *
	 * @param array $args 
	 * @return string
	 */
	protected function hello($args) {
		return 'hello to you too, '.(!empty($args['name']) ? $args['name'] : 'bub').'!';
	}
}

?>