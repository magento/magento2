<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Sales\Model\Resource\Setup */

$this->addAttribute('quote_item', 'weee_tax_applied', ['type' => 'text']);
$this->addAttribute('quote_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('quote_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);

$this->addAttribute('order_item', 'weee_tax_applied', ['type' => 'text']);
$this->addAttribute('order_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('order_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
$this->addAttribute('order_item', 'weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('order_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
$this->addAttribute('order_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('order_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
$this->addAttribute('order_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('order_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);

$this->addAttribute('invoice_item', 'weee_tax_applied', ['type' => 'text']);
$this->addAttribute('invoice_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('invoice_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);

$this->addAttribute('creditmemo_item', 'weee_tax_applied', ['type' => 'text']);
$this->addAttribute('creditmemo_item', 'weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'weee_tax_applied_row_amount', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'weee_tax_row_disposition', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'base_weee_tax_applied_amount', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'base_weee_tax_applied_row_amnt', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'base_weee_tax_disposition', ['type' => 'decimal']);
$this->addAttribute('creditmemo_item', 'base_weee_tax_row_disposition', ['type' => 'decimal']);
