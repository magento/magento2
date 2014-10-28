<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Sales\Model\Resource\Setup */
$installer = $this;

/**
 * Install eav entity types to the eav/entity_type table
 */
$installer->installEntities();

/**
 * Install order statuses from config
 */
$data = array();
$statuses = array(
    'pending' => __('Pending'),
    'pending_payment' => __('Pending Payment'),
    'processing' => __('Processing'),
    'holded' => __('On Hold'),
    'complete' => __('Complete'),
    'closed' => __('Closed'),
    'canceled' => __('Canceled'),
    'fraud' => __('Suspected Fraud'),
    'payment_review' => __('Payment Review')
);
foreach ($statuses as $code => $info) {
    $data[] = array('status' => $code, 'label' => $info);
}
$installer->getConnection()->insertArray($installer->getTable('sales_order_status'), array('status', 'label'), $data);

/**
 * Install order states from config
 */
$data = array();
$states = array(
    'new' => array(
        'label' => __('New'),
        'statuses' => array('pending' => array('default' => '1')),
        'visible_on_front' => true
    ),
    'pending_payment' => array(
        'label' => __('Pending Payment'),
        'statuses' => array('pending_payment' => array('default' => '1'))
    ),
    'processing' => array(
        'label' => __('Processing'),
        'statuses' => array('processing' => array('default' => '1')),
        'visible_on_front' => true
    ),
    'complete' => array(
        'label' => __('Complete'),
        'statuses' => array('complete' => array('default' => '1')),
        'visible_on_front' => true
    ),
    'closed' => array(
        'label' => __('Closed'),
        'statuses' => array('closed' => array('default' => '1')),
        'visible_on_front' => true
    ),
    'canceled' => array(
        'label' => __('Canceled'),
        'statuses' => array('canceled' => array('default' => '1')),
        'visible_on_front' => true
    ),
    'holded' => array(
        'label' => __('On Hold'),
        'statuses' => array('holded' => array('default' => '1')),
        'visible_on_front' => true
    ),
    'payment_review' => array(
        'label' => __('Payment Review'),
        'statuses' => array('payment_review' => array('default' => '1'), 'fraud' => array()),
        'visible_on_front' => true
    )
);

foreach ($states as $code => $info) {
    if (isset($info['statuses'])) {
        foreach ($info['statuses'] as $status => $statusInfo) {
            $data[] = array(
                'status' => $status,
                'state' => $code,
                'is_default' => is_array($statusInfo) && isset($statusInfo['default']) ? 1 : 0
            );
        }
    }
}
$installer->getConnection()->insertArray(
    $installer->getTable('sales_order_status_state'),
    array('status', 'state', 'is_default'),
    $data
);
