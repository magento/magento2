<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;

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
