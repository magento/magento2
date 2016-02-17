<?php
/**
 * Unified diff generator for PHP DiffLib.
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

class Diff_Renderer_Text_Unified extends Diff_Renderer_Abstract
{
	/**
	 * Render and return a unified diff.
	 *
	 * @return string The unified diff.
	 */
	public function render()
	{
		$diff = '';
		$opCodes = $this->diff->getGroupedOpcodes();
		foreach($opCodes as $group) {
			$lastItem = count($group)-1;
			$i1 = $group[0][1];
			$i2 = $group[$lastItem][2];
			$j1 = $group[0][3];
			$j2 = $group[$lastItem][4];

			if($i1 == 0 && $i2 == 0) {
				$i1 = -1;
				$i2 = -1;
			}

			$diff .= '@@ -'.($i1 + 1).','.($i2 - $i1).' +'.($j1 + 1).','.($j2 - $j1)." @@\n";
			foreach($group as $code) {
				list($tag, $i1, $i2, $j1, $j2) = $code;
				if($tag == 'equal') {
					$diff .= ' '.implode("\n ", $this->diff->GetA($i1, $i2))."\n";
				}
				else {
					if($tag == 'replace' || $tag == 'delete') {
						$diff .= '-'.implode("\n-", $this->diff->GetA($i1, $i2))."\n";
					}

					if($tag == 'replace' || $tag == 'insert') {
						$diff .= '+'.implode("\n+", $this->diff->GetB($j1, $j2))."\n";
					}
				}
			}
		}
		return $diff;
	}
}