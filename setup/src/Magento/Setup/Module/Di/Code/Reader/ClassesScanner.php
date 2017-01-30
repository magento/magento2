<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

        $classes = [];
        foreach ($recursiveIterator as $fileItem) {
            /** @var $fileItem \SplFileInfo */
            if ($fileItem->isDir() || $fileItem->getExtension() !== 'php') {
                continue;
            }
            foreach ($this->excludePatterns as $excludePatterns) {
                if ($this->isExclude($fileItem, $excludePatterns)) {
                    continue 2;
                }
            }
            $fileScanner = new FileScanner($fileItem->getRealPath());
            $classNames = $fileScanner->getClassNames();
            foreach ($classNames as $className) {
                if (!class_exists($className)) {
                    require_once $fileItem->getRealPath();
                }
                $classes[] = $className;
            }
        }
        return $classes;
    }

    /**
     * Find out if file should be excluded
     *
     * @param \SplFileInfo $fileItem
     * @param string $patterns
     * @return bool
     */
    private function isExclude(\SplFileInfo $fileItem, $patterns)
    {
        if (!is_array($patterns)) {
            $patterns = (array)$patterns;
        }
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, str_replace('\\', '/', $fileItem->getRealPath()))) {
                return true;
            }
        }
        return false;
    }
}
