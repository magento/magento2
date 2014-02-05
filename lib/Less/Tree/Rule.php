<?php


class Less_Tree_Rule extends Less_Tree{

	public $name;
	public $value;
	public $important;
	public $merge;
	public $index;
	public $inline;
	public $variable;
	public $currentFileInfo;
	public $type = 'Rule';

	public function __construct($name, $value = null, $important = null, $merge = null, $index = null, $currentFileInfo = null,  $inline = false){
		$this->name = $name;
		$this->value = ($value instanceof Less_Tree_Value) ? $value : new Less_Tree_Value(array($value));
		$this->important = $important ? ' ' . trim($important) : '';
		$this->merge = $merge;
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
		$this->inline = $inline;
		$this->variable = ($name[0] === '@');
	}

	function accept($visitor) {
		$this->value = $visitor->visitObj( $this->value );
	}

	function genCSS( $env, &$strs ){

		self::OutputAdd( $strs, $this->name . Less_Environment::$colon_space, $this->currentFileInfo, $this->index);
		try{
			$this->value->genCSS($env, $strs);

		}catch( Exception $e ){
			$e->index = $this->index;
			$e->filename = $this->currentFileInfo['filename'];
			throw $e;
		}
		self::OutputAdd( $strs, $this->important . (($this->inline || ($env->lastRule && Less_Environment::$compress)) ? "" : ";"), $this->currentFileInfo, $this->index);
	}

	public function compile ($env){

		$return = null;
		$strictMathBypass = false;
		if( $this->name === "font" && !$env->strictMath ){
			$strictMathBypass = true;
			$env->strictMath = true;
		}

		$return = new Less_Tree_Rule($this->name,
									$this->value->compile($env),
									$this->important,
									$this->merge,
									$this->index,
									$this->currentFileInfo,
									$this->inline);

		if( $strictMathBypass ){
			$env->strictMath = false;
		}

		return $return;
	}

	function makeImportant(){
		return new Less_Tree_Rule($this->name, $this->value, '!important', $this->merge, $this->index, $this->currentFileInfo, $this->inline);
	}

}
