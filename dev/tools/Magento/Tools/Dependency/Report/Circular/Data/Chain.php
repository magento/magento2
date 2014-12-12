<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Circular\Data;

/**
 * Chain
 */
class Chain
{
    /**
     * Chain construct
     *
     * @param array $modules
     */
    public function __construct($modules)
    {
        $this->modules = $modules;
    }

    /**
     * Get modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }
}
