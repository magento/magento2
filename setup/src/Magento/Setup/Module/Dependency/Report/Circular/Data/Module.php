<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

/**
 * Module
 * @since 2.0.0
 */
class Module
{
    /**
     * Module name
     *
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Circular dependencies chains
     *
     * @var \Magento\Setup\Module\Dependency\Report\Circular\Data\Chain[]
     * @since 2.0.0
     */
    protected $chains;

    /**
     * Module construct
     *
     * @param array $name
     * @param \Magento\Setup\Module\Dependency\Report\Circular\Data\Chain[] $chains
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get circular dependencies chains
     *
     * @return \Magento\Setup\Module\Dependency\Report\Circular\Data\Chain[]
     * @since 2.0.0
     */
    public function getChains()
    {
        return $this->chains;
    }

    /**
     * Get circular dependencies chains count
     *
     * @return int
     * @since 2.0.0
     */
    public function getChainsCount()
    {
        return count($this->chains);
    }
}
