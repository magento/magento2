<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
interface ModuleRegistryInterface
{
    /**
     * Get list of registered Magento module paths
     *
     * Returns an array where key is fully-qualified module name and value is absolute path to module
     *
     * @return array
     */
    public function getModulePaths();

    /**
     * Get path of a module if it is already registered
     *
     * @param string $moduleName
     * @return null|string
     */
    public function getModulePath($moduleName);
}
