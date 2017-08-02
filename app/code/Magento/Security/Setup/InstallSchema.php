<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Session DB table name
     */
    const ADMIN_SESSIONS_DB_TABLE_NAME = 'admin_user_session';

    /**
     * Security Control DB table name
     */
    const PASSWORD_RESET_REQUEST_EVENT_DB_TABLE_NAME = 'password_reset_request_event';

    /**
     * Admin user table name
     */
    const ADMIN_USER_DB_TABLE_NAME = 'admin_user';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.1.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'admin_user_session'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::ADMIN_SESSIONS_DB_TABLE_NAME))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'session_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Session id value'
            )
            ->addColumn(
                'user_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Admin User ID'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                'Current Session status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addColumn(
                'ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Remote user IP'
            )
            ->addIndex(
                $installer->getIdxName(self::ADMIN_SESSIONS_DB_TABLE_NAME, ['session_id']),
                ['session_id']
            )
            ->addIndex(
                $installer->getIdxName(self::ADMIN_SESSIONS_DB_TABLE_NAME, ['user_id']),
                ['user_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::ADMIN_SESSIONS_DB_TABLE_NAME,
                    'user_id',
                    self::ADMIN_USER_DB_TABLE_NAME,
                    'user_id'
                ),
                'user_id',
                $installer->getTable(self::ADMIN_USER_DB_TABLE_NAME),
                'user_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Admin User sessions table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'password_reset_request_event'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::PASSWORD_RESET_REQUEST_EVENT_DB_TABLE_NAME))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'request_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Type of the event under a security control'
            )
            ->addColumn(
                'account_reference',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'An identifier for existing account or another target'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Timestamp when the event occurs'
            )
            ->addColumn(
                'ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Remote user IP'
            )
            ->addIndex(
                $installer->getIdxName(self::PASSWORD_RESET_REQUEST_EVENT_DB_TABLE_NAME, ['account_reference']),
                ['account_reference']
            )
            ->addIndex(
                $installer->getIdxName(self::PASSWORD_RESET_REQUEST_EVENT_DB_TABLE_NAME, ['created_at']),
                ['created_at']
            )
            ->setComment('Password Reset Request Event under a security control');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
