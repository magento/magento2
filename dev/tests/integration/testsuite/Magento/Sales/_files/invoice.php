<?php
/**
 * Paid invoice fixture.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'default_rollback.php';
require 'order.php';
/** @var \Magento\Sales\Model\Order $order */

$orderService = \Magento\TestFramework\ObjectManager::getInstance()->create(
    'Magento\Sales\Model\Service\Order',
    ['order' => $order]
);
$invoice = $orderService->prepareInvoice();
$invoice->register();
$order->setIsInProcess(true);
$transactionSave = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Framework\DB\Transaction');
$transactionSave->addObject($invoice)->addObject($order)->save();
