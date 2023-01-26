<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class CleanUpProductPriceReplicaTable implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private SchemaSetupInterface $schemaSetup;

    /**
     * CleanUpProductPriceReplicaTable constructor.
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        $connection = $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection();

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
