<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Setup;

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

        $table = $installer->getConnection()->newTable(
            $installer->getTable('catalogsearch_recommendations')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'query_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Query Id'
        )->addColumn(
            'relation_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Relation Id'
        )->addForeignKey(
            $installer->getFkName('catalogsearch_recommendations', 'query_id', 'search_query', 'query_id'),
            'query_id',
            $installer->getTable('search_query'),
            'query_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('catalogsearch_recommendations', 'relation_id', 'search_query', 'query_id'),
            'relation_id',
            $installer->getTable('search_query'),
            'query_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment('Advanced Search Recommendations');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
