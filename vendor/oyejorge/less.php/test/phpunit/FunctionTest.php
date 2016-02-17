<?php

class phpunit_FunctionTest extends phpunit_bootstrap{
	/**
	 * Test
	 */
	public function testFunction() {
		echo "\nBegin Tests";

		$less_file = $this->fixtures_dir.'/functions/less/f1.less';
		$expected_css = file_get_contents( $this->fixtures_dir.'/functions/css/f1.css' );

		$parser = new Less_Parser();

		$parser->registerFunction( 'myfunc-reverse', array( __CLASS__, 'reverse' ) );

		$parser->parseFile( $less_file );
		$generated_css = $parser->getCss();

		$this->assertEquals( $expected_css, $generated_css );
	}

	public static function reverse( $arg ) {
		if( is_a( $arg, 'Less_Tree_Quoted' ) ) {
			$arg->value = strrev( $arg->value );
			return $arg;
		}
	}
}