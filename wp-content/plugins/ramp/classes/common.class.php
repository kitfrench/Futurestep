<?php

class cfd_common {
	
// Data Cleanup

	/**
	 * Convert objects to arrays
	 *
	 * @param array $data 
	 * @return array
	 */
	public function object_to_array(&$data) {
		if (is_object($data)) {
			$data = get_object_vars($data);
			// array_walk_recursive won't traverse down in to converted objects, so handle them manually
			array_walk_recursive($data, array($this, 'object_to_array'));
		}
		
		// dereference arrays 'cause PHP won't
		if (is_array($data)) {
			$d = $data;
			$data = array();
			foreach ($d as $key => $value) {
				$data[$key] = $value;
			}
		}
		
		return $data;
	}

	public function trim_scalar(&$data) {
		if (is_scalar($data)) {
			// pull out leading and trailing whitespace
			$data = trim($data);
			// normalize line endings as XMLRPC doesn't seem to like \r\n, but does like just \n
			$data = preg_replace('/(?>\r\n|\n|\x0b|\f|\r|\x85)/', "\n", $data); 
		}
		
		// IXR doesn't like nulls, so make 'em empty strings
		if (is_null($data)) {
			$data = '';
		}
		
		return $data;
	}

}

?>