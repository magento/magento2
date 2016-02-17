<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Math\Source;

use RandomLib;
use SecurityLib\Strength;

/**
 * Author:
 * George Argyros <argyros.george@gmail.com>
 *
 * Copyright (c) 2012, George Argyros
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * * Neither the name of the <organization> nor the
 * names of its contributors may be used to endorse or promote products
 * derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL GEORGE ARGYROS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *
 * The function is providing, at least at the systems tested :),
 * $len bytes of entropy under any PHP installation or operating system.
 * The execution time should be at most 10-20 ms in any system.
 *
 * Modified by Padraic Brady as part of Zend Framework to use 25% of the
 * original version's iterations.
 */
class HashTiming implements RandomLib\Source
{
    /**
     * Return an instance of Strength indicating the strength of the source
     *
     * @return Strength An instance of one of the strength classes
     */
    public static function getStrength()
    {
        return new Strength(Strength::VERYLOW);
    }

    /**
     * Generate a random string of the specified size
     *
     * @param int $size The size of the requested random string
     *
     * @return string A string of the requested size
     */
    public function generate($size)
    {
        $result         = '';
        $entropy        = '';
        $msec_per_round = 400;
        $bits_per_round = 2;
        $total          = $size;
        $hash_length    = 20;

        while (strlen($result) < $size) {
            $bytes  = ($total > $hash_length)? $hash_length : $total;
            $total -= $bytes;
            for ($i=1; $i < 3; $i++) {
                $t1   = microtime(true);
                $seed = mt_rand();
                for ($j=1; $j < 50; $j++) {
                    $seed = sha1($seed);
                }
                $t2 = microtime(true);
                $entropy .= $t1 . $t2;
            }
            $div = (int) (($t2 - $t1) * 1000000);
            if ($div <= 0) {
                $div = 400;
            }
            $rounds = (int) ($msec_per_round * 50 / $div);
            $iter = $bytes * (int) (ceil(8 / $bits_per_round));
            for ($i = 0; $i < $iter; $i ++) {
                $t1 = microtime();
                $seed = sha1(mt_rand());
                for ($j = 0; $j < $rounds; $j++) {
                    $seed = sha1($seed);
                }
                $t2 = microtime();
                $entropy .= $t1 . $t2;
            }
            $result .= sha1($entropy, true);
        }
        return substr($result, 0, $size);
    }
}
