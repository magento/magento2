<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var Order $order */
Resolver::getInstance()->requireDataFixture('Magento/Braintree/Fixtures/order.php');

$objectManager = ObjectManager::getInstance();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000002');
/** @var InvoiceService $invoiceService */
$invoiceService = $objectManager->get(InvoiceService::class);
$invoice = $invoiceService->prepareInvoice($order);
$invoice->setIncrementId('100000002');
$invoice->register();

$items = $invoice->getAllItems();
$item = array_pop($items);
$item->setQty(1);
$invoice->setTotalQty(1);

$items = $order->getAllItems();
/** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
$item = array_pop($items);
$item->setQtyInvoiced(1);
$invoice->collectTotals();

/** @var InvoiceRepositoryInterface $invoiceRepository */
$invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
$invoice = $invoiceRepository->save($invoice);

/** @var TransactionRepositoryInterface $transactionRepository */
$transactionRepository = $objectManager->get(TransactionRepositoryInterface::class);
$transaction = $transactionRepository->create();
$transaction->setTxnType('capture');
$transaction->setPaymentId($order->getPayment()->getEntityId());
$transaction->setOrderId($order->getEntityId());
$transactionRepository->save($transaction);
