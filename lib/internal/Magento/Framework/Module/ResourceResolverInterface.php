<?php
/**
 * Resource resolver is used to retrieve a list of resources declared by module
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

interface ResourceResolverInterface
{
    /**
     * Retrieve the list of resources declared by module
     *
     * @param string $moduleName
     * @return array
     */
    public function getResourceList($moduleName);
}
