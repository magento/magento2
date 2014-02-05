<?php


class Less_Tree_Negative extends Less_Tree{

	public $value;
	public $type = 'Negative';

	function __construct($node){
		$this->value = $node;
	}

	//function accept($visitor) {
	//	$this->value = $visitor->visit($this->value);
	//}

	function genCSS( $env, &$strs ){
		self::OutputAdd( $strs, '-' );
		$this->value->genCSS( $env, $strs );
	}

	function compile($env) {
		if( $env->isMathOn() ){
			$ret = new Less_Tree_Operation('*', array( new Less_Tree_Dimension(-1), $this->value ) );
			return $ret->compile($env);
		}
		return new Less_Tree_Negative( $this->value->compile($env) );
	}
}