<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Compress;

/**
 * Compression interface
 */
interface CompressionAlgorithmInterface
{
    /**
     * Compresses $value with the defined settings
     *
     * @param  string $value Data to compress
     * @return string The compressed data
     */
    public function compress($value);

    /**
     * Decompresses $value with the defined settings
     *
     * @param  string $value Data to decompress
     * @return string The decompressed data
     */
    public function decompress($value);

    /**
     * Return the adapter name
     *
     * @return string
     */
    public function toString();
}
