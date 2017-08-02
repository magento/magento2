<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

/**
 * Chain
 * @since 2.0.0
 */
class Chain
{
    /**
     * Chain construct
     *
     * @param array $modules
     * @since 2.0.0
     */
    public function __construct($modules)
    {
        $this->modules = $modules;
    }

    /**
     * Get modules
     *
     * @return array
     * @since 2.0.0
     */
    public function getModules()
    {
        return $this->modules;
    }
}
