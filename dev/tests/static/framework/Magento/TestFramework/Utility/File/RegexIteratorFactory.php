<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\Utility\File;

/**
 * Factory for \RegexIterator
 */
class RegexIteratorFactory
{
    /**
     * Create instance of \RegexIterator
     *
     * @param string $directoryPath
     * @param string $regexp
     * @return \RegexIterator
     */
    public function create($directoryPath, $regexp)
    {
        $directory = new \RecursiveDirectoryIterator($directoryPath);
        $recursiveIterator = new \RecursiveIteratorIterator($directory);
        return new \RegexIterator($recursiveIterator, $regexp, \RegexIterator::GET_MATCH);
    }
}
