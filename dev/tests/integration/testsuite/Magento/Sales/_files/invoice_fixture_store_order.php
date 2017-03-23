<?php
/**
 * Paid invoice fixture.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'order_fixture_store.php';
/** @var \Magento\Sales\Model\Order $order */

$orderService = \Magento\TestFramework\ObjectManager::getInstance()->create(
    \Magento\Sales\Api\InvoiceManagementInterface::class
);
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
$order = $invoice->getOrder();
$order->setIsInProcess(true);
$transactionSave = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\DB\Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();
