<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $connection->dropIndex(
                $setup->getTable('search_query'),
                $installer->getIdxName('search_query', ['query_text', 'store_id'])
            );
            $connection->addIndex(
                $setup->getTable('search_query'),
                $installer->getIdxName(
                    'search_query',
                    ['query_text', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['query_text', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            /**
             * Create table 'search_synonyms'
             */
            $table = $connection
                ->newTable($installer->getTable('search_synonyms'))
                ->addColumn(
                    'group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Synonyms Group Id'
                )
                ->addColumn(
                    'synonyms',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    65535,
                    ['nullable' => false],
                    'list of synonyms making up this group'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Store Id - identifies the store view these synonyms belong to'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $installer->getTable('search_synonyms'),
                        ['synonyms'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['synonyms'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                )
                ->addIndex(
                    $installer->getIdxName('search_synonyms', 'store_id'),
                    ['store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addForeignKey(
                    $installer->getFkName('search_synonyms', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('table storing various synonyms groups per store view');

            $connection->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            // Drop and recreate 'search_synonyms' table
            $connection->dropTable($installer->getTable('search_synonyms'));

            $table = $connection
                ->newTable($installer->getTable('search_synonyms'))
                ->addColumn(
                    'group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Synonyms Group Id'
                )
                ->addColumn(
                    'synonyms',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    65535,
                    ['nullable' => false],
                    'list of synonyms making up this group'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Store Id - identifies the store view these synonyms belong to'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Website Id - identifies the website id these synonyms belong to'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $installer->getTable('search_synonyms'),
                        ['synonyms'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['synonyms'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                )
                ->addIndex(
                    $installer->getIdxName('search_synonyms', 'store_id'),
                    ['store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $installer->getIdxName('search_synonyms', 'website_id'),
                    ['website_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addForeignKey(
                    $installer->getFkName('search_synonyms', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('search_synonyms', 'website_id', 'store_website', 'website_id'),
                    'website_id',
                    $installer->getTable('store_website'),
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('table storing various synonyms groups');

            $connection->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            $connection->dropIndex(
                $setup->getTable('search_query'),
                $installer->getIdxName('search_query', 'synonym_for')
            );
            $connection->dropColumn($setup->getTable('search_query'), 'synonym_for');
        }
    }
}
