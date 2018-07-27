<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable(\Magento\Security\Setup\InstallSchema::ADMIN_SESSIONS_DB_TABLE_NAME),
                'ip',
                'ip',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 15,
                    'nullable' => false,
                    'comment' => 'Remote user IP'
                ]
            );

            $setup->getConnection()->changeColumn(
                $setup->getTable(\Magento\Security\Setup\InstallSchema::PASSWORD_RESET_REQUEST_EVENT_DB_TABLE_NAME),
                'ip',
                'ip',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 15,
                    'nullable' => false,
                    'comment' => 'Remote user IP'
                ]
            );
        }

        $setup->endSetup();
    }
}
