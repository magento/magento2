<?php
/**
 * Resource resolver is used to retrieve a list of resources declared by module
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
