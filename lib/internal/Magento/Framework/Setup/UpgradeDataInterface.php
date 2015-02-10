<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\Module\DataSetup;

/**
 * Interface for data upgrades of a module
 */
interface UpgradeDataInterface
{
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataResourceInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function update(ModuleDataResourceInterface $setup, ModuleContextInterface $context);
}
