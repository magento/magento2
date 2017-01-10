<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Sales\Model\ResourceModel\Order\Handler\Address as AddressHandler;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment as OrderPaymentResource;
use Magento\Sales\Model\ResourceModel\Order\Status\History as OrderStatusHistoryResource;
use Magento\Sales\Api\OrderItemRepositoryInterface;

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
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

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
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderPaymentResource $orderPaymentResource
     * @param OrderStatusHistoryResource $orderStatusHistoryResource
     */
    public function __construct(
        AddressHandler $addressHandler,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderPaymentResource $orderPaymentResource,
        OrderStatusHistoryResource $orderStatusHistoryResource
    ) {
        $this->addressHandler = $addressHandler;
        $this->orderItemRepository = $orderItemRepository;
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

        if (null !== $object->getItems()) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($object->getItems() as $item) {
                $item->setOrderId($object->getId());
                $item->setOrder($object);
                $this->orderItemRepository->save($item);
            }
        }
        if (null !== $object->getPayment()) {
            $payment = $object->getPayment();
            $payment->setParentId($object->getId());
            $payment->setOrder($object);
            $this->orderPaymentResource->save($payment);
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
        $this->addressHandler->removeEmptyAddresses($object);
        $this->addressHandler->process($object);
    }
}
