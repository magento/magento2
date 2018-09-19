<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/order.php';

/** @var InvoiceManagementInterface $orderService */
$orderService = Bootstrap::getObjectManager()->create(InvoiceManagementInterface::class);
/** @var Invoice $invoice */
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
/** @var Order $order */
$order = $invoice->getOrder();
$order->setIsInProcess(true);
/** @var Transaction $transactionSave */
$transactionSave = Bootstrap::getObjectManager()->create(Transaction::class);
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
$commentRepository = Bootstrap::getObjectManager()->get(InvoiceCommentRepositoryInterface::class);

foreach ($comments as $data) {
    /** @var $comment Comment */
    $comment = Bootstrap::getObjectManager()->create(Comment::class);
    $comment->setParentId($invoice->getId());
    $comment->setComment($data['comment']);
    $comment->setIsVisibleOnFront($data['is_visible_on_front']);
    $comment->setIsCustomerNotified($data['is_customer_notified']);
    $commentRepository->save($comment);
}
