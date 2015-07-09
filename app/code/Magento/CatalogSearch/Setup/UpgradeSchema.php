<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;


use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $connection->dropTable($installer->getTable('catalogsearch_fulltext'));
            $table = $connection->newTable($installer->getTable('catalogsearch_fulltext_index_default'))
                ->addColumn(
                    'FTS_DOC_ID',
                    Table::TYPE_BIGINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'auto_increment' => true, 'primary' => true],
                    'Entity ID'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )->addColumn(
                    'attribute_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false]
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store ID'
                )->addColumn(
                    'data_index',
                    Table::TYPE_TEXT,
                    '4g',
                    ['nullable' => true],
                    'Data index'
                )->addIndex(
                    'FTI_CATALOGSEARCH_FULLTEXT_DATA_INDEX',
                    ['data_index'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                );
            $connection->createTable($table);
        }
    }
}
