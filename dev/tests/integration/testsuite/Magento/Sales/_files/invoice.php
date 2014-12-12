<?php
/**
 * Paid invoice fixture.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
