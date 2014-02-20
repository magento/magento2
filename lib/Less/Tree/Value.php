<?php


class Less_Tree_Value extends Less_Tree{

	public $type = 'Value';

	public function __construct($value=null){
		$this->value = $value;
	}

	function accept($visitor) {
		$this->value = $visitor->visitArray($this->value);
	}

	public function compile($env){

		$ret = array();
		foreach($this->value as $i => $v){
			$ret[] = $v->compile($env);
		}
		if( $i > 0 ){
			return new Less_Tree_Value($ret);
		}
		return $ret[0];
	}

	function genCSS( $env, &$strs ){
		$len = count($this->value);
		for($i = 0; $i < $len; $i++ ){
			$this->value[$i]->genCSS( $env, $strs);
			if( $i+1 < $len ){
				self::OutputAdd( $strs, Less_Environment::$comma_space );
			}
		}
	}

}
