<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\MessageQueue\Model\ResourceModel\Lock;

/**
 * Upgrade the MessageQueue module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->createQueueLockTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * Checks if queue lock table exists and creates one if it does not
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function createQueueLockTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;

        if ($installer->tableExists(Lock::QUEUE_LOCK_TABLE)) {
            return;
        };

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
