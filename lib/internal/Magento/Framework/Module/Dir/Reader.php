<?php
/**
 * Module configuration file reader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Dir;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\ModuleListInterface;

/**
 * @api
 * @since 100.0.2
 */
class Reader
{
    /**
     * Module directories that were set explicitly
     *
     * @var array
     */
    protected $customModuleDirs = [];

    /**
     * Directory registry
     *
     * @var Dir
     */
    protected $moduleDirs;

    /**
     * Modules configuration provider
     *
     * @var ModuleListInterface
     */
    protected $modulesList;

    /**
     * @var FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * Found configuration files grouped by configuration types (filename).
     *
     * @var array
     */
    private $fileIterators = [];

    /**
     * @param Dir $moduleDirs
     * @param ModuleListInterface $moduleList
     * @param FileIteratorFactory $fileIteratorFactory
     * @param Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(
        Dir $moduleDirs,
        ModuleListInterface $moduleList,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->moduleDirs = $moduleDirs;
        $this->modulesList = $moduleList;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->readFactory = $readFactory;
    }

    /**
     * Go through all modules and find configuration files of active modules.
     *
     * @param string $filename
     * @return FileIterator
     */
    public function getConfigurationFiles($filename)
    {
        return $this->getFilesIterator($filename, Dir::MODULE_ETC_DIR);
    }

    /**
     * Go through all modules and find composer.json files of active modules.
     *
     * @return FileIterator
     */
    public function getComposerJsonFiles()
    {
        return $this->getFilesIterator('composer.json');
    }

    /**
     * Retrieve iterator for files with $filename from components located in component $subDir.
     *
     * @param string $filename
     * @param string $subDir
     *
     * @return FileIterator
     */
    private function getFilesIterator($filename, $subDir = '')
    {
        if (!isset($this->fileIterators[$subDir][$filename])) {
            $this->fileIterators[$subDir][$filename] = $this->fileIteratorFactory->create(
                $this->getFiles($filename, $subDir)
            );
        }
        return $this->fileIterators[$subDir][$filename];
    }

    /**
     * Go through all modules and find corresponding files of active modules
     *
     * @param string $filename
     * @param string $subDir
     * @return array
     */
    private function getFiles($filename, $subDir = '')
    {
        $result = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            try {
                $moduleSubDir = $this->getModuleDir($subDir, $moduleName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            $file = $moduleSubDir . '/' . $filename;
            $directoryRead = $this->readFactory->create($moduleSubDir);
            $path = $directoryRead->getRelativePath($file);
            if ($directoryRead->isExist($path)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Retrieve list of module action files
     *
     * @return array
     */
    public function getActionFiles()
    {
        $actions = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            $actionDir = $this->getModuleDir(Dir::MODULE_CONTROLLER_DIR, $moduleName);
            if (!file_exists($actionDir)) {
                continue;
            }
            $dirIterator = new \RecursiveDirectoryIterator($actionDir, \RecursiveDirectoryIterator::SKIP_DOTS);
            $recursiveIterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
            $namespace = str_replace('_', '\\', $moduleName);
            /** @var \SplFileInfo $actionFile */
            foreach ($recursiveIterator as $actionFile) {
                $actionName = str_replace('/', '\\', str_replace($actionDir, '', $actionFile->getPathname()));
                $action = $namespace . "\\" . Dir::MODULE_CONTROLLER_DIR . substr($actionName, 0, -4);
                $actions[strtolower($action)] = $action;
            }
        }
        return $actions;
    }

    /**
     * Get module directory by directory type
     *
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    public function getModuleDir($type, $moduleName)
    {
        if (isset($this->customModuleDirs[$moduleName][$type])) {
            return $this->customModuleDirs[$moduleName][$type];
        }
        return $this->moduleDirs->getDir($moduleName, $type);
    }

    /**
     * Set path to the corresponding module directory
     *
     * @param string $moduleName
     * @param string $type directory type (etc, controllers, locale etc)
     * @param string $path
     * @return void
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        $this->customModuleDirs[$moduleName][$type] = $path;
        $this->fileIterators = [];
    }
}
