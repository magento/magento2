<?php

class Less_Tree_Javascript extends Less_Tree{

	public $type = 'Javascript';

	public function __construct($string, $index, $escaped){
		$this->escaped = $escaped;
		$this->expression = $string;
		$this->index = $index;
	}

	public function compile($env){
		return $this;
	}

	function genCSS( $env, &$strs ){
		self::OutputAdd( $strs, '/* Sorry, can not do JavaScript evaluation in PHP... :( */' );
	}

	public function toCSS($env = null){
		return Less_Environment::$compress ? '' : '/* Sorry, can not do JavaScript evaluation in PHP... :( */';
	}
}
