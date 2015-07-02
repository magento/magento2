<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Resource\Order;

use Magento\Sales\Model\Resource\Order\Handler\Address as AddressHandler;
use Magento\Framework\Model\Resource\Db\VersionControl\RelationInterface;
use Magento\Sales\Model\Resource\Order\Item as OrderItemResource;
use Magento\Sales\Model\Resource\Order\Payment as OrderPaymentResource;
use Magento\Sales\Model\Resource\Order\Status\History as OrderStatusHistoryResource;

/**
 * Class Relation
 */
class Relation implements RelationInterface
{
    /**
     * @var AddressHandler
     */
    protected $addressHandler;

    /**
     * @var OrderItemResource
     */
    protected $orderItemResource;

    /**
     * @var OrderPaymentResource
     */
    protected $orderPaymentResource;

    /**
     * @var OrderStatusHistoryResource
     */
    protected $orderStatusHistoryResource;

    /**
     * @param AddressHandler $addressHandler
     * @param OrderItemResource $orderItemResource
     * @param OrderPaymentResource $orderPaymentResource
     * @param OrderStatusHistoryResource $orderStatusHistoryResource
     */
    public function __construct(
        AddressHandler $addressHandler,
        OrderItemResource $orderItemResource,
        OrderPaymentResource $orderPaymentResource,
        OrderStatusHistoryResource $orderStatusHistoryResource
    ) {
        $this->addressHandler = $addressHandler;
        $this->orderItemResource = $orderItemResource;
        $this->orderPaymentResource = $orderPaymentResource;
        $this->orderStatusHistoryResource = $orderStatusHistoryResource;
    }

    /**
     * Save relations for Order
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Exception
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->addressHandler->removeEmptyAddresses($object);
        $this->addressHandler->process($object);
        if (null !== $object->getItems()) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($object->getItems() as $item) {
                $item->setOrderId($object->getId());
                $item->setOrder($object);
                $this->orderItemResource->save($item);
            }
        }
        if (null !== $object->getPayments()) {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            foreach ($object->getPayments() as $payment) {
                $payment->setParentId($object->getId());
                $payment->setOrder($object);
                $this->orderPaymentResource->save($payment);
            }
        }
        if (null !== $object->getStatusHistories()) {
            /** @var \Magento\Sales\Model\Order\Status\History $statusHistory */
            foreach ($object->getStatusHistories() as $statusHistory) {
                $statusHistory->setParentId($object->getId());
                $statusHistory->setOrder($object);
                $this->orderStatusHistoryResource->save($statusHistory);

            }
        }
        if (null !== $object->getRelatedObjects()) {
            foreach ($object->getRelatedObjects() as $relatedObject) {
                $relatedObject->setOrder($object);
                $relatedObject->save();
            }
        }
    }
}
