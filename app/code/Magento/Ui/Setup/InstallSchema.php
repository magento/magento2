<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Setup;

use Magento\Framework\DB\Ddl\Table;
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
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $setup->startSetup();
        $table = $connection->newTable($setup->getTable('ui_bookmark'))
            ->addColumn(
                'bookmark_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Bookmark identifier'
            )
            ->addColumn(
                'user_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'User Id'
            )
            ->addColumn(
                'namespace',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Bookmark namespace'
            )
            ->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Bookmark Identifier'
            )
            ->addColumn(
                'current',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false],
                'Mark current bookmark per user and identifier'
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => true], 'Bookmark title')
            ->addColumn('config', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => true], 'Bookmark config')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['nullable' => false], 'Bookmark created at')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['nullable' => false], 'Bookmark updated at')
            ->addIndex(
                $setup->getIdxName(
                    'ui_bookmark',
                    ['user_id', 'namespace', 'identifier'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['user_id', 'namespace', 'identifier'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            )
            ->addForeignKey(
                $setup->getFkName('ui_bookmark', 'user_id', 'admin_user', 'user_id'),
                'user_id',
                $setup->getTable('admin_user'),
                'user_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Bookmark');
        $connection->createTable($table);
        $setup->endSetup();
    }
}
