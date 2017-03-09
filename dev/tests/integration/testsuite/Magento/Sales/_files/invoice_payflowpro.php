<?php
/**
 * Not paid invoice fixture for online payment method.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'order_paid_with_payflowpro.php';
/** @var \Magento\Sales\Model\Order $order */

$orderService = \Magento\TestFramework\ObjectManager::getInstance()->create(
    \Magento\Sales\Api\InvoiceManagementInterface::class
);
$invoice = $orderService->prepareInvoice($order);
/** To allow invoice cancelling it should be created without capturing. */
$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::NOT_CAPTURE)->register();
$order = $invoice->getOrder();
$order->setIsInProcess(true);
$transactionSave = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\DB\Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();
