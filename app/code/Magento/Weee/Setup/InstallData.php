<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataResourceInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataResourceInterface $setup, ModuleContextInterface $context)
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup->addAttribute('quote_item', 'weee_tax_applied', ['type' => 'text']);
        $quoteSetup->addAttribute('quote_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'weee_tax_disposition', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);


        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order_item', 'weee_tax_applied', ['type' => 'text']);
        $salesSetup->addAttribute('order_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'weee_tax_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('order_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);

        $salesSetup->addAttribute('invoice_item', 'weee_tax_applied', ['type' => 'text']);
        $salesSetup->addAttribute('invoice_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'weee_tax_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);

        $salesSetup->addAttribute('creditmemo_item', 'weee_tax_applied', ['type' => 'text']);
        $salesSetup->addAttribute('creditmemo_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'weee_tax_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);
    }
}
