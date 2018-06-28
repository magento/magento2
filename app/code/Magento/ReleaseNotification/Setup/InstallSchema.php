<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Setup;

use Magento\Framework\DB\Ddl\Table;
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
        $setup->startSetup();

        /**
         * Create table 'release_notification_viewer_log'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('release_notification_viewer_log')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Log ID'
        )->addColumn(
            'viewer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Viewer admin user ID'
        )->addColumn(
            'last_view_version',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16,
            ['nullable' => false],
            'Viewer last view on product version'
        )->addIndex(
            $setup->getIdxName(
                'release_notification_viewer_log',
                ['viewer_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['viewer_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $setup->getFkName('release_notification_viewer_log', 'viewer_id', 'admin_user', 'user_id'),
            'viewer_id',
            $setup->getTable('admin_user'),
            'user_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Release Notification Viewer Log Table'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
