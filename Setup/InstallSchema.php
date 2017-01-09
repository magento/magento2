<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\MessageQueue\Model\ResourceModel\Lock;

/**
 * Initializes lock table to lock messages that were processed already.
 *
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
         * Create table 'queue_lock'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(Lock::QUEUE_LOCK_TABLE)
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Message ID'
        )->addColumn(
            'message_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => '', 'nullable' => false],
            'Message Code'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Created At'
        )->addIndex(
            $installer->getIdxName(
                'queue_lock',
                'message_code',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'message_code',
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Messages that were processed are inserted here to be locked.'
        );
        $installer->getConnection()->createTable($table);
    }
}
