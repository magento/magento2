<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Invoice;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item as InvoiceItemResource;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Comment as InvoiceCommentResource;

/**
 * Class Relation
 * @since 2.0.0
 */
class Relation implements RelationInterface
{
    /**
     * @var InvoiceItemResource
     * @since 2.0.0
     */
    protected $invoiceItemResource;

    /**
     * @var InvoiceCommentResource
     * @since 2.0.0
     */
    protected $invoiceCommentResource;

    /**
     * @param InvoiceItemResource $invoiceItemResource
     * @param InvoiceCommentResource $invoiceCommentResource
     * @since 2.0.0
     */
    public function __construct(
        InvoiceItemResource $invoiceItemResource,
        InvoiceCommentResource $invoiceCommentResource
    ) {
        $this->invoiceItemResource = $invoiceItemResource;
        $this->invoiceCommentResource = $invoiceCommentResource;
    }

    /**
     * Process relations for Shipment
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var $object \Magento\Sales\Model\Order\Invoice */
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $item) {
                /** @var \Magento\Sales\Model\Order\Invoice\Item */
                $item->setParentId($object->getId());
                $item->setOrderItem($item->getOrderItem());
                $this->invoiceItemResource->save($item);
            }
        }

        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                /** @var \Magento\Sales\Model\Order\Invoice\Comment */
                $this->invoiceCommentResource->save($comment);
            }
        }
    }
}
