<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Weee\Model\Resource\Setup */
$quoteSetup = $this->createQuoteSetup(['resourceName' => 'quote_setup']);
$quoteSetup->addAttribute('quote_item', 'weee_tax_applied', ['type' => 'text']);
$quoteSetup->addAttribute('quote_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'weee_tax_disposition', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
$quoteSetup->addAttribute('quote_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);

$salesSetup = $this->createSalesSetup(['resourceName' => 'sales_setup']);
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
