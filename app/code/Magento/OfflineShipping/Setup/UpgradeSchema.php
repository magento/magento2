<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade schema DB for OfflineShipping module.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private static $quoteConnectionName = 'checkout';

    /**
     * @var string
     */
    private static $salesConnectionName = 'sales';

    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->updateFreeShippingColumns($setup);
        }

        $setup->endSetup();
    }

    /**
     * Modify free_shipping columns added incorrectly in InstallSchema.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function updateFreeShippingColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable('salesrule'),
            'simple_free_shipping',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Simple Free Shipping',
            ]

        );
        $setup->getConnection(self::$salesConnectionName)->modifyColumn(
            $setup->getTable('sales_order_item', self::$salesConnectionName),
            'free_shipping',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Free Shipping',
            ]

        );
        $setup->getConnection(self::$quoteConnectionName)->modifyColumn(
            $setup->getTable('quote_address', self::$quoteConnectionName),
            'free_shipping',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Free Shipping',
            ]

        );
        $setup->getConnection(self::$quoteConnectionName)->modifyColumn(
            $setup->getTable('quote_item', self::$quoteConnectionName),
            'free_shipping',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Free Shipping',
            ]
        );
        $setup->getConnection(self::$quoteConnectionName)->modifyColumn(
            $setup->getTable('quote_address_item', self::$quoteConnectionName),
            'free_shipping',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'comment' => 'Free Shipping',
            ]
        );
    }
}
