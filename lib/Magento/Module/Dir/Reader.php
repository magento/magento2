<?php
/**
 * Module configuration file reader
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Module\Dir;

use Magento\App\Filesystem;
use Magento\Config\FileIterator;
use Magento\Config\FileIteratorFactory;
use Magento\Filesystem\Directory\Read;
use Magento\Module\ModuleListInterface;
use Magento\Module\Dir;

class Reader
{
    /**
     * Module directories that were set explicitly
     *
     * @var array
     */
    protected $customModuleDirs = array();

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
     * @var Read
     */
    protected $modulesDirectory;

    /**
     * @var FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @param Dir $moduleDirs
     * @param ModuleListInterface $moduleList
     * @param Filesystem $filesystem
     * @param FileIteratorFactory $fileIteratorFactory
     */
    public function __construct(
        Dir $moduleDirs,
        ModuleListInterface $moduleList,
        Filesystem $filesystem,
        FileIteratorFactory $fileIteratorFactory
    ) {
        $this->moduleDirs = $moduleDirs;
        $this->modulesList = $moduleList;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->modulesDirectory = $filesystem->getDirectoryRead(Filesystem::MODULES_DIR);
    }

    /**
     * Go through all modules and find configuration files of active modules
     *
     * @param string $filename
     * @return FileIterator
     */
    public function getConfigurationFiles($filename)
    {
        $result = array();
        foreach (array_keys($this->modulesList->getModules()) as $moduleName) {
            $file = $this->getModuleDir('etc', $moduleName) . '/' . $filename;
            $path = $this->modulesDirectory->getRelativePath($file);
            if ($this->modulesDirectory->isExist($path)) {
                $result[] = $path;
            }
        }
        return $this->fileIteratorFactory->create($this->modulesDirectory, $result);
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
    }
}
