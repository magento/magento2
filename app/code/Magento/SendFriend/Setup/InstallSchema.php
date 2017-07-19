<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Setup;

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
            $installer->getTable('sendfriend_log')
        )->addColumn(
            'log_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Log ID'
        )->addColumn(
            'ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            '20',
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Customer IP address'
        )->addColumn(
            'time',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Log time'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Website ID'
        )->addIndex(
            $installer->getIdxName('sendfriend_log', 'ip'),
            'ip'
        )->addIndex(
            $installer->getIdxName('sendfriend_log', 'time'),
            'time'
        )->setComment(
            'Send to friend function log storage table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
