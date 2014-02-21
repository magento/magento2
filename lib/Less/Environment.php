<?php

//less.js : lib/less/functions.js


class Less_Environment{

	public $paths = array();			// option - unmodified - paths to search for imports on
	static $files = array();			// list of files that have been imported, used for import-once
	public $relativeUrls;				// option - whether to adjust URL's to be relative
	public $rootpath;					// option - rootpath to append to URL's
	public $strictImports = null;		// option -
	public $insecure;					// option - whether to allow imports from insecure ssl hosts
	public static $compress = false;	// option - whether to compress
	public $processImports;				// option - whether to process imports. if false then imports will not be imported
	public $javascriptEnabled;			// option - whether JavaScript is enabled. if undefined, defaults to true
	public $useFileCache;				// browser only - whether to use the per file session cache
	public $currentFileInfo;			// information about the current file - for error reporting and importing and making urls relative etc.

	/**
	 * @var array
	 */
	public $frames = array();


	/**
	 * @var bool
	 */
	public $debug = false;


	/**
	 * @var array
	 */
	public $mediaBlocks = array();

	/**
	 * @var array
	 */
	public $mediaPath = array();

	public $selectors = array();

	public $charset;

	public $parensStack = array();

	public $strictMath = false;

	public $strictUnits = false;

	public $tabLevel = 0;

	public $lastRule = false;

	public $importMultiple = false;

	//public $type = 'Environment';


	public static $comma_space;
	public static $colon_space;
	public static $firstSelector;

	public function __construct( $options = null ){
		$this->frames = array();


		if( isset($options['compress']) ){
			self::$compress = (bool)$options['compress'];
		}
		if( isset($options['strictUnits']) ){
			$this->strictUnits = (bool)$options['strictUnits'];
		}


		if( self::$compress ){
			self::$comma_space = ',';
			self::$colon_space = ':';
		}else{
			self::$comma_space = ', ';
			self::$colon_space = ': ';
		}


	}


	//may want to just use the __clone()?
	public function copyEvalEnv($frames = array() ){

		$evalCopyProperties = array(
			//'silent',      // whether to swallow errors and warnings
			//'verbose',     // whether to log more activity
			//'yuicompress', // whether to compress with the outside tool yui compressor
			//'ieCompat',    // whether to enforce IE compatibility (IE8 data-uri)
			'strictMath',  // whether math has to be within parenthesis
			'strictUnits', // whether units need to evaluate correctly
			//'cleancss',    // whether to compress with clean-css
			//'sourceMap',   // whether to output a source map
			//'importMultiple'// whether we are currently importing multiple copies
			);

		$new_env = new Less_Environment();
		foreach($evalCopyProperties as $property){
			$new_env->$property = $this->$property;
		}
		$new_env->frames = $frames;
		return $new_env;
	}

	public function inParenthesis(){
		$this->parensStack[] = true;
	}

	public function outOfParenthesis() {
		array_pop($this->parensStack);
	}

	public function isMathOn() {
		return $this->strictMath ? !!$this->parensStack : true;
	}

	public static function isPathRelative($path){
		/**
		 * - return !preg_match('/^(?:[a-z-]+:|\/)/',$path);
		 * + return !preg_match('/^(?:[A-Za-z-_]+:|\/)/',$path);
		 *
		 * For identification paths such as Magento_Theme::validation_advice_bg.gif as an absolute paths.
		 */
		return !preg_match('/^(?:[A-Za-z-_]+:|\/)/',$path);
	}


	/**
	 * Canonicalize a path by resolving references to '/./', '/../'
	 * Does not remove leading "../"
	 * @param string path or url
	 * @return string Canonicalized path
	 *
	 */
	static function normalizePath($path){

		$segments = explode('/',$path);
		$segments = array_reverse($segments);

		$path = array();
		$path_len = 0;

		while( $segments ){
			$segment = array_pop($segments);
			switch( $segment ) {

				case '.':
				break;

				case '..':
					if( !$path_len || ( $path[$path_len-1] === '..') ){
						$path[] = $segment;
						$path_len++;
					}else{
						array_pop($path);
						$path_len--;
					}
				break;

				default:
					$path[] = $segment;
					$path_len++;
				break;
			}
		}

		return implode('/',$path);
	}


	/**
	 * @return bool
	 */
	public function getDebug(){
		return $this->debug;
	}

	/**
	 * @param $debug
	 * @return void
	 */
	public function setDebug($debug){
		$this->debug = $debug;
	}

	public function unshiftFrame($frame){
		array_unshift($this->frames, $frame);
	}

	public function shiftFrame(){
		return array_shift($this->frames);
	}

	public function addFrame($frame){
		$this->frames[] = $frame;
	}

	public function addFrames(array $frames){
		$this->frames = array_merge($this->frames, $frames);
	}
}
