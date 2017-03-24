<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Reader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;

class ClassesScanner implements ClassesScannerInterface
{
    /**
     * @var array
     */
    protected $excludePatterns = [];

    /**
     * @var array
     */

    protected $fileResults = [];

    /**
     * @var string
     */

    protected $generationDirectory;

    /**
     * @param array $excludePatterns
     */
    public function __construct(array $excludePatterns = [], $generationDirectory = false)
    {
        $this->excludePatterns = $excludePatterns;
        $this->generationDirectory = $generationDirectory;
    }

    public function getGenerationDirectory()
    {
        if ($this->generationDirectory === false) {
            $directoryList = ObjectManager::getInstance()->get(DirectoryList::class);
            /* @var $directoryList DirectoryList */
            $this->generationDirectory = $directoryList->getPath(DirectoryList::GENERATION);
        }
        return $this->generationDirectory;
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
     * Determines if the path provided is in the var/generation folder
     *
     * @param $path
     * @return bool
     */

    public function isGeneration($path)
    {
        $generation = $this->getGenerationDirectory();
        return strpos($path, $generation) === 0;
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
        $isGeneration = $this->isGeneration($realPath);

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
            $fileScanner = new FileClassScanner($fileItemPath);
            $classNames = $fileScanner->getClassNames();
            $this->includeClasses($classNames, $fileItemPath);
            $classes = array_merge($classes, $classNames);
        }
        return $classes;
    }

    protected function includeClasses(array $classNames, $fileItemPath)
    {
        foreach ($classNames as $className) {
            if (!class_exists($className)) {
                require_once $fileItemPath;
                return;
            }
        }
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
