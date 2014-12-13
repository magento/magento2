<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Framework\Module\DataSetup */
$installer = $this;

$data = [];
$statuses = [
    'pending_ogone' => __('Pending Ogone'),
    'cancel_ogone' => __('Cancelled Ogone'),
    'decline_ogone' => __('Declined Ogone'),
    'processing_ogone' => __('Processing Ogone Payment'),
    'processed_ogone' => __('Processed Ogone Payment'),
    'waiting_authorozation' => __('Waiting Authorization'),
];
foreach ($statuses as $code => $info) {
    $data[] = ['status' => $code, 'label' => $info];
}
$installer->getConnection()->insertArray($installer->getTable('sales_order_status'), ['status', 'label'], $data);

$data = [];
$states = [
    'pending_payment' => ['statuses' => ['pending_ogone' => []]],
    'processing' => ['statuses' => ['processed_ogone' => []]],
];

foreach ($states as $code => $info) {
    if (isset($info['statuses'])) {
        foreach ($info['statuses'] as $status => $statusInfo) {
            $data[] = [
                'status' => $status,
                'state' => $code,
                'is_default' => is_array($statusInfo) && isset($statusInfo['default']) ? 1 : 0,
            ];
        }
    }
}
$installer->getConnection()->insertArray(
    $installer->getTable('sales_order_status_state'),
    ['status', 'state', 'is_default'],
    $data
);
