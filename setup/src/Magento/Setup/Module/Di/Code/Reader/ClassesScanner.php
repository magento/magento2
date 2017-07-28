<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class \Magento\Setup\Module\Di\Code\Reader\ClassesScanner
 *
 * @since 2.0.0
 */
class ClassesScanner implements ClassesScannerInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $excludePatterns = [];

    /**
     * @var array
     * @since 2.2.0
     */
    private $fileResults = [];

    /**
     * @var string
     * @since 2.2.0
     */
    private $generationDirectory;

    /**
     * @param array $excludePatterns
     * @param string $generationDirectory
     * @since 2.0.0
     */
    public function __construct(array $excludePatterns = [], DirectoryList $directoryList = null)
    {
        $this->excludePatterns = $excludePatterns;
        if ($directoryList === null) {
            $directoryList = ObjectManager::getInstance()->get(DirectoryList::class);
        }
        $this->generationDirectory = $directoryList->getPath(DirectoryList::GENERATION);
    }

    /**
     * Adds exclude patterns
     *
     * @param array $excludePatterns
     * @return void
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function getList($path)
    {

        $realPath = realpath($path);
        $isGeneration = strpos($realPath, $this->generationDirectory) === 0;

        // Generation folders should not have their results cached since they may actually change during compile
        if (!$isGeneration && isset($this->fileResults[$realPath])) {
            return $this->fileResults[$realPath];
        }
        if (!(bool)$realPath) {
            throw new FileSystemException(new \Magento\Framework\Phrase('Invalid path: %1', [$path]));
        }
        $recursiveIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath, \FilesystemIterator::FOLLOW_SYMLINKS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $classes = $this->extract($recursiveIterator);
        if (!$isGeneration) {
            $this->fileResults[$realPath] = $classes;
        }
        return $classes;
    }

    /**
     * Extracts all the classes from the recursive iterator
     *
     * @param \RecursiveIteratorIterator $recursiveIterator
     * @return array
     * @since 2.2.0
     */
    private function extract(\RecursiveIteratorIterator $recursiveIterator)
    {
        $classes = [];
        foreach ($recursiveIterator as $fileItem) {
            /** @var $fileItem \SplFileInfo */
            if ($fileItem->isDir() || $fileItem->getExtension() !== 'php' || $fileItem->getBasename()[0] == '.') {
                continue;
            }
            $fileItemPath = $fileItem->getRealPath();
            foreach ($this->excludePatterns as $excludePatterns) {
                if ($this->isExclude($fileItemPath, $excludePatterns)) {
                    continue 2;
                }
            }
            $fileScanner = new FileClassScanner($fileItemPath);
            $classNames = $fileScanner->getClassNames();
            $this->includeClasses($classNames, $fileItemPath);
            $classes = array_merge($classes, $classNames);
        }
        return $classes;
    }

    /**
     * @param array $classNames
     * @param string $fileItemPath
     * @return bool Whether the clas is included or not
     * @since 2.2.0
     */
    private function includeClasses(array $classNames, $fileItemPath)
    {
        foreach ($classNames as $className) {
            if (!class_exists($className)) {
                require_once $fileItemPath;
                return true;
            }
        }
        return false;
    }

    /**
     * Find out if file should be excluded
     *
     * @param string $fileItemPath
     * @param string $patterns
     * @return bool
     * @since 2.0.0
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
