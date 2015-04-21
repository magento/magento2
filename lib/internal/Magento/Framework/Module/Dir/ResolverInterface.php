<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Dir;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
interface ResolverInterface
{
    /**
     * Get list of Magento module paths
     *
     * Returns an array where key is fully-qualified module name and value is absolute path to module
     *
     * @return array
     */
    public function getModulePaths();

    /**
     * @param string $moduleName
     * @return null|string
     */
    public function getModulePath($moduleName);
}
