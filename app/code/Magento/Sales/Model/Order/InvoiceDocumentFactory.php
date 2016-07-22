<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceInterfaceFactory;

/**
 * Class InvoiceDocumentFactory
 *
 * @api
 */
class InvoiceDocumentFactory
{
    private $invoiceFactory;

    public function __construct(
        InvoiceInterfaceFactory $invoiceFactory
    ) {
        $this->invoiceFactory = $invoiceFactory;
    }

    /**
     * @param int $orderId
     * @param InvoiceItemCreationInterface[] $items
     * @param InvoiceCommentCreationInterface|null $comment
     * @param InvoiceCreationArgumentsInterface|null $arguments
     * @return InvoiceInterface
     */
    public function create(
        $orderId,
        $items = [],
        InvoiceCommentCreationInterface $comment = null,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        /** @var InvoiceInterface $invoice */
        $invoice = $this->invoiceFactory->create();
        $invoice->setOrderId($orderId);
        $invoice->setItems($items);
        $invoice->setComments([$comment]);
        return $invoice;
    }
}
