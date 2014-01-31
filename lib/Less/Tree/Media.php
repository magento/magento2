<?php

class Less_Tree_Media extends Less_Tree{

	public $features;
	public $ruleset;
	public $type = 'Media';

	public function __construct($value = array(), $features = array(), $index = null, $currentFileInfo = null ){

		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;

		$selectors = $this->emptySelectors();

		$this->features = new Less_Tree_Value($features);

		$this->rules = array(new Less_Tree_Ruleset($selectors, $value));
		$this->rules[0]->allowImports = true;
	}

	function accept( $visitor ){
		$this->features = $visitor->visitObj($this->features);
		$this->rules = $visitor->visitArray($this->rules);
	}

	function genCSS( $env, &$strs ){

		self::OutputAdd( $strs, '@media ', $this->currentFileInfo, $this->index );
		$this->features->genCSS( $env, $strs );
		Less_Tree::outputRuleset( $env, $strs, $this->rules);

	}

	public function compile($env) {

		$media = new Less_Tree_Media(array(), array(), $this->index, $this->currentFileInfo );

		$strictMathBypass = false;
		if( $env->strictMath === false) {
			$strictMathBypass = true;
			$env->strictMath = true;
		}
		try {
			$media->features = $this->features->compile($env);
		}catch(Exception $e){}

		if( $strictMathBypass ){
			$env->strictMath = false;
		}

		$env->mediaPath[] = $media;
		$env->mediaBlocks[] = $media;

		array_unshift($env->frames, $this->rules[0]);
		$media->rules = array($this->rules[0]->compile($env));
		array_shift($env->frames);

		array_pop($env->mediaPath);

		return !$env->mediaPath ? $media->compileTop($env) : $media->compileNested($env);
	}

	public function variable($name) {
		return $this->rules[0]->variable($name);
	}

	public function find($selector) {
		return $this->rules[0]->find($selector, $this);
	}

	public function emptySelectors(){
		$el = new Less_Tree_Element('','&', $this->index, $this->currentFileInfo );
		return array( new Less_Tree_Selector(array($el), array(), null, $this->index, $this->currentFileInfo) );
	}

	public function markReferenced(){
		$this->isReferenced = true;
		foreach($this->rules[0]->rules as $rule){
			if( Less_Parser::is_method($rule,'markReferenced') ){
				$rule->markReferenced();
			}
		}
	}

	// evaltop
	public function compileTop($env) {
		$result = $this;

		if (count($env->mediaBlocks) > 1) {
			$selectors = $this->emptySelectors();
			$result = new Less_Tree_Ruleset($selectors, $env->mediaBlocks);
			$result->multiMedia = true;
		}

		$env->mediaBlocks = array();
		$env->mediaPath = array();

		return $result;
	}

	public function compileNested($env) {
		$path = array_merge($env->mediaPath, array($this));

		// Extract the media-query conditions separated with `,` (OR).
		foreach ($path as $key => $p) {
			$value = $p->features instanceof Less_Tree_Value ? $p->features->value : $p->features;
			$path[$key] = is_array($value) ? $value : array($value);
		}

		// Trace all permutations to generate the resulting media-query.
		//
		// (a, b and c) with nested (d, e) ->
		//	a and d
		//	a and e
		//	b and c and d
		//	b and c and e

		$permuted = $this->permute($path);
		$expressions = array();
		foreach($permuted as $path){

			for( $i=0, $len=count($path); $i < $len; $i++){
				$path[$i] = Less_Parser::is_method($path[$i], 'toCSS') ? $path[$i] : new Less_Tree_Anonymous($path[$i]);
			}

			for( $i = count($path) - 1; $i > 0; $i-- ){
				array_splice($path, $i, 0, array(new Less_Tree_Anonymous('and')));
			}

			$expressions[] = new Less_Tree_Expression($path);
		}
		$this->features = new Less_Tree_Value($expressions);



		// Fake a tree-node that doesn't output anything.
		return new Less_Tree_Ruleset(array(), array());
	}

	public function permute($arr) {
		if (!$arr)
			return array();

		if (count($arr) == 1)
			return $arr[0];

		$result = array();
		$rest = $this->permute(array_slice($arr, 1));
		foreach ($rest as $r) {
			foreach ($arr[0] as $a) {
				$result[] = array_merge(
					is_array($a) ? $a : array($a),
					is_array($r) ? $r : array($r)
				);
			}
		}

		return $result;
	}

	function bubbleSelectors($selectors) {
		$this->rules = array(new Less_Tree_Ruleset( $selectors, array($this->rules[0])));
	}

}
