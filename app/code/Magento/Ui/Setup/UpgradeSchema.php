<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable('ui_bookmark'),
                'created_at',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Bookmark created at'
            );
            $setup->getConnection()->modifyColumn(
                $setup->getTable('ui_bookmark'),
                'updated_at',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Bookmark updated at'
            );
        }
        $setup->endSetup();
    }
}
