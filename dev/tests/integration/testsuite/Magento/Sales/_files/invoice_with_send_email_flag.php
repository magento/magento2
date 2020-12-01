<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var OrderInterfaceFactory $orderFactory */
$orderFactory = $objectManager->get(OrderInterfaceFactory::class);
/** @var InvoiceService $invoiceService */
$invoiceService = $objectManager->get(InvoiceManagementInterface::class);
/** @var Transaction $transactionSave */
$transactionSave = $objectManager->get(Transaction::class);
/** @var Order $order */
$order = $orderFactory->create()->loadByIncrementId('100000001');

$invoice = $invoiceService->prepareInvoice($order);
$invoice->register();
$invoice->setSendEmail(true);
$order->setIsInProcess(true);
$transactionSave->addObject($invoice)->addObject($order)->save();
