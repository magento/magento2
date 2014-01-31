<?php

/*
class Less_Visitor_import extends Less_VisitorReplacing{

	public $_visitor;
	public $_importer;
	public $importCount;

	function __construct( $importer = null, $evalEnv = null ){
		$this->_visitor = new Less_Visitor($this);
		$this->_importer = $importer;
		if( $evalEnv ){
			$this->env = $evalEnv;
		}else{
			$this->env = new Less_Environment();
		}
		$this->importCount = 0;
	}


	function run( $root ){
		// process the contents
		$this->_visitor->visitObj($root);

		$this->isFinished = true;

		//if( $this->importCount === 0) {
		//	$this->_finish();
		//}
	}

	function visitImport($importNode, &$visitArgs ){
		$importVisitor = $this;

		$visitArgs['visitDeeper'] = false;

		if( $importNode->css ){
			return $importNode;
		}

		$evaldImportNode = $importNode->compileForImport($this->env);

		if( $evaldImportNode && !$evaldImportNode->css ){
			$importNode = $evaldImportNode;
			$this->importCount++;
		}

		return $importNode;
	}


	function visitRule( $ruleNode, &$visitArgs ){
		$visitArgs['visitDeeper'] = false;
		return $ruleNode;
	}

	function visitDirective($directiveNode, $visitArgs){
		array_unshift($this->env->frames,$directiveNode);
		return $directiveNode;
	}

	function visitDirectiveOut($directiveNode) {
		array_shift($this->env->frames);
	}

	function visitMixinDefinition($mixinDefinitionNode, $visitArgs) {
		array_unshift($this->env->frames,$mixinDefinitionNode);
		return $mixinDefinitionNode;
	}

	function visitMixinDefinitionOut($mixinDefinitionNode) {
		array_shift($this->env->frames);
	}

	function visitRuleset($rulesetNode, $visitArgs) {
		array_unshift($this->env->frames,$rulesetNode);
		return $rulesetNode;
	}

	function visitRulesetOut($rulesetNode) {
		array_shift($this->env->frames);
	}

	function visitMedia($mediaNode, $visitArgs) {
		array_unshift($this->env->frames, $mediaNode->ruleset);
		return $mediaNode;
	}

	function visitMediaOut($mediaNode) {
		array_shift($this->env->frames);
	}

}
*/