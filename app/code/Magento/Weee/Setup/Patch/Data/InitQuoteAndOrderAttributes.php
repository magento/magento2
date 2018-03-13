<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Setup\Patch\Data;

use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InitQuoteAndOrderAttributes
 * @package Magento\Weee\Setup\Patch
 */
class InitQuoteAndOrderAttributes implements DataPatchInterface, PatchVersionInterface
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
     * InitQuoteAndOrderAttributes constructor.
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
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->moduleDataSetup]);
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
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);
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
