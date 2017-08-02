<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Utility;

/**
 * Factory for \RegexIterator
 * @since 2.2.0
 */
class RegexIteratorFactory
{
    /**
     * Create instance of \RegexIterator
     *
     * @param string $directoryPath
     * @param string $regexp
     * @return \RegexIterator
     * @since 2.2.0
     */
    public function create($directoryPath, $regexp)
    {
        $directory = new \RecursiveDirectoryIterator($directoryPath);
        $recursiveIterator = new \RecursiveIteratorIterator($directory);
        return new \RegexIterator($recursiveIterator, $regexp, \RegexIterator::GET_MATCH);
    }
}
