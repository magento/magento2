<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for DB schema upgrades of a module
 */
interface UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param ModuleSchemaResourceInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleSchemaResourceInterface $setup, ModuleContextInterface $context);
}
