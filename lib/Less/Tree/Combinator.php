<?php


class Less_Tree_Combinator extends Less_Tree{

	public $value;
	public $type = 'Combinator';

	public function __construct($value = null) {
		if( $value == ' ' ){
			$this->value = ' ';
		}else {
			$this->value = trim($value);
		}
	}

	static $_outputMap = array(
		''  => '',
		' ' => ' ',
		':' => ' :',
		'+' => ' + ',
		'~' => ' ~ ',
		'>' => ' > ',
		'|' => '|'
	);

	static $_outputMapCompressed = array(
		''  => '',
		' ' => ' ',
		':' => ' :',
		'+' => '+',
		'~' => '~',
		'>' => '>',
		'|' => '|'
	);

	function genCSS($env, &$strs ){
		if( Less_Environment::$compress ){
			self::OutputAdd( $strs, self::$_outputMapCompressed[$this->value] );
		}else{
			self::OutputAdd( $strs, self::$_outputMap[$this->value] );
		}
	}

}
