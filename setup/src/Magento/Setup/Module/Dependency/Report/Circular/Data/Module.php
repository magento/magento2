<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

/**
 * Module
 */
class Module
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name;

    /**
     * Circular dependencies chains
     *
     * @var \Magento\Setup\Module\Dependency\Report\Circular\Data\Chain[]
     */
    protected $chains;

    /**
     * Module construct
     *
     * @param array $name
     * @param \Magento\Setup\Module\Dependency\Report\Circular\Data\Chain[] $chains
     */
    public function __construct($name, array $chains = [])
    {
        $this->name = $name;
        $this->chains = $chains;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get circular dependencies chains
     *
     * @return \Magento\Setup\Module\Dependency\Report\Circular\Data\Chain[]
     */
    public function getChains()
    {
        return $this->chains;
    }

    /**
     * Get circular dependencies chains count
     *
     * @return int
     */
    public function getChainsCount()
    {
        return count($this->chains);
    }
}
