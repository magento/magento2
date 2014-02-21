<?php

class Less_Visitor_joinSelector extends Less_Visitor{

	public $contexts = array( array() );

	function run( $root ){
		return $this->visitObj($root);
	}

	function visitRule( $ruleNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	function visitMixinDefinition( $mixinDefinitionNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	function visitRuleset( $rulesetNode ){

		$paths = array();

		if( !$rulesetNode->root ){
			$selectors = array();

			if( $rulesetNode->selectors && count($rulesetNode->selectors) ){
				foreach($rulesetNode->selectors as $selector){
					if( $selector->getIsOutput() ){
						$selectors[] = $selector;
					}
				}
			}

			if( !count($selectors) ){
				$rulesetNode->selectors = $selectors = null;
				$rulesetNode->rules = null;
			}else{
				$context = end($this->contexts); //$context = $this->contexts[ count($this->contexts) - 1];
				$paths = $rulesetNode->joinSelectors( $context, $selectors);
			}

			$rulesetNode->paths = $paths;
		}

		$this->contexts[] = $paths; //different from less.js. Placed after joinSelectors() so that $this->contexts will get correct $paths
	}

	function visitRulesetOut( $rulesetNode ){
		array_pop($this->contexts);
	}

	function visitMedia($mediaNode) {
		$context = end($this->contexts); //$context = $this->contexts[ count($this->contexts) - 1];

		if( !count($context) || (is_object($context[0]) && @$context[0]->multiMedia) ){
			$mediaNode->rules[0]->root = true;
		}
	}

}

