<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade the NewRelicReporting module DB scheme
 * @since 2.2.0
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            // The following fields are not 'unsigned' as they should after upgrade from 2.1
            $setup->getConnection()->modifyColumn(
                $setup->getTable('reporting_orders'),
                'total',
                ['unsigned' => true, 'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
            $setup->getConnection()->modifyColumn(
                $setup->getTable('reporting_orders'),
                'total_base',
                ['unsigned' => true, 'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            );
        }

        $setup->endSetup();
    }
}
