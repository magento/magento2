<?php
/**
 * Context diff generator for PHP DiffLib.
 *
 * PHP version 5
 *
 * Copyright (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Chris Boulton nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software 
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DiffLib
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.1
 * @link http://github.com/chrisboulton/php-diff
 */

require_once dirname(__FILE__).'/../Abstract.php';

class Diff_Renderer_Text_Context extends Diff_Renderer_Abstract
{
	/**
	 * @var array Array of the different opcode tags and how they map to the context diff equivalent.
	 */
	private $tagMap = array(
		'insert' => '+',
		'delete' => '-',
		'replace' => '!',
		'equal' => ' '
	);

	/**
	 * Render and return a context formatted (old school!) diff file.
	 *
	 * @return string The generated context diff.
	 */
	public function render()
	{
		$diff = '';
		$opCodes = $this->diff->getGroupedOpcodes();
		foreach($opCodes as $group) {
			$diff .= "***************\n";
			$lastItem = count($group)-1;
			$i1 = $group[0][1];
			$i2 = $group[$lastItem][2];
			$j1 = $group[0][3];
			$j2 = $group[$lastItem][4];

			if($i2 - $i1 >= 2) {
				$diff .= '*** '.($group[0][1] + 1).','.$i2." ****\n";
			}
			else {
				$diff .= '*** '.$i2." ****\n";
			}

			if($j2 - $j1 >= 2) {
				$separator = '--- '.($j1 + 1).','.$j2." ----\n";
			}
			else {
				$separator = '--- '.$j2." ----\n";
			}

			$hasVisible = false;
			foreach($group as $code) {
				if($code[0] == 'replace' || $code[0] == 'delete') {
					$hasVisible = true;
					break;
				}
			}

			if($hasVisible) {
				foreach($group as $code) {
					list($tag, $i1, $i2, $j1, $j2) = $code;
					if($tag == 'insert') {
						continue;
					}
					$diff .= $this->tagMap[$tag].' '.implode("\n".$this->tagMap[$tag].' ', $this->diff->GetA($i1, $i2))."\n";
				}
			}

			$hasVisible = false;
			foreach($group as $code) {
				if($code[0] == 'replace' || $code[0] == 'insert') {
					$hasVisible = true;
					break;
				}
			}

			$diff .= $separator;

			if($hasVisible) {
				foreach($group as $code) {
					list($tag, $i1, $i2, $j1, $j2) = $code;
					if($tag == 'delete') {
						continue;
					}
					$diff .= $this->tagMap[$tag].' '.implode("\n".$this->tagMap[$tag].' ', $this->diff->GetB($j1, $j2))."\n";
				}
			}
		}
		return $diff;
	}
}