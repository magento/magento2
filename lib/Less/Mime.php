<?php

/**
 * Mime lookup
 *
 * @package Less
 * @subpackage node
 */
class Less_Mime{

	// this map is intentionally incomplete
	// if you want more, install 'mime' dep
	static $_types = array(
	        '.htm' => 'text/html',
	        '.html'=> 'text/html',
	        '.gif' => 'image/gif',
	        '.jpg' => 'image/jpeg',
	        '.jpeg'=> 'image/jpeg',
	        '.png' => 'image/png'
	        );

	static function lookup( $filepath ){
		$parts = explode('.',$filepath);
		$ext = '.'.strtolower(array_pop($parts));

		if( !isset(self::$_types[$ext]) ){
			return null;
		}
		return self::$_types[$ext];
	}

	static function charsets_lookup( $type = null ){
		// assumes all text types are UTF-8
		return $type && preg_match('/^text\//',$type) ? 'UTF-8' : '';
	}
}