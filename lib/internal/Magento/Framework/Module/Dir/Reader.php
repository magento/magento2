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
 * @since 2.0.0
 */
class Reader
{
    /**
     * Module directories that were set explicitly
     *
     * @var array
     * @since 2.0.0
     */
    protected $customModuleDirs = [];

    /**
     * Directory registry
     *
     * @var Dir
     * @since 2.0.0
     */
    protected $moduleDirs;

    /**
     * Modules configuration provider
     *
     * @var ModuleListInterface
     * @since 2.0.0
     */
    protected $modulesList;

    /**
     * @var FileIteratorFactory
     * @since 2.0.0
     */
    protected $fileIteratorFactory;

    /**
     * @var Filesystem\Directory\ReadFactory
     * @since 2.0.0
     */
    protected $readFactory;

    /**
     * Found configuration files grouped by configuration types (filename).
     *
     * @var array
     * @since 2.2.0
     */
    private $fileIterators = [];

    /**
     * @param Dir $moduleDirs
     * @param ModuleListInterface $moduleList
     * @param FileIteratorFactory $fileIteratorFactory
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getConfigurationFiles($filename)
    {
        return $this->getFilesIterator($filename, Dir::MODULE_ETC_DIR);
    }

    /**
     * Go through all modules and find composer.json files of active modules.
     *
     * @return FileIterator
     * @since 2.0.0
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
     * @since 2.2.0
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
     * @since 2.0.0
     */
    private function getFiles($filename, $subDir = '')
    {
        $result = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            $moduleSubDir = $this->getModuleDir($subDir, $moduleName);
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        $this->customModuleDirs[$moduleName][$type] = $path;
        $this->fileIterators = [];
    }
}
