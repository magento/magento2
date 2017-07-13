<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem;

use Zend\Stdlib\Glob as ZendGlob;
use Zend\Stdlib\Exception\RuntimeException as ZendRuntimeException;

/**
 * Wrapper for Zend\Stdlib\Glob
 */
class Glob extends ZendGlob
{
    /**
     * Find pathnames matching a pattern.
     *
     * @param  string  $pattern
     * @param  int $flags
     * @param  bool $forceFallback
     * @return array
     */
    public static function glob($pattern, $flags = 0, $forceFallback = false)
    {
        try {
            $result = ZendGlob::glob($pattern, $flags, $forceFallback);
        } catch (ZendRuntimeException $e) {
            $result = [];
        }
        return $result;
    }
}
