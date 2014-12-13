<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $installer \Magento\Sales\Model\Resource\Setup */
$installer = $this;

/**
 * Prepare database for install
 */
$installer->startSetup();
/**
 * Add paypal attributes to the:
 *  - sales/flat_quote_payment_item table
 *  - sales/flat_order table
 */
$installer->addAttribute('quote_payment', 'paypal_payer_id', []);
$installer->addAttribute('quote_payment', 'paypal_payer_status', []);
$installer->addAttribute('quote_payment', 'paypal_correlation_id', []);
$installer->addAttribute(
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
$installer->getConnection()->insertArray($installer->getTable('sales_order_status'), ['status', 'label'], $data);

/**
 * Prepare database after install
 */
$installer->endSetup();
