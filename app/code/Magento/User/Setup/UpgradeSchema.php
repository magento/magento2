<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableAdmins = $installer->getTable('admin_user');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'failures_num',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Failure Number'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'first_failure',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'comment' => 'First Failure'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'lock_expires',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'comment' => 'Expiration Lock Dates'
            ]
        );

        /**
         * Create table 'admin_passwords'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('admin_passwords')
        )->addColumn(
            'password_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Password Id'
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'User Id'
        )->addColumn(
            'password_hash',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Password Hash'
        )->addColumn(
            'expires',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Expires'
        )->addColumn(
            'last_updated',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Last Updated'
        )->addIndex(
            $installer->getIdxName('admin_passwords', ['user_id']),
            ['user_id']
        )->addForeignKey(
            $installer->getFkName('admin_passwords', 'user_id', 'admin_user', 'user_id'),
            'user_id',
            $installer->getTable('admin_user'),
            'user_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Admin Passwords'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
