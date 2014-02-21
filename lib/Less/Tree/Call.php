<?php


//
// A function call node.
//

class Less_Tree_Call extends Less_Tree{
    public $value;

    var $name;
    var $args;
    var $index;
    var $currentFileInfo;
    public $type = 'Call';

	public function __construct($name, $args, $index, $currentFileInfo = null ){
		$this->name = $name;
		$this->args = $args;
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
	}

	function accept( $visitor ){
		$this->args = $visitor->visitArray( $this->args );
	}

    //
    // When evaluating a function call,
    // we either find the function in `tree.functions` [1],
    // in which case we call it, passing the  evaluated arguments,
    // or we simply print it out as it appeared originally [2].
    //
    // The *functions.js* file contains the built-in functions.
    //
    // The reason why we evaluate the arguments, is in the case where
    // we try to pass a variable to a function, like: `saturate(@color)`.
    // The function should receive the value, not the variable.
    //
    public function compile($env){
		$args = array();
		foreach($this->args as $a){
			$args[] = $a->compile($env);
		}

		$name = $this->name;
		switch($name){
			case '%':
			$name = '_percent';
			break;

			case 'data-uri':
			$name = 'datauri';
			break;

			case 'svg-gradient':
			$name = 'svggradient';
			break;
		}


		if( is_callable( array('Less_Functions',$name) ) ){ // 1.
			try {
				$func = new Less_Functions($env, $this->currentFileInfo);
				$result = call_user_func_array( array($func,$name),$args);
				if( $result != null ){
					return $result;
				}

			} catch (Exception $e) {
				throw new Less_Exception_Compiler('error evaluating function `' . $this->name . '` '.$e->getMessage().' index: '. $this->index);
			}

		}

		return new Less_Tree_Call( $this->name, $args, $this->index, $this->currentFileInfo );
    }

	public function genCSS( $env, &$strs ){

		self::OutputAdd( $strs, $this->name . '(', $this->currentFileInfo, $this->index );
		$args_len = count($this->args);
		for($i = 0; $i < $args_len; $i++ ){
			$this->args[$i]->genCSS($env, $strs );
			if( $i + 1 < $args_len ){
				self::OutputAdd( $strs, ', ' );
			}
		}

		self::OutputAdd( $strs, ')' );
	}

    public function toCSS($env = null){
        return $this->compile($env)->toCSS();
    }

}
