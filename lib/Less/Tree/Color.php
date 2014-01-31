<?php


class Less_Tree_Color extends Less_Tree{
	var $rgb;
	var $alpha;
	public $type = 'Color';

	public function __construct($rgb, $a = 1){
		$this->rgb = array();
		if( is_array($rgb) ){
			$this->rgb = $rgb;
		}else if( strlen($rgb) == 6 ){
			foreach(str_split($rgb, 2) as $c){
				$this->rgb[] = hexdec($c);
			}
		}else{
			foreach(str_split($rgb, 1) as $c){
				$this->rgb[] = hexdec($c.$c);
			}
		}
		$this->alpha = is_numeric($a) ? $a : 1;
	}

    public function compile($env = null){
        return $this;
    }

	public function luma(){
		return (0.2126 * $this->rgb[0] / 255) + (0.7152 * $this->rgb[1] / 255) + (0.0722 * $this->rgb[2] / 255);
	}

	public function genCSS( $env, &$strs ){
		self::OutputAdd( $strs, $this->toCSS($env) );
	}

    public function toCSS($env = null, $doNotCompress = false ){
		$compress = Less_Environment::$compress && !$doNotCompress;


	    //
	    // If we have some transparency, the only way to represent it
	    // is via `rgba`. Otherwise, we use the hex representation,
	    // which has better compatibility with older browsers.
	    // Values are capped between `0` and `255`, rounded and zero-padded.
	    //
    	if( $this->alpha < 1.0 ){
            if( $this->alpha === 0 && isset($this->isTransparentKeyword) && $this->isTransparentKeyword ){
                return 'transparent';
            }


			$values = array_map('round', $this->rgb);
			$values[] = $this->alpha;

			$glue = ($compress ? ',' : ', ');
			return "rgba(" . implode($glue, $values) . ")";
		}else{

			$color = $this->toRGB();

			if( $compress ){

				// Convert color to short format
				if( $color[1] === $color[2] && $color[3] === $color[4] && $color[5] === $color[6]) {
					$color = '#'.$color[1] . $color[3] . $color[5];
				}
			}

			return $color;
		}
    }

    //
    // Operations have to be done per-channel, if not,
    // channels will spill onto each other. Once we have
    // our result, in the form of an integer triplet,
    // we create a new Color node to hold the result.
    //
    public function operate($env, $op, $other) {
        $result = array();

        if (! ($other instanceof Less_Tree_Color)) {
            $other = $other->toColor();
        }

        for ($c = 0; $c < 3; $c++) {
            $result[$c] = Less_Functions::operate($env, $op, $this->rgb[$c], $other->rgb[$c]);
        }
        return new Less_Tree_Color($result, $this->alpha + $other->alpha);
    }

    public function toRGB(){
		$color = '';
		foreach($this->rgb as $i){
			$i = Less_Parser::round($i);
			$i = ($i > 255 ? 255 : ($i < 0 ? 0 : $i));
			$i = dechex($i);
			$color .= str_pad($i, 2, '0', STR_PAD_LEFT);
		}
		return '#'.$color;
    }

	public function toHSL(){
		$r = $this->rgb[0] / 255;
		$g = $this->rgb[1] / 255;
		$b = $this->rgb[2] / 255;
		$a = $this->alpha;

		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$l = ($max + $min) / 2;
		$d = $max - $min;

		if( $max === $min ){
			$h = $s = 0;
		} else {
			$s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

			switch ($max) {
				case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
				case $g: $h = ($b - $r) / $d + 2;                 break;
				case $b: $h = ($r - $g) / $d + 4;                 break;
			}
			$h /= 6;
		}
		return array('h' => $h * 360, 's' => $s, 'l' => $l, 'a' => $a );
	}

	//Adapted from http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript
	function toHSV() {
		$r = $this->rgb[0] / 255;
		$g = $this->rgb[1] / 255;
		$b = $this->rgb[2] / 255;
		$a = $this->alpha;

		$max = max($r, $g, $b);
		$min = min($r, $g, $b);

		$v = $max;

		$d = $max - $min;
		if ($max === 0) {
			$s = 0;
		} else {
			$s = $d / $max;
		}

		if ($max === $min) {
			$h = 0;
		} else {
			switch($max){
				case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
				case $g: $h = ($b - $r) / $d + 2; break;
				case $b: $h = ($r - $g) / $d + 4; break;
			}
			$h /= 6;
		}
		return array('h'=> $h * 360, 's'=> $s, 'v'=> $v, 'a' => $a );
	}

	public function toARGB(){
		$argb = array_merge( (array) Less_Parser::round($this->alpha * 255), $this->rgb);

		$temp = '';
		foreach($argb as $i){
			$i = Less_Parser::round($i);
			$i = dechex($i > 255 ? 255 : ($i < 0 ? 0 : $i));
			$temp .= str_pad($i, 2, '0', STR_PAD_LEFT);
		}
		return '#' . $temp;
	}

    public function compare($x){

		if( !property_exists( $x, 'rgb' ) ){
			return -1;
		}


        return ($x->rgb[0] === $this->rgb[0] &&
            $x->rgb[1] === $this->rgb[1] &&
            $x->rgb[2] === $this->rgb[2] &&
            $x->alpha === $this->alpha) ? 0 : -1;
    }


	public static function fromKeyword( $keyword ){

		if( Less_Colors::hasOwnProperty($keyword) ){
			// detect named color
			return new Less_Tree_Color(substr(Less_Colors::color($keyword), 1));
		}

		if( $keyword === 'transparent' ){
			$transparent = new Less_Tree_Color( array(0, 0, 0), 0);
			$transparent->isTransparentKeyword = true;
			return $transparent;
		}
	}

}
