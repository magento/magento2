<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class InvoiceDocumentFactory
 *
 * @api
 * @since 2.1.2
 */
class InvoiceDocumentFactory
{
    /**
     * @var InvoiceService
     * @since 2.1.2
     */
    private $invoiceService;

    /**
     * InvoiceDocumentFactory constructor.
     * @param InvoiceService $invoiceService
     * @since 2.1.2
     */
    public function __construct(
        InvoiceService $invoiceService
    ) {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @param OrderInterface $order
     * @param array $items
     * @param InvoiceCommentCreationInterface|null $comment
     * @param bool|false $appendComment
     * @param InvoiceCreationArgumentsInterface|null $arguments
     * @return InvoiceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.2
     */
    public function create(
        OrderInterface $order,
        $items = [],
        InvoiceCommentCreationInterface $comment = null,
        $appendComment = false,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        $invoiceItems = $this->itemsToArray($items);
        $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

        if ($comment) {
            $invoice->addComment(
                $comment->getComment(),
                $appendComment,
                $comment->getIsVisibleOnFront()
            );
        }

        return $invoice;
    }

    /**
     * Convert Items To Array
     *
     * @param InvoiceItemCreationInterface[] $items
     * @return array
     * @since 2.1.2
     */
    private function itemsToArray($items = [])
    {
        $invoiceItems = [];
        foreach ($items as $item) {
            $invoiceItems[$item->getOrderItemId()] = $item->getQty();
        }
        return $invoiceItems;
    }
}
