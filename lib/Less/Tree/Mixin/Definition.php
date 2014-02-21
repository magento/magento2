<?php

class Less_Tree_Mixin_Definition extends Less_Tree_Ruleset{
	public $name;
	public $selectors;
	public $params;
	public $arity;
	public $rules;
	public $lookups;
	public $required;
	public $frames;
	public $condition;
	public $variadic;
	public $type = 'MixinDefinition';


	// less.js : /lib/less/tree/mixin.js : tree.mixin.Definition
	public function __construct($name, $params, $rules, $condition, $variadic = false){
		$this->name = $name;
		$this->selectors = array(new Less_Tree_Selector(array( new Less_Tree_Element(null, $name))));

		$this->params = $params;
		$this->condition = $condition;
		$this->variadic = $variadic;
		$this->arity = count($params);
		$this->rules = $rules;
		$this->lookups = array();

		$this->required = 0;
		if( is_array($params) ){
			foreach( $params as $p ){
				if (! isset($p['name']) || ($p['name'] && !isset($p['value']))) {
					$this->required++;
				}
			}
		}

		$this->frames = array();
		$this->SetRulesetIndex();
	}



	//function accept( $visitor ){
	//	$this->params = $visitor->visit($this->params);
	//	$this->rules = $visitor->visit($this->rules);
	//	$this->condition = $visitor->visit($this->condition);
	//}


	public function toCSS($env = null){
		return '';
	}

	// less.js : /lib/less/tree/mixin.js : tree.mixin.Definition.evalParams
	public function compileParams($env, $mixinEnv, $args = array() , &$evaldArguments = array() ){
		$frame = new Less_Tree_Ruleset(null, array());
		$varargs;
		$params = $this->params;
		$val;
		$name;
		$isNamedFound;


		$mixinEnv = clone $mixinEnv;
		$mixinEnv->frames = array_merge( array($frame), $mixinEnv->frames);
		//$mixinEnv = $mixinEnv->copyEvalEnv( array_merge( array($frame), $mixinEnv->frames) );

		$args_len = count($args);
		for($i = 0; $i < $args_len; $i++ ){
			$arg = $args[$i];

			if( $arg && $arg['name'] ){
				$name = $arg['name'];
				$isNamedFound = false;

				foreach($params as $j => $param){
					if( !isset($evaldArguments[$j]) && $name === $params[$j]['name']) {
						$evaldArguments[$j] = $arg['value']->compile($env);
						array_unshift($frame->rules, new Less_Tree_Rule( $name, $arg['value']->compile($env) ) );
						$isNamedFound = true;
						break;
					}
				}
				if ($isNamedFound) {
					array_splice($args, $i, 1);
					$i--;
					$args_len--;
					continue;
				} else {
					throw new Less_Exception_Compiler("Named argument for " . $this->name .' '.$args[$i]['name'] . ' not found');
				}
			}
		}

		$argIndex = 0;
		foreach($params as $i => $param){

			if ( isset($evaldArguments[$i]) ){ continue; }

			$arg = null;
			if( array_key_exists($argIndex,$args) && $args[$argIndex] ){
				$arg = $args[$argIndex];
			}

			if (isset($param['name']) && $param['name']) {
				$name = $param['name'];

				if( isset($param['variadic']) && $args ){
					$varargs = array();
					for ($j = $argIndex; $j < $args_len; $j++) {
						$varargs[] = $args[$j]['value']->compile($env);
					}
					$expression = new Less_Tree_Expression($varargs);
					array_unshift($frame->rules, new Less_Tree_Rule($param['name'], $expression->compile($env)));
				}else{
					$val = ($arg && $arg['value']) ? $arg['value'] : false;

					if ($val) {
						$val = $val->compile($env);
					} else if ( isset($param['value']) ) {
						$val = $param['value']->compile($mixinEnv);
						$frame->resetCache();
					} else {
						throw new Less_Exception_Compiler("Wrong number of arguments for " . $this->name . " (" . count($args) . ' for ' . $this->arity . ")");
					}

					array_unshift($frame->rules, new Less_Tree_Rule($param['name'], $val));
					$evaldArguments[$i] = $val;
				}
			}

			if ( isset($param['variadic']) && $args) {
				for ($j = $argIndex; $j < $args_len; $j++) {
					$evaldArguments[$j] = $args[$j]['value']->compile($env);
				}
			}
			$argIndex++;
		}

		asort($evaldArguments);

		return $frame;
	}

	// less.js : /lib/less/tree/mixin.js : tree.mixin.Definition.eval
	public function compile($env, $args = NULL, $important = NULL) {
		$_arguments = array();

		$mixinFrames = array_merge($this->frames, $env->frames);

		$mixinEnv = new Less_Environment();
		$mixinEnv->addFrames($mixinFrames);

		$frame = $this->compileParams($env, $mixinEnv, $args, $_arguments);



		$ex = new Less_Tree_Expression($_arguments);
		array_unshift($frame->rules, new Less_Tree_Rule('@arguments', $ex->compile($env)));


		$rules = array_slice($this->rules,0);

		$ruleset = new Less_Tree_Ruleset(null, $rules);
		$ruleset->originalRuleset = $this->ruleset_id;


		$ruleSetEnv = $env->copyEvalEnv( array_merge( array($this, $frame), $mixinFrames ) );
		$ruleset = $ruleset->compile( $ruleSetEnv );

		if( $important ){
			$ruleset = $ruleset->makeImportant();
		}
		return $ruleset;
	}


	public function matchCondition($args, $env) {

		if( !$this->condition ){
			return true;
		}

		$frame = $this->compileParams($env, $env->copyEvalEnv(array_merge($this->frames,$env->frames)), $args );

		$compile_env = $env->copyEvalEnv(
			array_merge(
				array($frame)		// the parameter variables
				, $this->frames		// the parent namespace/mixin frames
				, $env->frames		// the current environment frames
			)
		);

		if( !$this->condition->compile($compile_env) ){
			return false;
		}

		return true;
	}

	public function matchArgs($args, $env = NULL){
		$argsLength = count($args);

		if( !$this->variadic ){
			if( $argsLength < $this->required ){
				return false;
			}
			if( $argsLength > count($this->params) ){
				return false;
			}
		}else{
			if( $argsLength < ($this->required - 1)){
				return false;
			}
		}

		$len = min($argsLength, $this->arity);

		for( $i = 0; $i < $len; $i++ ){
			if( !isset($this->params[$i]['name']) && !isset($this->params[$i]['variadic']) ){
				if( $args[$i]['value']->compile($env)->toCSS() != $this->params[$i]['value']->compile($env)->toCSS() ){
					return false;
				}
			}
		}

		return true;
	}

}
