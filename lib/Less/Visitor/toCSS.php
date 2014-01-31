<?php

class Less_Visitor_toCSS extends Less_VisitorReplacing{

	function __construct($env){
		$this->_env = $env;
		parent::__construct();
	}

	function run( $root ){
		return $this->visitObj($root);
	}

	function visitRule( $ruleNode ){
		if( $ruleNode->variable ){
			return array();
		}
		return $ruleNode;
	}

	function visitMixinDefinition( $mixinNode ){
		return array();
	}

	function visitExtend( $extendNode ){
		return array();
	}

	function visitComment( $commentNode ){
		if( $commentNode->isSilent( $this->_env) ){
			return array();
		}
		return $commentNode;
	}

	function visitMedia( $mediaNode, &$visitDeeper ){
		$mediaNode->accept($this);
		$visitDeeper = false;

		if( !$mediaNode->rules ){
			return array();
		}
		return $mediaNode;
	}

	function visitDirective( $directiveNode ){
		if( isset($directiveNode->currentFileInfo['reference']) && (!property_exists($directiveNode,'isReferenced') || !$directiveNode->isReferenced) ){
			return array();
		}
		if( $directiveNode->name === '@charset' ){
			// Only output the debug info together with subsequent @charset definitions
			// a comment (or @media statement) before the actual @charset directive would
			// be considered illegal css as it has to be on the first line
			if( isset($this->charset) && $this->charset ){

				//if( $directiveNode->debugInfo ){
				//	$comment = new Less_Tree_Comment('/* ' . str_replace("\n",'',$directiveNode->toCSS($this->_env))." */\n");
				//	$comment->debugInfo = $directiveNode->debugInfo;
				//	return $this->visit($comment);
				//}


				return array();
			}
			$this->charset = true;
		}
		return $directiveNode;
	}

	function checkPropertiesInRoot( $rules ){
		foreach($rules as $ruleNode){
			if( $ruleNode instanceof Less_Tree_Rule && !$ruleNode->variable ){
				$msg = "properties must be inside selector blocks, they cannot be in the root. Index ".$ruleNode->index.($ruleNode->currentFileInfo ? (' Filename: '.$ruleNode->currentFileInfo['filename']) : null);
				throw new Less_Exception_Compiler($msg);
			}
		}
	}

	function visitRuleset( $rulesetNode, &$visitDeeper ){

		$visitDeeper = false;
		$rulesets = array();
		if( $rulesetNode->firstRoot ){
			$this->checkPropertiesInRoot( $rulesetNode->rules );
		}
		if( !$rulesetNode->root ){

			$paths = array();
			foreach($rulesetNode->paths as $p){
				if( $p[0]->elements[0]->combinator->value === ' ' ){
					$p[0]->elements[0]->combinator = new Less_Tree_Combinator('');
				}

				foreach($p as $pi){
					if( $pi->getIsReferenced() && $pi->getIsOutput() ){
						$paths[] = $p;
						break;
					}
				}
			}

			$rulesetNode->paths = $paths;

			// Compile rules and rulesets
			$nodeRuleCnt = count($rulesetNode->rules);
			for( $i = 0; $i < $nodeRuleCnt; ){
				$rule = $rulesetNode->rules[$i];

				if( property_exists($rule,'rules') ){
					// visit because we are moving them out from being a child
					$rulesets[] = $this->visitObj($rule);
					array_splice($rulesetNode->rules,$i,1);
					$nodeRuleCnt--;
					continue;
				}
				$i++;
			}


			// accept the visitor to remove rules and refactor itself
			// then we can decide now whether we want it or not
			if( $nodeRuleCnt > 0 ){
				$rulesetNode->accept($this);

				if( $rulesetNode->rules ){

					if( count($rulesetNode->rules) >  1 ){
						$this->_mergeRules( $rulesetNode->rules );
						$this->_removeDuplicateRules( $rulesetNode->rules );
					}

					// now decide whether we keep the ruleset
					if( $rulesetNode->paths ){
						//array_unshift($rulesets, $rulesetNode);
						array_splice($rulesets,0,0,array($rulesetNode));
					}
				}

			}

		}else{
			$rulesetNode->accept( $this );
			if( $rulesetNode->firstRoot || $rulesetNode->rules ){
				return $rulesetNode;
				//array_unshift($rulesets, $rulesetNode);
			}
			return $rulesets;
		}

		if( count($rulesets) === 1 ){
			return $rulesets[0];
		}
		return $rulesets;
	}

	function _removeDuplicateRules( &$rules ){
		// remove duplicates
		$ruleCache = array();
		for( $i = count($rules)-1; $i >= 0 ; $i-- ){
			$rule = $rules[$i];
			if( $rule instanceof Less_Tree_Rule ){
				if( !isset($ruleCache[$rule->name]) ){
					$ruleCache[$rule->name] = $rule;
				}else{
					$ruleList =& $ruleCache[$rule->name];
					if( $ruleList instanceof Less_Tree_Rule ){
						$ruleList = $ruleCache[$rule->name] = array( $ruleCache[$rule->name]->toCSS($this->_env) );
					}
					$ruleCSS = $rule->toCSS($this->_env);
					if( array_search($ruleCSS,$ruleList) !== false ){
						array_splice($rules,$i,1);
					}else{
						$ruleList[] = $ruleCSS;
					}
				}
			}
		}
	}

	function _mergeRules( &$rules ){
		$groups = array();

		$rules_len = count($rules);
		for( $i = 0; $i < $rules_len; $i++ ){
			$rule = $rules[$i];

			if( ($rule instanceof Less_Tree_Rule) && $rule->merge ){

				$key = $rule->name;
				if( $rule->important ){
					$key .= ',!';
				}

				if( !isset($groups[$key]) ){
					$groups[$key] = array();
					$parts =& $groups[$key];
				}else{
					array_splice($rules, $i--, 1);
					$rules_len--;
				}

				$parts[] = $rule;
			}
		}

		foreach($groups as $parts){

			if( count($parts) > 1 ){
				$rule = $parts[0];

				$values = array();
				foreach($parts as $p){
					$values[] = $p->value;
				}

				$rule->value = new Less_Tree_Value( $values );
			}
		}
	}
}

