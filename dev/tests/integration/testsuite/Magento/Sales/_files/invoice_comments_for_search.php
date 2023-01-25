<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
/** @var InvoiceManagementInterface $orderService */
$orderService = $objectManager->create(InvoiceManagementInterface::class);
/** @var Invoice $invoice */
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
/** @var Order $order */
$order = $invoice->getOrder();
$order->setIsInProcess(true);
/** @var Transaction $transactionSave */
$transactionSave = $objectManager->create(Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();

$comments = [
    [
        'comment' => 'comment 1',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 2',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 3',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 4',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 5',
        'is_visible_on_front' => 0,
        'is_customer_notified' => 1,
    ],
];

/** @var InvoiceCommentRepositoryInterface $commentRepository */
$commentRepository = $objectManager->get(InvoiceCommentRepositoryInterface::class);

foreach ($comments as $data) {
    /** @var $comment Comment */
    $comment = $objectManager->create(Comment::class);
    $comment->setParentId($invoice->getId());
    $comment->setComment($data['comment']);
    $comment->setIsVisibleOnFront($data['is_visible_on_front']);
    $comment->setIsCustomerNotified($data['is_customer_notified']);
    $commentRepository->save($comment);
}
