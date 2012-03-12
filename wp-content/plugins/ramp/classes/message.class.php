<?php

class cfd_message {
	public $is_cfd_message = 1; # helper for identifying cfd_messages on the other end of IXR communications
	public $message;	# actual message data, can be anything
	public $type;	# string, short message type description
	public $success;	# bool, self explanatory
	
	public function __construct($args = array('success' => false, 'type' => null, 'type' => null)) {
		if (!is_array($args)) {
			$this->import_json($args);
		}
		else {
			$this->add($args);
		}
	}
	
	public function add(array $args = array('success' => false, 'message' => null, 'type' => null)) {
		$this->success = (bool) $args['success'];	# force bool
		$this->message = $args['message'];			# message will vary wildly
		$this->type = strval($args['type']);		# force string
	}

// Getters & setters
	public function __get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		}
		else {
			return false;
		}
	}
	
	public function __isset($var) {
		return isset($this->$var);
	}
	
	public function __set($var, $val) {
		return $this->$var = $val;
	}
	
	public function __unset($var) {
		unset($this->$var);
		return !isset($this->$var);
	}

	public function get_results() {
		return array(
			'success' => trim($this->success),
			'message' => $this->message,
			'type' => trim($this->type)
		);
	}
	
	public function get_json() {
		return json_encode($this->get_results());
	}
	
	public function import_json($json) {
		$array = json_decode($json, true);
		if (is_array($array)) {
			$this->message = $array['message'];
			$this->type = $array['type'];
			$this->success = (bool) $array['success'];
		}
	}

	public function __toString() {
		return $this->get_json();
	}

// Delivery
	/**
	 * Deliver the JSON and get out of the page load.
	 *
	 * @return void
	 */
	public function send() {
		header('Content-type: application/json');
		echo $this->get_json();
		exit;
	}
}

?>