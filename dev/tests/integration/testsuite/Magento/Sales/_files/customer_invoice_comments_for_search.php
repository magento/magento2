<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Comment as CommentResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Sales/_files/customers_with_invoices.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
/** @var Invoice $invoice */
$invoice = $order->getInvoiceCollection()->getFirstItem();

$comments = [
    [
        'comment' => 'visible_comment',
        'is_visible_on_front' => 1
    ],
    [
        'comment' => 'non_visible_comment',
        'is_visible_on_front' => 0,
    ],
];

/** @var CommentResource $commentResource */
$commentResource = $objectManager->get(CommentResource::class);

foreach ($comments as $data) {
    /** @var Comment $comment */
    $comment = $objectManager->create(Comment::class);
    $comment->setParentId($invoice->getId());
    $comment->setComment($data['comment']);
    $comment->setIsVisibleOnFront($data['is_visible_on_front']);
    $comment->setIsCustomerNotified(false);
    $commentResource->save($comment);
}
