<?php


class Less_Tree_Variable extends Less_Tree{

	public $name;
	public $index;
	public $currentFileInfo;
	public $evaluating = false;
	public $type = 'Variable';

    public function __construct($name, $index, $currentFileInfo = null) {
        $this->name = $name;
        $this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
    }

	public function compile($env) {
		$name = $this->name;
		if (strpos($name, '@@') === 0) {
			$v = new Less_Tree_Variable(substr($name, 1), $this->index + 1);
			$name = '@' . $v->compile($env)->value;
		}

		if ($this->evaluating) {
			throw new Less_Exception_Compiler("Recursive variable definition for " . $name, $this->index, null, $this->currentFileInfo['filename']);
		}

		$this->evaluating = true;


		foreach($env->frames as $frame){
			if( $v = $frame->variable($name) ){
				$this->evaluating = false;
				return $v->value->compile($env);
			}
		}

		throw new Less_Exception_Compiler("variable " . $name . " is undefined", $this->index, null);
	}

}
