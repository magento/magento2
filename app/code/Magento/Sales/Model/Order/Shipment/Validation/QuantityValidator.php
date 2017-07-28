<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment\Validation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class QuantityValidator
 * @since 2.2.0
 */
class QuantityValidator implements ValidatorInterface
{
    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * InvoiceValidator constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @since 2.2.0
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param ShipmentInterface $entity
     * @return array
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     * @since 2.2.0
     */
    public function validate($entity)
    {
        if ($entity->getOrderId() === null) {
            return [__('Order Id is required for shipment document')];
        }

        if (empty($entity->getItems())) {
            return [__('You can\'t create a shipment without products.')];
        }
        $messages = [];

        $order = $this->orderRepository->get($entity->getOrderId());
        $orderItemsById = $this->getOrderItems($order);

        $totalQuantity = 0;
        foreach ($entity->getItems() as $item) {
            if (!isset($orderItemsById[$item->getOrderItemId()])) {
                $messages[] = __(
                    'The shipment contains product SKU "%1" that is not part of the original order.',
                    $item->getSku()
                );
                continue;
            }
            $orderItem = $orderItemsById[$item->getOrderItemId()];

            if (!$this->isQtyAvailable($orderItem, $item->getQty())) {
                $messages[] =__(
                    'The quantity to ship must not be greater than the unshipped quantity'
                    . ' for product SKU "%1".',
                    $orderItem->getSku()
                );
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
     * @return OrderItemInterface[]
     * @since 2.2.0
     */
    private function getOrderItems(OrderInterface $order)
    {
        $orderItemsById = [];
        foreach ($order->getItems() as $item) {
            $orderItemsById[$item->getItemId()] = $item;
        }

        return $orderItemsById;
    }

    /**
     * @param Item $orderItem
     * @param int $qty
     * @return bool
     * @since 2.2.0
     */
    private function isQtyAvailable(Item $orderItem, $qty)
    {
        return $qty <= $orderItem->getQtyToShip() || $orderItem->isDummy(true);
    }
}
