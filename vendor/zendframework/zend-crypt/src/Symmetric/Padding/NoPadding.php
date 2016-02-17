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
 * No Padding
 */
class NoPadding implements PaddingInterface
{
    /**
     * Pad a string, do nothing and return the string
     *
     * @param  string $string
     * @param  int    $blockSize
     * @return string
     */
    public function pad($string, $blockSize = 32)
    {
        return $string;
    }

    /**
     * Unpad a string, do nothing and return the string
     *
     * @param  string $string
     * @return string
     */
    public function strip($string)
    {
        return $string;
    }
}
