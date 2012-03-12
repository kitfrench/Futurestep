<?php

class cfd_exception extends Exception {
	public function __construct($message, $errorCode = null) {
		parent::__construct($message, $errorCode);
		trigger_error('RAMP Exception Handled: '.$message, E_USER_WARNING);
	}
	
	public function getHTML() {
		return '
			<div class="cfd-error">
				<p>'.__($this->getMessage()).'</p>
			</div>
		';
	}
}

// ? maybe not even use? I dunno yet...
class cfd_xmlrpc_exception extends cfd_exception {
	protected $IXRError;
	
	public function __construct($message, $errorCode, $IXRError) {
		Exception::__construct($message, $errorCode);
		$this->setIXRError($IXRError);
	}
	
	public function setIXRError(IXR_Error $IXRError) {
		$this->IXRError = $IXRError;
	}
	
	public function getIXRError() {
		return $this->IXRError;
	}
}

?>