<?php

class Less_Tree_Directive extends Less_Tree{

	public $name;
	public $value;
	public $rules;
	public $index;
	public $type = 'Directive';

	public function __construct($name, $value = null, $index = null, $currentFileInfo = null ){
		$this->name = $name;
		if (is_array($value)) {
			$rule = new Less_Tree_Ruleset(array(), $value);
			$rule->allowImports = true;
			$this->rules = array($rule);
		} else {
			$this->value = $value;
		}
		$this->currentFileInfo = $currentFileInfo;
	}


	function accept( $visitor ){
		if( $this->rules ){
			$this->rules = $visitor->visitArray( $this->rules );
		}
		if( $this->value ){
			$this->value = $visitor->visitObj( $this->value );
		}
	}

	function genCSS( $env, &$strs ){

		self::OutputAdd( $strs, $this->name, $this->currentFileInfo, $this->index );

		if( $this->rules ){
			Less_Tree::outputRuleset( $env, $strs, $this->rules);
		}else{
			self::OutputAdd( $strs, ' ' );
			$this->value->genCSS( $env, $strs );
			self::OutputAdd( $strs, ';' );
		}
	}

	public function compile($env){
		$evaldDirective = $this;
		if( $this->rules ){
			$env->unshiftFrame($this);
			$evaldDirective = new Less_Tree_Directive( $this->name, null, $this->index, $this->currentFileInfo );
			$evaldDirective->rules = array( $this->rules[0]->compile($env) );
			$evaldDirective->rules[0]->root = true;
			$env->shiftFrame();
		}
		return $evaldDirective;
	}

	// TODO: Not sure if this is right...
	public function variable($name){
		return $this->rules[0]->variable($name);
	}

	public function find($selector){
		return $this->rules[0]->find($selector, $this);
	}

	//rulesets: function () { return tree.Ruleset.prototype.rulesets.apply(this.rules[0]); },

	public function markReferenced(){
		$this->isReferenced = true;
		if( $this->rules ){
			$rules = $this->rules[0]->rules;
			for( $i = 0; $i < count($rules); $i++ ){
				if( Less_Parser::is_method( $rules[$i], 'markReferenced') ){
					$rules[$i]->markReferenced();
				}
			}
		}
	}

}
