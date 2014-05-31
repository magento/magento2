<?php

/**
 * DefaultFunc
 *
 * @package Less
 * @subpackage tree
 */
class Less_Tree_DefaultFunc{

	static $error_;
	static $value_;

	static function compile(){
		if( self::$error_ ){
			throw Exception(self::$error_);
		}
		if( self::$value_ !== null ){
			return self::$value_ ? new Less_Tree_Keyword('true') : new Less_Tree_Keyword('false');
		}
	}

	static function value( $v ){
		self::$value_ = $v;
	}

	static function error( $e ){
		self::$error_ = $e;
	}

	static function reset(){
		self::$value_ = self::$error_ = null;
	}
}