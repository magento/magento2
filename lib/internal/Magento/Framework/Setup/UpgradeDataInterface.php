<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for data upgrades of a module
 *
 * @api
 * @since 2.0.0
 */
interface UpgradeDataInterface
{
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @since 2.0.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context);
}
