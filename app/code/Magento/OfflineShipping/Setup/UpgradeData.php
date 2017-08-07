<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script.
 */
class UpgradeData implements UpgradeDataInterface
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
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.1') < 0) {
            $this->updateQuoteShippingAddresses($setup);
        }
        $setup->endSetup();
    }

    /**
     * Replace Null with '0' for 'free_shipping' and 'simple_free_shipping' accordingly to upgraded schema.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function updateQuoteShippingAddresses(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('salesrule'),
            ['simple_free_shipping' => 0],
            [new \Zend_Db_Expr('simple_free_shipping IS NULL')]
        );
        $setup->getConnection(self::$salesConnectionName)->update(
            $setup->getTable('sales_order_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $setup->getConnection(self::$quoteConnectionName)->update(
            $setup->getTable('quote_address'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $setup->getConnection(self::$quoteConnectionName)->update(
            $setup->getTable('quote_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
    }
}
