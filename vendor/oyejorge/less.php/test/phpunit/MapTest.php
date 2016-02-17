<?php

class phpunit_MapTest extends phpunit_bootstrap{


	/**
	 * Test
	 */
	public function testMap(){
		echo "\nBegin Tests";

		$less_file			= $this->fixtures_dir.'/bootstrap3-sourcemap/less/bootstrap.less';
		$map_file			= $this->fixtures_dir.'/bootstrap3-sourcemap/expected/bootstrap.map';
		$map_destination	= $this->cache_dir.'/bootstrap.map';



		$options['sourceMap']			= true;
		$options['sourceMapWriteTo']	= $map_destination;
		$options['sourceMapURL']		= '/';
		$options['sourceMapBasepath']	= dirname(dirname($less_file));


		$parser = new Less_Parser($options);
		$parser->parseFile($less_file);
		$css = $parser->getCss();

		$expected_map	= file_get_contents($map_file);
		$generated_map	= file_get_contents($map_destination);
		$this->assertEquals( $expected_map, $generated_map );

	}

}