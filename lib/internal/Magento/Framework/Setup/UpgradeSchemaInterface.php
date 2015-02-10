<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Setup\Module\SetupModule;

/**
 * Interface for DB schema upgrades of a module
 */
interface UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param ModuleResourceInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function update(ModuleResourceInterface $setup, ModuleContextInterface $context);
}
