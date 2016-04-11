<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;


    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(SalesSetupFactory $salesSetupFactory, QuoteSetupFactory $quoteSetupFactory)
    {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }
    
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
            'paypal_canceled_reversal'  => __('PayPal Canceled Reversal'),
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
}
