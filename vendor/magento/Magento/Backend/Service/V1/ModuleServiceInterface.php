<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Service\V1;

interface ModuleServiceInterface
{
    /**
     * Returns an array of enabled modules
     *
     * @return string[]
     */
    public function getModules();
}
