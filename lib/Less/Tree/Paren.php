<?php

class Less_Tree_Paren extends Less_Tree{

	public $value;
	public $type = 'Paren';

	public function __construct($value) {
		$this->value = $value;
	}

	function accept($visitor){
		$this->value = $visitor->visitObj($this->value);
	}

	function genCSS( $env, &$strs ){
		self::OutputAdd( $strs, '(' );
		$this->value->genCSS( $env, $strs );
		self::OutputAdd( $strs, ')' );
	}

	public function compile($env) {
		return new Less_Tree_Paren($this->value->compile($env));
	}

}
