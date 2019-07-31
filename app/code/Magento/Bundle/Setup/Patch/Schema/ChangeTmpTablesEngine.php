<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Change engine for temporary tables to InnoDB.
 */
class ChangeTmpTablesEngine implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $tables = [
            'catalog_product_index_price_bundle_tmp',
            'catalog_product_index_price_bundle_sel_tmp',
            'catalog_product_index_price_bundle_opt_tmp',
        ];
        foreach ($tables as $table) {
            $this->schemaSetup->getConnection()->changeTableEngine($table, 'InnoDB');
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
