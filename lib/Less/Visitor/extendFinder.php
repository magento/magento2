<?php


class Less_Visitor_extendFinder extends Less_Visitor{

	public $contexts = array();
	public $allExtendsStack;
	public $foundExtends;

	function __construct(){
		$this->contexts = array();
		$this->allExtendsStack = array(array());
		parent::__construct();
	}

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
		$rules = $rulesetNode->rules;
		$ruleCnt = count($rules);
		for($i = 0; $i < $ruleCnt; $i++ ){
			if( $rules[$i] instanceof Less_Tree_Extend ){
				$allSelectorsExtendList[] = $rules[$i];
				$rulesetNode->extendOnEveryPath = true;
			}
		}



		// now find every selector and apply the extends that apply to all extends
		// and the ones which apply to an individual extend
		$paths = $rulesetNode->paths;
		$paths_len = count($paths);
		for($i = 0; $i < $paths_len; $i++ ){

			$selectorPath = $paths[$i];
			$selector = end($selectorPath); //$selectorPath[ count($selectorPath)-1];


			$list = array_merge($selector->extendList, $allSelectorsExtendList);

			$extendList = array();
			foreach($list as $allSelectorsExtend){
				$extendList[] = clone $allSelectorsExtend;
			}

			$extendList_len = count($extendList);
			for($j = 0; $j < $extendList_len; $j++ ){
				$this->foundExtends = true;
				$extend = $extendList[$j];
				$extend->findSelfSelectors( $selectorPath );
				$extend->ruleset = $rulesetNode;
				if( $j === 0 ){ $extend->firstExtendOnThisSelectorPath = true; }

				$temp = count($this->allExtendsStack)-1;
				$this->allExtendsStack[ $temp ][] = $extend;
			}
		}

		$this->contexts[] = $rulesetNode->selectors;
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

	function visitMediaOut( $mediaNode ){
		array_pop($this->allExtendsStack);
	}

	function visitDirective( $directiveNode ){
		$directiveNode->allExtends = array();
		$this->allExtendsStack[] =& $directiveNode->allExtends;
	}

	function visitDirectiveOut( $directiveNode ){
		array_pop($this->allExtendsStack);
	}
}


