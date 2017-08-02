<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Data;

/**
 * Config
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get modules
     *
     * @return array
     * @since 2.0.0
     */
    public function getModules();

    /**
     * Get total dependencies count
     *
     * @return int
     * @since 2.0.0
     */
    public function getDependenciesCount();
}
