<?php

/**
 * Extend Finder Visitor
 *
 * @package Less
 * @subpackage visitor
 */
class Less_Visitor_extendFinder extends Less_Visitor{

	public $contexts = array();
	public $allExtendsStack;
	public $foundExtends;

	function __construct(){
		$this->contexts = array();
		$this->allExtendsStack = array(array());
		parent::__construct();
	}

	/**
	 * @param Less_Tree_Ruleset $root
	 */
	function run($root){
		$root = $this->visitObj($root);
		$root->allExtends =& $this->allExtendsStack[0];
		return $root;
	}

	function visitRule($ruleNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	function visitMixinDefinition( $mixinDefinitionNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	function visitRuleset($rulesetNode){

		if( $rulesetNode->root ){
			return;
		}

		$allSelectorsExtendList = array();

		// get &:extend(.a); rules which apply to all selectors in this ruleset
		if( $rulesetNode->rules ){
			foreach($rulesetNode->rules as $rule){
				if( $rule instanceof Less_Tree_Extend ){
					$allSelectorsExtendList[] = $rule;
					$rulesetNode->extendOnEveryPath = true;
				}
			}
		}


		// now find every selector and apply the extends that apply to all extends
		// and the ones which apply to an individual extend
		foreach($rulesetNode->paths as $selectorPath){
			$selector = end($selectorPath); //$selectorPath[ count($selectorPath)-1];

			$j = 0;
			foreach($selector->extendList as $extend){
				$this->allExtendsStackPush($rulesetNode, $selectorPath, $extend, $j);
			}
			foreach($allSelectorsExtendList as $extend){
				$this->allExtendsStackPush($rulesetNode, $selectorPath, $extend, $j);
			}
		}

		$this->contexts[] = $rulesetNode->selectors;
	}

	function allExtendsStackPush($rulesetNode, $selectorPath, $extend, &$j){
		$this->foundExtends = true;
		$extend = clone $extend;
		$extend->findSelfSelectors( $selectorPath );
		$extend->ruleset = $rulesetNode;
		if( $j === 0 ){
			$extend->firstExtendOnThisSelectorPath = true;
		}

		$end_key = count($this->allExtendsStack)-1;
		$this->allExtendsStack[$end_key][] = $extend;
		$j++;
	}


	function visitRulesetOut( $rulesetNode ){
		if( !is_object($rulesetNode) || !$rulesetNode->root ){
			array_pop($this->contexts);
		}
	}

	function visitMedia( $mediaNode ){
		$mediaNode->allExtends = array();
		$this->allExtendsStack[] =& $mediaNode->allExtends;
	}

	function visitMediaOut(){
		array_pop($this->allExtendsStack);
	}

	function visitDirective( $directiveNode ){
		$directiveNode->allExtends = array();
		$this->allExtendsStack[] =& $directiveNode->allExtends;
	}

	function visitDirectiveOut(){
		array_pop($this->allExtendsStack);
	}
}


