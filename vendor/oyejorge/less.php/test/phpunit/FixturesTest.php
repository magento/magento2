<?php


class phpunit_FixturesTest extends phpunit_bootstrap{


	/**
	 * Test the contents of the files in /test/Fixtures/lessjs/expected
	 *
	 */
	function testLessJs(){

		echo "\nBegin Tests";

		$css_dir = $this->fixtures_dir.'/lessjs/expected';
		$files = scandir($css_dir);

		foreach($files as $file){
			if( $file == '.' || $file == '..' ){
				continue;
			}

			$expected_file = $css_dir.'/'.$file;

			if( is_dir($expected_file) ){
				continue;
			}

			$this->CompareFile( $expected_file );
		}

		echo "\n";
	}



	/**
	 * Change a css file name to a less file name
	 *
	 * eg: /Fixtures/lessjs/css/filename.css -> /Fixtures/lessjs/less/filename.less
	 *
	 */
	function TranslateFile( $file_css, $dir = 'less', $type = 'less' ){

		$filename = basename($file_css);
		$filename = substr($filename,0,-4);

		return dirname( dirname($file_css) ).'/'.$dir.'/'.$filename.'.'.$type;
	}


	/**
	 * Compare the parser results with the expected css
	 *
	 */
	function CompareFile( $expected_file ){

		$less_file = $this->TranslateFile( $expected_file );
		$expected_css = trim(file_get_contents($expected_file));


		// Check with standard parser
		echo "\n  ".basename($expected_file);
		echo "\n    - Standard Compiler";

		$parser = new Less_Parser();
		$parser->parseFile($less_file);
		$css = $parser->getCss();
		$css = trim($css);
		$this->assertEquals( $expected_css, $css );


		// Check with cache
		if( $this->cache_dir ){

			$options = array('cache_dir'=>$this->cache_dir);
			$files = array( $less_file => '' );

			echo "\n    - Regenerating Cache";
			$css_file_name = Less_Cache::Regen( $files, $options );
			$css = file_get_contents($this->cache_dir.'/'.$css_file_name);
			$css = trim($css);
			$this->assertEquals( $expected_css, $css );



			// Check using the cached data
			echo "\n    - Using Cache";
			$css_file_name = Less_Cache::Get( $files, $options );
			$css = file_get_contents($this->cache_dir.'/'.$css_file_name);
			$css = trim($css);
			$this->assertEquals( $expected_css, $css );

		}


	}


}