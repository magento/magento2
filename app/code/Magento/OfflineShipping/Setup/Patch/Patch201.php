<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->updateQuoteShippingAddresses($setup);
        $setup->endSetup();

    }

    private function updateQuoteShippingAddresses(ModuleDataSetupInterface $setup
    )
    {
        $setup->getConnection()->update(
            $setup->getTable('salesrule'),
            ['simple_free_shipping' => 0],
            [new \Zend_Db_Expr('simple_free_shipping IS NULL')]
        );
        $setup->getConnection($this->salesConnectionName)->update(
            $setup->getTable('sales_order_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $setup->getConnection($this->quoteConnectionName)->update(
            $setup->getTable('quote_address'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $setup->getConnection($this->quoteConnectionName)->update(
            $setup->getTable('quote_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );

    }
}
