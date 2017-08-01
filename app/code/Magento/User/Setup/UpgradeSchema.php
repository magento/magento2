<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Setup;

use Magento\Framework\DB\Ddl\Table;
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
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->addFailuresToAdminUserTable($setup);
            $this->createAdminPasswordsTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * Adds 'failures_num', 'first_failure', and 'lock_expires' columns to 'admin_user' table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function addFailuresToAdminUserTable(SchemaSetupInterface $setup)
    {
        $tableAdmins = $setup->getTable('admin_user');

        $setup->getConnection()->addColumn(
            $tableAdmins,
            'failures_num',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Failure Number'
            ]
        );
        $setup->getConnection()->addColumn(
            $tableAdmins,
            'first_failure',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'comment' => 'First Failure'
            ]
        );
        $setup->getConnection()->addColumn(
            $tableAdmins,
            'lock_expires',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'comment' => 'Expiration Lock Dates'
            ]
        );
    }

    /**
     * Create table 'admin_passwords'
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function createAdminPasswordsTable(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists($setup->getTable('admin_passwords'))) {
            return;
        }

        $table = $setup->getConnection()
            ->newTable($setup->getTable('admin_passwords'))
            ->addColumn(
                'password_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Password Id'
            )
            ->addColumn(
                'user_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'User Id'
            )
            ->addColumn(
                'password_hash',
                Table::TYPE_TEXT,
                100,
                [],
                'Password Hash'
            )
            ->addColumn(
                'expires',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Deprecated'
            )
            ->addColumn(
                'last_updated',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Last Updated'
            )
            ->addIndex(
                $setup->getIdxName('admin_passwords', ['user_id']),
                ['user_id']
            )
            ->addForeignKey(
                $setup->getFkName('admin_passwords', 'user_id', 'admin_user', 'user_id'),
                'user_id',
                $setup->getTable('admin_user'),
                'user_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Admin Passwords');

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->modifyColumn(
            $setup->getTable('admin_passwords'),
            'expires',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Deprecated',
            ]
        );
    }
}
