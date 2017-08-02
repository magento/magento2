<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'indexer_state'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('indexer_state'))
            ->addColumn(
                'state_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Indexer State Id'
            )
            ->addColumn(
                'indexer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Indexer Id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                ['default' => 'invalid'],
                'Indexer Status'
            )
            ->addColumn(
                'updated',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Indexer Status'
            )
            ->addColumn(
                'hash_config',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Hash of indexer config'
            )
            ->addIndex(
                $installer->getIdxName('indexer_state', ['indexer_id']),
                ['indexer_id']
            )
            ->setComment('Indexer State');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mview_state'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mview_state'))
            ->addColumn(
                'state_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'View State Id'
            )
            ->addColumn(
                'view_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'View Id'
            )
            ->addColumn(
                'mode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                ['default' => 'disabled'],
                'View Mode'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                ['default' => 'idle'],
                'View Status'
            )
            ->addColumn(
                'updated',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'View updated time'
            )
            ->addColumn(
                'version_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'View Version Id'
            )
            ->addIndex(
                $installer->getIdxName('mview_state', ['view_id']),
                ['view_id']
            )
            ->addIndex(
                $installer->getIdxName('mview_state', ['mode']),
                ['mode']
            )
            ->setComment('View State');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
