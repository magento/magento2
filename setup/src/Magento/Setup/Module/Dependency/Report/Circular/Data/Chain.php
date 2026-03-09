<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

/**
 * Chain
 */
class Chain
{
    /**
     * @var array
     */
    private $modules;

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
