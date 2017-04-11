<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

/**
 *  Files collector
 */
class FilesCollector
{
    /**
     * Get files
     *
     * @param array $paths
     * @param bool $fileMask
     * @return array
     */
    public function getFiles(array $paths, $fileMask = false)
    {
        $files = [];
        foreach ($paths as $path) {
            foreach ($this->_getIterator($path, $fileMask) as $file) {
                $files[] = (string)$file;
            }
        }
        sort($files);
        return $files;
    }

    /**
     * Get files iterator
     *
     * @param string $path
     * @param bool $fileMask
     * @return \RecursiveIteratorIterator|\RegexIterator
     * @throws \InvalidArgumentException
     */
    protected function _getIterator($path, $fileMask = false)
    {
        try {
            $directoryIterator = new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS
            );
            $iterator = new \RecursiveIteratorIterator($directoryIterator);
        } catch (\UnexpectedValueException $valueException) {
            throw new \InvalidArgumentException(sprintf('Cannot read directory for parse phrase: "%s".', $path));
        }
        if ($fileMask) {
            $iterator = new \RegexIterator($iterator, $fileMask);
        }
        return $iterator;
    }
}
