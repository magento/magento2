<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric\Padding;

/**
 * PKCS#7 padding
 */
class Pkcs7 implements PaddingInterface
{
    /**
     * Pad the string to the specified size
     *
     * @param string $string    The string to pad
     * @param int    $blockSize The size to pad to
     *
     * @return string The padded string
     */
    public function pad($string, $blockSize = 32)
    {
        $pad = $blockSize - (strlen($string) % $blockSize);
        return $string . str_repeat(chr($pad), $pad);
    }

    /**
     * Strip the padding from the supplied string
     *
     * @param string $string The string to trim
     *
     * @return string The unpadded string
     */
    public function strip($string)
    {
        $end  = substr($string, -1);
        $last = ord($end);
        $len  = strlen($string) - $last;
        if (substr($string, $len) == str_repeat($end, $last)) {
            return substr($string, 0, $len);
        }
        return false;
    }
}
