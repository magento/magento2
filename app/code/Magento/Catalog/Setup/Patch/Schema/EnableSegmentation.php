<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Class EnableSegmentation.
 *
 * @package Magento\Catalog\Setup\Patch\Schema
 */
class EnableSegmentation implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * EnableSegmentation constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $setup = $this->schemaSetup;

        $storeSelect = $setup->getConnection()->select()->from($setup->getTable('store'))->where('store_id > 0');
        foreach ($setup->getConnection()->fetchAll($storeSelect) as $store) {
            $indexTable = $setup->getTable('catalog_category_product_index') .
                '_' .
                \Magento\Store\Model\Store::ENTITY .
                $store['store_id'];
            if (!$setup->getConnection()->isTableExists($indexTable)) {
                $setup->getConnection()->createTable(
                    $setup->getConnection()->createTableByDdl(
                        $setup->getTable('catalog_category_product_index'),
                        $indexTable
                    )
                );
            }
            if (!$setup->getConnection()->isTableExists($indexTable . '_replica')) {
                $setup->getConnection()->createTable(
                    $setup->getConnection()->createTableByDdl(
                        $setup->getTable('catalog_category_product_index'),
                        $indexTable . '_replica'
                    )
                );
            }
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
