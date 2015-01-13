<?php
/**
 * Resolves file/directory paths to modules they belong to
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Dir;

use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Module\ModuleListInterface;

class ReverseResolver
{
    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var ModuleDir
     */
    protected $_moduleDirs;

    /**
     * @param ModuleListInterface $moduleList
     * @param ModuleDir $moduleDirs
     */
    public function __construct(ModuleListInterface $moduleList, ModuleDir $moduleDirs)
    {
        $this->_moduleList = $moduleList;
        $this->_moduleDirs = $moduleDirs;
    }

    /**
     * Retrieve fully-qualified module name, path belongs to
     *
     * @param string $path Full path to file or directory
     * @return string|null
     */
    public function getModuleName($path)
    {
        $path = str_replace('\\', '/', $path);
        foreach ($this->_moduleList->getNames() as $moduleName) {
            $moduleDir = $this->_moduleDirs->getDir($moduleName);
            $moduleDir = str_replace('\\', '/', $moduleDir);
            if ($path == $moduleDir || strpos($path, $moduleDir . '/') === 0) {
                return $moduleName;
            }
        }
        return null;
    }
}
