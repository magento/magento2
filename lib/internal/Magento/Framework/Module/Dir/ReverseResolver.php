<?php
/**
 * Resolves file/directory paths to modules they belong to
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Dir;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class \Magento\Framework\Module\Dir\ReverseResolver
 *
 */
class ReverseResolver
{
    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var Dir
     */
    protected $_moduleDirs;

    /**
     * @param ModuleListInterface $moduleList
     * @param Dir $moduleDirs
     */
    public function __construct(ModuleListInterface $moduleList, Dir $moduleDirs)
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
