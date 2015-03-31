<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'catalogsearch_fulltext'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogsearch_fulltext'))
            ->addColumn(
                'fulltext_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'data_index',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '4g',
                [],
                'Data index'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalogsearch_fulltext',
                    ['product_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalogsearch_fulltext',
                    'data_index',
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                'data_index',
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
            )
            ->setOption(
                'type',
                'InnoDB'
            )
            ->setComment('Catalog search result table');

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addColumn(
            $installer->getTable('catalog_eav_attribute'),
            'search_weight',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '3',
                'comment' => 'Search Weight'
            ]
        );

        $installer->endSetup();

    }
}
