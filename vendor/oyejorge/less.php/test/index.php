<?php

define('phpless_start_time',microtime());

error_reporting(E_ALL | E_STRICT); //previous to php 5.4, E_ALL did not include E_STRICT
@ini_set('display_errors',1);

error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
set_error_handler(array('ParserTest','showError'),E_ALL | E_STRICT);

set_time_limit(60);
//error_reporting(E_ALL | E_STRICT);

$dir = dirname(dirname(__FILE__));


//get parser
require_once $dir.'/lib/Less/Autoloader.php';
Less_Autoloader::register();
require_once $dir.'/lessc.inc.php';


//? performance improvement (php 5.3+)
//https://github.com/composer/composer/pull/3482
//gc_disable();

//using release
/* require_once $dir.'/test/release/Less.php'; */



//get diff
require( $dir. '/test/php-diff/lib/Diff.php');
require( $dir. '/test/php-diff/lib/Diff/Renderer/Html/SideBySide.php');
require( $dir. '/test/php-diff/lib/Diff/Renderer/Html/Inline.php');


class ParserTest{

	//options
	var $compress = false;
	var $dir;
	var $test_dirs = array('lessjs','bootstrap3');
	var $cache_dir;
	var $head;
	var $files_tested = 0;
	var $matched_count = 0;

	function __construct(){


		$this->cache_dir = dirname(__FILE__).'/_cache';

		if( !file_exists($this->cache_dir) || !is_dir($this->cache_dir) ){
			echo '<p>Invalid cache directory</p>';
		}elseif( !is_writable($this->cache_dir) ){
			echo '<p>Cache directory not writable</p>';
		}


		//get any other possible test folders
		$fixtures_dir = dirname(__FILE__).'/Fixtures';
		$temp = scandir($fixtures_dir);
		foreach($temp as $dir){
			if( $dir == '.' || $dir == '..' ){
				continue;
			}
			$full_path = $fixtures_dir.'/'.$dir.'/less';
			if( !file_exists($full_path) || !is_dir($full_path) ){
				continue;
			}
			$this->test_dirs[] = $dir;
		}
		$this->test_dirs = array_unique($this->test_dirs);


		//Set the directory to test
		if( !empty($_REQUEST['dir']) && in_array($_REQUEST['dir'],$this->test_dirs) ){
			$this->dir = $_REQUEST['dir'];
		}else{
			$this->dir = reset($this->test_dirs);
		}
		$dir = $fixtures_dir.'/'.$this->dir;

		$this->lessJsProvider($dir);
	}

	public function WhichComparisons($dir){

		if( isset($_GET['file']) ){

			//compare single css file
			$full_css = $dir.'/css/'.$_GET['file'].'.css';
			if( file_exists($full_css) ){
				$less = '/less/'.$_GET['file'].'.less';
				$css = '/css/'.$_GET['file'].'.css';
				return array( array($less,$css) );
			}

			//compare single sourcemap
			$full_map = $dir.'/css/'.$_GET['file'].'.map';
			if( file_exists($full_css) ){
				$less = '/less/'.$_GET['file'].'.less';
				$map = '/css/'.$_GET['file'].'.map';
				return array( array($less,false, $map) );
			}

		}


		$list = scandir($dir.'/css');
		foreach($list as $file){

			if( $file === '.' || $file === '..' ){
				continue;
			}
			$type = preg_replace('/.*\.([a-z]+)/','\1',$file);
			if( $type == 'css' ){
				$pairs[] = array('/less/'.str_replace('.css','.less',$file), '/css/'.$file );
			}elseif( $type == 'map' ){
				$pairs[] = array('/less/'.str_replace('.map','.less',$file), false,'/css/'.$file  );
			}

		}

		return $pairs;

	}

    public function lessJsProvider($dir){

		$pairs = $this->WhichComparisons($dir);

		$match_list = $diff = array();
		foreach($pairs as $files){

			$files += array(false,false,false);

			ob_start();
			echo '<div class="row">';
			$matched = $this->testLessJsCssGeneration( $dir, $files[0], $files[1], $files[2] );
			echo '</div>';

			if( $matched ){
				$match_list[] = ob_get_clean();
			}else{
				$diff[] = ob_get_clean();
			}
		}

		if( count($diff) ){

			echo '<div class="row row_heading">';
			echo '<b class="filename">File</b>';
			echo '<b>Less.js CSS</b>';
			echo '<b>Expected Less.php CSS</b>';
			echo '</div>';

			echo implode('',$diff);
		}

		if( count($match_list) ){
			echo '<h3>Matched</h3>';
			foreach($match_list as $a){
				echo $a;
			}
		}

    }

    public function testLessJsCssGeneration($dir, $less, $css, $map ){
		global $obj_buffer;


		$options = array();
		$options['compress'] 		= $this->compress;
		//$options['relativeUrls']	= true;
		//$options['cache_dir']		= $this->cache_dir;
		//$options['cache_method']	= 'php';
		//$options['urlArgs']	= '424242';



		$this->files_tested++;
		$matched		= false;
		$basename		= basename($less);
		$basename 		= substr($basename,0,-5); //remove .less extension
		$file_less		= $dir.$less;
		$file_uri		= $this->AbsoluteToRelative( dirname($file_less) );
		$file_sourcemap	= false;
		$file_css		= false;


		echo '<a class="filename" href="?dir='.rawurlencode($this->dir).'&amp;file='.rawurlencode($basename).'">File: '.$basename.'</a>';

		if( !file_exists($file_less) ){
			echo '<b>LESS file missing: '.$file_less.'</b>';
			return false;
		}


		//css or map
		if( $css ){
			$file_css = $dir.$css;
			$file_expected	= $this->TranslateFile($file_css,'expected','css');
			if( !file_exists($file_css) ){
				echo '<b>CSS file missing: '.$file_css.'</b>';
				return false;
			}
		}elseif( $map ){
			$file_sourcemap	= $dir.$map;
			$file_expected	= $this->TranslateFile($file_sourcemap,'expected','map');
			if( !file_exists($file_sourcemap) ){
				echo '<b>Sourcemap file missing: '.$file_sourcemap.'</b>';
				return false;
			}
		}else{
			echo '<b>Unknown comparison file</b>';
			return false;
		}






		//generate the sourcemap
		if( file_exists($file_sourcemap) ){

			$generated_map = $this->cache_dir.'/'.$basename.'.map';

			$options['sourceMap']			= true;
			$options['sourceMapBasepath']	= $dir;
			$options['sourceMapWriteTo']	= $generated_map;
			$options['sourceMapURL']		= $this->AbsoluteToRelative($generated_map);
		}

		//example, but not very functional import callback
		/*
		$options['import_callback'] = 'callback';
		if( !function_exists('callback') ){
			function callback($evald){
				$evaldPath = $evald->getPath();
				msg('evaldpath: '.$evaldPath);
			}
		}
		*/


		$compiled = '';
		try{

			/**
			 * Less_Cache Testing
			Less_Cache::$cache_dir = $this->cache_dir;
			//$cached_css_file = Less_Cache::Regen( array($file_less=>'') );
			//$options['output'] = md5($file_less).'.css';
			$cached_css_file = Less_Cache::Get( array($file_less=>''), $options );
			$compiled = file_get_contents( $this->cache_dir.'/'.$cached_css_file );
			*/


			$parser = new Less_Parser( $options );
			$parser->parseFile( $file_less ); //$file_uri
			$compiled = $parser->getCss();


			//$parser = new Less_Parser( $options );
			//$less_content = file_get_contents( $file_less );
			//$parser->parse( $less_content );
			//$compiled = $parser->getCss();


			//$parser = new lessc();
			//$compiled = $parser->compileFile($file_less);


			//$parser = new lessc();
			//$compiled = $parser->compile(file_get_contents($file_less));

		}catch(Exception $e){
			$compiled = $e->getMessage();
		}


		//sourcemap comparison
		if( $file_sourcemap ){

			//$expected = file_get_contents($generated_map);
			//$this->SaveExpected($file_expected, $expected);


			$generated_map = $this->ComparableSourceMap($generated_map);
			$file_sourcemap = $this->ComparableSourceMap($file_sourcemap);

			$expected_map = '';
			if( file_exists($file_expected) ){
				$expected_map = $this->ComparableSourceMap($file_expected);
			}


			$matched = $this->CompareFiles( $generated_map, $file_sourcemap, $expected_map);


			if( isset($_GET['file']) ){
				$this->PHPDiff($generated_map,$file_sourcemap,true);
				$this->ObjBuffer();
			}

			return $matched;


		//css comparison
		}else{

			//$this->SaveExpected($file_expected, $compiled);


			$css = file_get_contents($file_css);

			// If compress is enabled, add some whitespaces back for comparison
			if( $this->compress ){
				$compiled = str_replace('{'," {\n",$compiled);
				//$compiled = str_replace('}',"\n}",$compiled);
				$compiled = str_replace(';',";\n",$compiled);
				$compiled = preg_replace('/\s*}\s*/',"\n}\n",$compiled);


				$css = preg_replace('/\n\s+/',"\n",$css);
				$css = preg_replace('/:\s+/',":",$css);
				$css = preg_replace('/;(\s)}/','$1}',$css);
			}

			$css = trim($css);
			$compiled = trim($compiled);

			$expected = '';
			if( file_exists($file_expected) ){
				$expected = file_get_contents($file_expected);
			}

			$matched = $this->CompareFiles( $compiled, $css, $expected);

		}


		if( isset($_GET['file']) ){
			$this->PHPDiff($compiled,$css);
			echo '<table><tr><td>';
			echo '<textarea id="lessphp_textarea" autocomplete="off">'.htmlspecialchars($compiled).'</textarea>';
			echo '</td><td>';
			echo '<textarea id="lessjs_textarea" autocomplete="off"></textarea>';
			echo '</td></tr></table>';
			$this->ObjBuffer();
			$this->LessLink($file_less);
		}


		return $matched;
	}

	function CompareFiles( $generated, $lessjs, $expected ){

		$generated = trim($generated);
		$lessjs = trim($lessjs);

		if( empty($generated) && empty($lessjs) ){
			echo '<b>----empty----</b>';
			return true;
		}

		if( $generated === $lessjs ){
			return true;
		}

		$line_diff = $this->LineDiff($generated,$lessjs);
		echo ' <b>'.$line_diff.' lines mismatched</b>';


		//check agains expected results
		if( $expected ){
			$expected = trim($expected);
			if( $generated == $expected ){
				echo '<span>Expected Matched</span>';
			}else{
				$line_diff = $this->LineDiff($generated,$expected);
				echo ' <b>'.$line_diff.' lines mismatched</b>';
			}

		}else{
			echo '<span>file doesn\'t exist</span>';
		}

		return false;
	}


	/**
	 * Save the results of the compiler
	 * The contents of these files are used by phpunit tests
	 *
	 */
	function SaveExpected($file_expected, $compiled ){
		$name = basename($file_expected);
		$dir = dirname($file_expected);
		if( !is_dir($dir) ){
			msg('Expected directory does not exist: '.$dir);
			return;
		}

		if( file_put_contents($file_expected, $compiled) ){
			msg('Expected results for '.$name.' were saved');
		}
	}


	/**
	 * Change a css file name to a less file name
	 *
	 * eg: /Fixtures/less.js/css/filename.css -> /Fixtures/less.js/less/filename.less
	 *
	 */
	function TranslateFile( $file_css, $dir = 'less', $type = 'less' ){

		$filename = basename($file_css);
		$filename = substr($filename,0,-4);

		return dirname( dirname($file_css) ).'/'.$dir.'/'.$filename.'.'.$type;
	}

    function ObjBuffer(){
		global $obj_buffer;


		if( !empty($obj_buffer) ){
			echo '<h3>Object comparison</h3>';
			echo '<textarea id="object_comparison">'.htmlspecialchars($obj_buffer,ENT_COMPAT,'UTF-8',false).'</textarea>';
		}
		echo '<div id="objectdiff"></div>';
		echo '<div id="diffoutput"></div>';

	}

	function LessLink($less){
		$less = $this->AbsoluteToRelative($less);
		$this->head .= '<link rel="stylesheet/less" type="text/css" href="'.$less.'" />';
	}

	function AbsoluteToRelative($path){
		if( strpos($path,$_SERVER['DOCUMENT_ROOT']) === 0 ){
			$path = substr($path,strlen($_SERVER['DOCUMENT_ROOT']));
		}
		return $path;
	}


	function ComparableSourceMap($file){
		$content = file_get_contents($file);
		$array = json_decode($content,true);
		$array['mappings'] = explode(';',$array['mappings']);
		return pre($array);
	}


	function LineDiff($compiled,$css){

		$compiled	= explode("\n",$compiled);
		$css		= explode("\n",$css);

		$diff1 = array_diff($compiled,$css);
		$diff2 = array_diff($css,$compiled);


		return max( count($diff1), count($diff2) );
	}


	/**
	 * Show diff using php (optional)
	 *
	 */
    function PHPDiff($compiled,$css, $force = false){

		if( !$force && isset($_COOKIE['phpdiff']) && $_COOKIE['phpdiff'] == 0 ){
			return;
		}

		$compiled = explode("\n", $compiled );
		$css = explode("\n", $css );

		$options = array();
		$diff = new Diff($compiled, $css, $options);
		$renderer = new Diff_Renderer_Html_SideBySide();
		//$renderer = new Diff_Renderer_Html_Inline();
		echo $diff->Render($renderer);


		//show the full contents
		/*
		if( isset($_GET['file']) ){
			echo '</table>';
			echo '<table style="width:100%"><tr><td>';
			echo '<pre>';
			echo implode("\n",$compiled);
			echo '</pre>';
			echo '</td><td>';
			echo '<pre>';
			echo implode("\n",$css);
			echo '</pre>';
			echo '</td></tr></table>';
		}
		*/
	}


	function Links(){

		echo '<ul id="links">';
		foreach($this->test_dirs as $dir){
			$class = '';
			if( $dir == $this->dir){
				$class = ' class="active"';
			}
			echo '<li '.$class.'><a href="?dir='.$dir.'">'.$dir.'</a></li>';
		}
		echo '</ul>';
	}

	function Summary(){

		if( !$this->files_tested ){
			return;
		}

		echo '<div id="summary">';

		//success rate
		echo '<fieldset><legend>Success Rate</legend>'.$this->matched_count.' out of '.$this->files_tested.'  files</fieldset>';

		//current memory usage
		$memory = memory_get_usage();
		echo '<fieldset><legend>Memory</legend>'.self::FormatBytes($memory).' ('.number_format($memory).')</fieldset>';

		//max memory usage
		$memory = memory_get_peak_usage();
		echo '<fieldset><legend>Memory Peak</legend>'.self::FormatBytes($memory).' ('.number_format($memory).')</fieldset>';

		//time
		echo '<fieldset><legend>Time (PHP):</legend>'.self::microtime_diff(phpless_start_time,microtime()).'</fieldset>';
		echo '<fieldset><legend>Time (Request)</legend>'.self::microtime_diff($_SERVER['REQUEST_TIME'],microtime()).'</fieldset>';

		echo '</div>';

	}


	function microtime_diff($a, $b = false, $eff = 6) {
		if( !$b ) $b = microtime();
		$a = array_sum(explode(" ", $a));
		$b = array_sum(explode(" ", $b));
		return sprintf('%0.'.$eff.'f', $b-$a);
	}

	static function FormatBytes($size, $precision = 2){
		$base = log($size) / log(1024);
		$suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
		$floor = max(0,floor($base));
		return round(pow(1024, $base - $floor), $precision) .' '. $suffixes[$floor];
	}

	static function Options(){

		//debugging
		$request = str_replace( array('XDEBUG_PROFILE','XDEBUG_TRACE'),'',$_SERVER['REQUEST_URI']);
		echo '<div style="float:right">';
		echo 'XDEBUG: ';
		echo '<a href="'.str_replace('&&','&',$request.'&XDEBUG_PROFILE').'">Debug Profile</a>';
		echo ' - ';
		echo '<a href="'.str_replace('&&','&',$request.'&XDEBUG_TRACE').'">Debug Trace</a>';
		echo '</div>';


		//options
		echo '<div id="options">';
		echo '<b>Options</b>';


		$checked = 'checked="checked"';
		if( isset($_COOKIE['phpdiff']) && $_COOKIE['phpdiff'] == 0 ){
			$checked = '';
		}
		echo '<label><input type="checkbox" name="phpdiff" value="phpdiff" '.$checked.' autocomplete="off"/><span>Show PHP Diff</span></label>';


		echo '</div>';
	}


	/**
	 * Error Handling
	 *
	 */
	static function showError($errno, $errmsg, $filename, $linenum, $vars){
		static $reported = array();


		//readable types
		$errortype = array (
					E_ERROR				=> 'Fatal Error',
					E_WARNING			=> 'Warning',
					E_PARSE				=> 'Parsing Error',
					E_NOTICE 			=> 'Notice',
					E_CORE_ERROR		=> 'Core Error',
					E_CORE_WARNING 		=> 'Core Warning',
					E_COMPILE_ERROR		=> 'Compile Error',
					E_COMPILE_WARNING 	=> 'Compile Warning',
					E_USER_ERROR		=> 'User Error',
					E_USER_WARNING 		=> 'User Warning',
					E_USER_NOTICE		=> 'User Notice',
					E_STRICT			=> 'Strict Notice',
					E_RECOVERABLE_ERROR => 'Recoverable Error',
					E_DEPRECATED		=> 'Deprecated',
					E_USER_DEPRECATED	=> 'User Deprecated',
				 );


		//get the backtrace and function where the error was thrown
		$backtrace = debug_backtrace();

		//remove showError() from backtrace
		if( strtolower($backtrace[0]['function']) == 'showerror' ){
			$backtrace = array_slice($backtrace,1,5);
		}else{
			$backtrace = array_slice($backtrace,0,5);
		}

		//record one error per function and only record the error once per request
		if( isset($backtrace[0]['function']) ){
			$uniq = $filename.$backtrace[0]['function'];
		}else{
			$uniq = $filename.$linenum;
		}

		if( isset($reported[$uniq]) ){
			return false;
		}
		$reported[$uniq] = true;

		//disable showError after 20 errors
		if( count($reported) >= 1 ){
			restore_error_handler();
		}



		//build message
		echo '<fieldset style="padding:1em">';
		echo '<legend>'.$errortype[$errno].' ('.$errno.')</legend> '.$errmsg;
		echo '<br/> &nbsp; &nbsp; <b>in:</b> '.$filename;
		echo '<br/> &nbsp; &nbsp; <b>on line:</b> '.$linenum;
		if( isset($_SERVER['REQUEST_URI']) ){
			echo '<br/> &nbsp; &nbsp; <b>Request:</b> '.$_SERVER['REQUEST_URI'];
		}
		if( isset($_SERVER['REQUEST_METHOD']) ){
			echo '<br/> &nbsp; &nbsp; <b>Method:</b> '.$_SERVER['REQUEST_METHOD'];
		}


		//attempting to entire all data can result in a blank screen
		foreach($backtrace as $i => $trace){
			foreach($trace as $tk => $tv){
				if( is_array($tv) ){
					$backtrace[$i][$tk] = 'array('.count($tv).')';
				}elseif( is_object($tv) ){
					$backtrace[$i][$tk] = 'object '.get_class($tv);
				}
			}
		}

		echo '<div><a href="javascript:void(0)" onclick="var st = this.nextSibling.style; if( st.display==\'block\'){ st.display=\'none\' }else{st.display=\'block\'};return false;">Show Backtrace</a>';
		echo '<div style="display:none">';
		echo pre($backtrace);
		echo '</div></div>';
		echo '</fieldset>';
		return false;
	}
}




/**
 * Output an object in a readable format for comparison with similar output from javascript
 *
 */
function obj($mixed, $objects = array() ){
	$objects_before = $objects;

	global $obj_buffer;
	if( empty($obj_buffer) ){
		$obj_buffer = "----make sure caching is turned off----\n";
	}

	static $level = 0;
	$output = '';


	$exclude_keys = array('originalRuleset','currentFileInfo','lookups','index','ruleset_id','type','_rulesets','_variables','allowImports','_css','cache_string','elements_len',
					'_oelements','first_oelements','_oelements_len','cacheable', ); //'variable','combinator'
	//$exclude_keys = array();

	$type = gettype($mixed);
	switch($type){
		case 'object':

			if( in_array($mixed,$objects,true) ){
				return 'recursive';
			}

			$objects[] = $mixed;
			$type = 'object';

			if( property_exists($mixed,'type') ){
				$type .= ' '.$mixed->type;
			}

			//$type = get_class($mixed).' object';
			//$output = $type.'(...)'."\n"; //recursive object references creates an infinite loop
			$temp = array();
			foreach($mixed as $key => $value){
				//declutter
				if( in_array($key,$exclude_keys,true) ){
					continue;
				}
				$temp[$key] = $value;
			}
			$mixed = $temp;
		case 'array':

			if( !count($mixed) ){
				$output = $type.'()';
				break;
			}

			$output = $type.'('."\n";
			ksort($mixed);
			foreach($mixed as $key => $value){
				$level++;
				$output .= str_repeat('    ',$level) . '[' . $key . '] => ' . obj($value, $objects ) . "\n";
				$level--;
			}
			$output .= str_repeat('    ',$level).')';
		break;
		case 'string':
			$output = '(string:'.strlen($mixed).')'.htmlspecialchars($mixed,ENT_COMPAT,'UTF-8',false).'';
		break;

		case 'integer':
			$type = 'number';
			$output = '(number)'.$mixed;
		break;


		case 'boolean':
			if( $mixed ){
				$mixed = 'true';
			}else{
				$mixed = 'false';
			}
		default:
			$output = '('.$type.')'.htmlspecialchars($mixed,ENT_COMPAT,'UTF-8',false).'';
		break;
	}

	if( $level === 0 ){
		$objects = array();
		$obj_buffer .= $output . "\n------------------------------------------------------------\n";
	}

	$objects = $objects_before;
	return $output;
}


function pre($arg){
	global $debug;

	if( !isset($debug) || !$debug ){
		//return;
	}
	ob_start();
	echo "\n\n<pre>";
	if( $arg === 0 ){
		echo '0';
	}elseif( !$arg ){
		var_dump($arg);
	}else{
		print_r($arg);
	}
	echo "</pre>\n";
	return ob_get_clean();
}


function msg($arg){
	echo Pre($arg);
}

function func_trace($len = 1){
	static $traces = array();
	$debug = debug_backtrace();
	array_shift($debug);
	for($i = 0; $i < $len; $i++ ){
		if( isset($debug[$i]['file']) ){
			$trace = $debug[$i]['file'].' @'.$debug[$i]['line'];
		}else{
			$trace = $debug[$i]['class'].'::'.$debug[$i]['function'];
		}
		if( !in_array($trace, $traces) ){
			msg($trace);
			//$traces[] = $trace;
		}
	}
}



ob_start();
$test_obj = new ParserTest();
$content = ob_get_clean();

?>
<!DOCTYPE html>
<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>Less.php Tests</title>
	<link rel="stylesheet" href="php-diff/styles.css" type="text/css" />
	<?php echo $test_obj->head ?>
	<link rel="stylesheet" type="text/css" href="assets/style.css" />
	<link rel="stylesheet" type="text/css" href="assets/jsdiff.css" />

	<script src="assets/jquery-1.10.2.min.js"></script>
	<script src="assets/diffview.js"></script>
	<script src="assets/difflib.js"></script>
	<script src="assets/script.js"></script>

	<?php

	echo '<script src="assets/x_moz-sourcemap/test.js"></script>';


	/*<script src="assets/x_moz-sourcemap/base64.js"></script>
	<script src="assets/x_moz-sourcemap/base64-vlq.js"></script>
	*/

		if( isset($_GET['file']) ){
			echo '<script src="assets/lessjs-config.js"></script>';
			//echo '<script src="assets/less-1.6.3.js"></script>';
			echo '<script src="assets/less-1.7.0.js"></script>';
		}
	?>
</head>
<body>

<?php

echo '<div id="heading">';
echo '<h1><a href="?">Less.php '.Less_Version::version.'</a></h1>';
echo $test_obj->Summary();
echo '</div>';


echo '<div id="contents">';
echo $test_obj->Links();
echo '<div id="results">';
echo $test_obj->Options();
echo $content;
echo '</div>';
echo '</div>';

?>
</body></html>
