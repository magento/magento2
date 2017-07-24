<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Upgrade Data script.
 */
class UpgradeData implements UpgradeDataInterface
{
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
     * Replace Null with '0' for free_shipping in quote shipping addresses.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function updateQuoteShippingAddresses(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('quote_address'),
            ['free_shipping' => 0],
            [
                'address_type = ?' => Address::ADDRESS_TYPE_SHIPPING,
                new \Zend_Db_Expr('free_shipping IS NULL'),
            ]
        );
    }
}
