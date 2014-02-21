<?php


class Less_Exception_Compiler extends Exception {

	private $filename;

	public function __construct($message = null, $code = 0, Exception $previous = null, $filename = null ){
		parent::__construct($message, $code);
		$this->filename = $filename;
	}

	public function getFilename() {
		return $this->filename;
	}

	public function __toString() {
		return $this->message . " (" . $this->filename . ")";
	}
}
