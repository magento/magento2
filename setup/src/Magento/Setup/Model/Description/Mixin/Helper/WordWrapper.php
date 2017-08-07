<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin\Helper;

/**
 * Apply specific format to words from source
 * @since 2.2.0
 */
class WordWrapper
{
    /**
     * Wrap $words with $format in $source
     *
     * @param string $source
     * @param array $words
     * @param string $format
     * @return string
     * @since 2.2.0
     */
    public function wrapWords($source, array $words, $format)
    {
        return empty($words)
            ? $source
            : preg_replace("/\\b(" . implode('|', $words) . ")\\b/", sprintf($format, '$1'), $source);
    }
}
