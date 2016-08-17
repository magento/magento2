<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class ShipmentQuantityValidator
 */
class ShipmentQuantityValidator implements ValidatorInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * InvoiceValidator constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param ShipmentInterface $entity
     * @return string[]
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     */
    public function validate($entity)
    {
        if ($entity->getOrderId() === null) {
            return [__('Order Id is required for shipment document')];
        }

        if ($entity->getItems() === null) {
            return [__('You can\'t create a shipment without products.')];
        }
        $messages = [];

        $order = $this->orderRepository->get($entity->getOrderId());
        $totalQuantity = 0;
        foreach ($entity->getItems() as $item) {
            $orderItem = $this->getOrderItemById($order, $item->getOrderItemId());
            if ($orderItem === null) {
                $messages[] = __('We can not found item "%1" in order.', $item->getOrderItemId());
                continue;
            }

            if (!$this->isQtyAvailable($orderItem, $item->getQty())) {
                $messages[] =__('We found an invalid quantity to ship for item "%1".', $item->getName());
            } else {
                $totalQuantity += $item->getQty();
            }
        }
        if ($totalQuantity <= 0) {
            $messages[] = __('You can\'t create a shipment without products.');
        }
        return $messages;
    }

    /**
     * @param OrderInterface $order
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderItemInterface|null
     */
    private function getOrderItemById(OrderInterface $order, $id)
    {
        foreach ($order->getItems() as $item) {
            if ($item->getItemId() === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param Item $orderItem
     * @param int $qty
     * @return bool
     */
    private function isQtyAvailable(Item $orderItem, $qty)
    {
        return $qty <= $orderItem->getQtyToShip() || $orderItem->isDummy(true);
    }
}
