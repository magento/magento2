<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Dependency\Data;

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
     * Module dependencies
     *
     * @var \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency[]
     * @since 2.0.0
     */
    protected $dependencies;

    /**
     * Module construct
     *
     * @param array $name
     * @param \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency[] $dependencies
     * @since 2.0.0
     */
    public function __construct($name, array $dependencies = [])
    {
        $this->name = $name;
        $this->dependencies = $dependencies;
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
     * Get dependencies
     *
     * @return \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency[]
     * @since 2.0.0
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Get total dependencies count
     *
     * @return int
     * @since 2.0.0
     */
    public function getDependenciesCount()
    {
        return count($this->dependencies);
    }

    /**
     * Get hard dependencies count
     *
     * @return int
     * @since 2.0.0
     */
    public function getHardDependenciesCount()
    {
        $dependenciesCount = 0;
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->isHard()) {
                $dependenciesCount++;
            }
        }
        return $dependenciesCount;
    }

    /**
     * Get soft dependencies count
     *
     * @return int
     * @since 2.0.0
     */
    public function getSoftDependenciesCount()
    {
        $dependenciesCount = 0;
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->isSoft()) {
                $dependenciesCount++;
            }
        }
        return $dependenciesCount;
    }
}
