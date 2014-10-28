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

require __DIR__ . '/../../../Magento/Sales/_files/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001')->setBaseToGlobalRate(2)->save();

/** @var \Magento\Tax\Model\Sales\Order\Tax $tax */
$tax = $objectManager->create('Magento\Tax\Model\Sales\Order\Tax');
$tax->setData(
    array(
        'order_id' => $order->getId(),
        'code' => 'tax_code',
        'title' => 'Tax Title',
        'hidden' => 0,
        'percent' => 10,
        'priority' => 1,
        'position' => 1,
        'amount' => 10,
        'base_amount' => 10,
        'process' => 1,
        'base_real_amount' => 10
    )
);
$tax->save();
