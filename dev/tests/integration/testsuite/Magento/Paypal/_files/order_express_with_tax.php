<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/order_express.php';

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

$subTotal = 121;
$taxRate = .0825;
$taxAmount = $subTotal * $taxRate;
$shippingAmount = 15;
$totalAmount =  $subTotal + $taxAmount + $shippingAmount;

$order->setSubtotal($subTotal);
$order->setBaseSubtotal($subTotal);
$order->setGrandTotal($totalAmount);
$order->setBaseGrandTotal($totalAmount);
$order->setTaxAmount($taxAmount);

/** @var OrderRepository $orderRepository */
$orderRepository = $objectManager->get(OrderRepository::class);
$orderRepository->save($order);
