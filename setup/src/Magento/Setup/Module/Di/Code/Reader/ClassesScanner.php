<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader;

use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Module\Di\Code\Reader\FileScanner;

class ClassesScanner implements ClassesScannerInterface
{
    /**
     * @var array
     */
    protected $excludePatterns = [];

    /**
     * @param array $excludePatterns
     */
    public function __construct(array $excludePatterns = [])
    {
        $this->excludePatterns = $excludePatterns;
    }

    /**
     * Adds exclude patterns
     *
     * @param array $excludePatterns
     * @return void
     */
    public function addExcludePatterns(array $excludePatterns)
    {
        $this->excludePatterns = array_merge($this->excludePatterns, $excludePatterns);
    }

    /**
     * Retrieves list of classes for given path
     *
     * @param string $path
     * @return array
     * @throws FileSystemException
     */
    public function getList($path)
    {
        $realPath = realpath($path);
        if (!(bool)$realPath) {
            throw new FileSystemException(new \Magento\Framework\Phrase('Invalid path: %1', [$path]));
        }
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath, \FilesystemIterator::FOLLOW_SYMLINKS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $classes = $this->extract($recursiveIterator);
        return $classes;
    }

    /**
     * Extracts all the classes from the recursive iterator
     *
     * @param \RecursiveIteratorIterator $recursiveIterator
     * @return array
     */

    private function extract(\RecursiveIteratorIterator $recursiveIterator)
    {
        $classes = [];
        foreach ($recursiveIterator as $fileItem) {
            /** @var $fileItem \SplFileInfo */
            if ($fileItem->isDir() || $fileItem->getExtension() !== 'php') {
                continue;
            }
            $fileItemPath = $fileItem->getRealPath();
            foreach ($this->excludePatterns as $excludePatterns) {
                if ($this->isExclude($fileItemPath, $excludePatterns)) {
                    continue 2;
                }
            }
            $fileScanner = new FileScanner($fileItemPath);
            $classNames = $fileScanner->getClassNames();
            foreach ($classNames as $className) {
                if (empty($className)) {
                    continue;
                }
                if (!class_exists($className)) {
                    require_once $fileItemPath;
                }
                $classes[] = $className;
            }
        }
        return $classes;
    }

    /**
     * Find out if file should be excluded
     *
     * @param string $fileItemPath
     * @param string $patterns
     * @return bool
     */
    private function isExclude($fileItemPath, $patterns)
    {
        if (!is_array($patterns)) {
            $patterns = (array)$patterns;
        }
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, str_replace('\\', '/', $fileItemPath))) {
                return true;
            }
        }
        return false;
    }
}
