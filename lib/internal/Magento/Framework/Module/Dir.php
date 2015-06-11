<?php
/**
 * Encapsulates directories structure of a Magento module
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Stdlib\String as StringHelper;
use Magento\Framework\Module\ModuleRegistryInterface;

class Dir
{
    /**#@+
     * Directories within modules
     */
    const MODULE_ETC_DIR = 'etc';
    const MODULE_I18N_DIR = 'i18n';
    const MODULE_VIEW_DIR = 'view';
    const MODULE_CONTROLLER_DIR = 'Controller';
    /**#@-*/

    /**
     * Modules root directory
     *
     * @var ReadInterface
     */
    protected $_modulesDirectory;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $_string;

    /**
     * Module registry
     *
     * @var ModuleRegistryInterface
     */
    private $moduleRegistry;

    /**
     * @param Filesystem $filesystem
     * @param StringHelper $string
     * @param ModuleRegistryInterface $moduleRegistry
     */
    public function __construct(
        Filesystem $filesystem,
        StringHelper $string,
        ModuleRegistryInterface $moduleRegistry
    ) {
        $this->_modulesDirectory = $filesystem->getDirectoryRead(DirectoryList::MODULES);
        $this->_string = $string;
        $this->moduleRegistry = $moduleRegistry;
    }

    /**
     * Retrieve full path to a directory of certain type within a module
     *
     * @param string $moduleName Fully-qualified module name
     * @param string $type Type of module's directory to retrieve
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDir($moduleName, $type = '')
    {
        if (null === $path = $this->moduleRegistry->getModulePath($moduleName)) {
            $relativePath = $this->_string->upperCaseWords($moduleName, '_', '/');
            $path = $this->_modulesDirectory->getAbsolutePath($relativePath);
        }

        if ($type) {
            if (!in_array($type, [
                self::MODULE_ETC_DIR,
                self::MODULE_I18N_DIR,
                self::MODULE_VIEW_DIR,
                self::MODULE_CONTROLLER_DIR
            ])) {
                throw new \InvalidArgumentException("Directory type '{$type}' is not recognized.");
            }
            $path .= '/' . $type;
        }

        return $path;
    }
}
