<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Dependency\Report\Data;

/**
 * Config
 */
interface ConfigInterface
{
    /**
     * Get modules
     *
     * @return array
     */
    public function getModules();

    /**
     * Get total dependencies count
     *
     * @return int
     */
    public function getDependenciesCount();
}
