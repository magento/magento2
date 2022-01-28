<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class CleanUpProductPriceReplicaTable implements SchemaPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        // There was a bug which caused the catalogrule_product_price_replica
        // table to become unnecessarily large. The bug causing the growth has
        // been resolved. This schema patch cleans up the damage done by that
        // bug on existing websites. Deleting all entries from the replica table
        // is safe.
        // See https://github.com/magento/magento2/issues/31752 for details.

        $tableName = $connection->getTableName('catalogrule_product_price_replica');

        if ($connection->isTableExists($tableName)) {
            $connection->truncateTable($tableName);
        }

        $connection->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
