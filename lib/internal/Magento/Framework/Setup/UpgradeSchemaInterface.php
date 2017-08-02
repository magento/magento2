<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for DB schema upgrades of a module
 *
 * @api
 * @since 2.0.0
 */
interface UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @since 2.0.0
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context);
}
