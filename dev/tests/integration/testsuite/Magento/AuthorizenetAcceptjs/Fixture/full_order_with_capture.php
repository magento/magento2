<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;

// phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
$order = include __DIR__ . '/../_files/full_order.php';

$objectManager = Bootstrap::getObjectManager();

/** @var Payment $payment */
$payment = $order->getPayment();
$payment->setMethod(Config::METHOD);
$payment->setAuthorizationTransaction(false);
$payment->setParentTransactionId(4321);

/** @var OrderRepository $orderRepo */
$orderRepo = $objectManager->get(OrderRepository::class);
$orderRepo->save($order);

/** @var InvoiceService $invoiceService */
$invoiceService = $objectManager->get(InvoiceService::class);
$invoice = $invoiceService->prepareInvoice($order);
$invoice->setIncrementId('100000001');
$invoice->register();

/** @var InvoiceRepositoryInterface $invoiceRepository */
$invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
$invoice = $invoiceRepository->save($invoice);


/** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
$creditmemo = $creditmemoFactory->createByInvoice($invoice, $invoice->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Magento\Sales\Model\Order\Creditmemo::STATE_OPEN);
$creditmemo->setIncrementId('100000001');

/** @var \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository */
$creditmemoRepository = $objectManager->get(\Magento\Sales\Api\CreditmemoRepositoryInterface::class);
$creditmemoRepository->save($creditmemo);

/** @var TransactionBuilder $transactionBuilder */
$transactionBuilder = $objectManager->create(TransactionBuilder::class);
$transactionAuthorize = $transactionBuilder->setPayment($payment)
    ->setOrder($order)
    ->setTransactionId(1234)
    ->build(Transaction::TYPE_AUTH);
$transactionCapture = $transactionBuilder->setPayment($payment)
    ->setOrder($order)
    ->setTransactionId(4321)
    ->build(Transaction::TYPE_CAPTURE);

$transactionRepository = $objectManager->create(TransactionRepositoryInterface::class);
$transactionRepository->save($transactionAuthorize);
$transactionRepository->save($transactionCapture);
