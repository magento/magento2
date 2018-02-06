<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(QuoteSetupFactory $quoteSetupFactory
        , SalesSetupFactory $salesSetupFactory)
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        /**
         * Prepare database for install
         */
        $setup->startSetup();

        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        /**
         * Add paypal attributes to the:
         *  - sales/flat_quote_payment_item table
         *  - sales/flat_order table
         */
        $quoteInstaller->addAttribute('quote_payment', 'paypal_payer_id', []);
        $quoteInstaller->addAttribute('quote_payment', 'paypal_payer_status', []);
        $quoteInstaller->addAttribute('quote_payment', 'paypal_correlation_id', []);
        $salesInstaller->addAttribute(
            'order',
            'paypal_ipn_customer_notified',
            ['type' => 'int', 'visible' => false, 'default' => 0]
        );
        $data = [];
        $statuses = [
            'pending_paypal' => __('Pending PayPal'),
            'paypal_reversed' => __('PayPal Reversed'),
            'paypal_canceled_reversal' => __('PayPal Canceled Reversal'),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);
        /**
         * Prepare database after install
         */
        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


}
