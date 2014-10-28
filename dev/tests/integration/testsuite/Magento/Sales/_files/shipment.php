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
require 'default_rollback.php';
require __DIR__ . '/../../../Magento/Sales/_files/order.php';

$payment = $order->getPayment();
$paymentInfoBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Payment\Helper\Data')
    ->getInfoBlock($payment);
$payment->setBlockMock($paymentInfoBlock);

/** @var \Magento\Sales\Model\Order\Shipment $shipment */
$shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment');
$shipment->setOrder($order);

$shipmentItem = $objectManager->create('Magento\Sales\Model\Order\Shipment\Item');
$shipmentItem->setOrderItem($orderItem);
$shipment->addItem($shipmentItem);
$shipment->setPackages([['1'], ['2']]);
$shipment->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW);

$shipment->save();
