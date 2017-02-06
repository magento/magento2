<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Setup;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'search_query'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('search_query'))
            ->addColumn(
                'query_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Query ID'
            )
            ->addColumn(
                'query_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Query text'
            )
            ->addColumn(
                'num_results',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Num results'
            )
            ->addColumn(
                'popularity',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Popularity'
            )
            ->addColumn(
                'redirect',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Redirect'
            )
            ->addColumn(
                'synonym_for',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Synonym for'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'display_in_terms',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Display in terms'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['default' => '1'],
                'Active status'
            )
            ->addColumn(
                'is_processed',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['default' => '0'],
                'Processed status'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated at'
            )
            ->addIndex(
                $installer->getIdxName('search_query', ['query_text', 'store_id', 'popularity']),
                ['query_text', 'store_id', 'popularity']
            )
            ->addIndex(
                $installer->getIdxName('search_query', 'store_id'),
                'store_id'
            )
            ->addIndex(
                $installer->getIdxName('search_query', 'is_processed'),
                'is_processed'
            )
            ->addIndex(
                $installer->getIdxName('search_query', 'synonym_for'),
                'synonym_for'
            )
            ->addForeignKey(
                $installer->getFkName('search_query', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Search query table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
