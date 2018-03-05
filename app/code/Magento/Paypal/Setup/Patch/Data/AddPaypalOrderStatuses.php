<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Setup\Patch\Data;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class AddPaypalOrderStates
 * @package Magento\Paypal\Setup\Patch
 */
class AddPaypalOrderStatuses implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * AddPaypalOrderStates constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * Prepare database for install
         */
        $this->moduleDataSetup->getConnection()->startSetup();

        $quoteInstaller = $this->quoteSetupFactory->create();
        $salesInstaller = $this->salesSetupFactory->create();
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
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            $data
        );
        /**
         * Prepare database after install
         */
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
