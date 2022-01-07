<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Model\Order\Shipment\ShipmentItemsValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\TrackValidator;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\ValidatorResultMerger;

/**
 * Ship order validation class
 */
class ShipOrder implements ShipOrderInterface
{
    /**
     * @var OrderValidatorInterface
     */
    private $orderValidator;

    /**
     * @var ShipmentValidatorInterface
     */
    private $shipmentValidator;

    /**
     * @var ValidatorResultMerger
     */
    private $validatorResultMerger;

    /**
     * @var ShipmentItemsValidatorInterface
     */
    private $itemsValidator;

    /**
     * @param OrderValidatorInterface $orderValidator
     * @param ShipmentValidatorInterface $shipmentValidator
     * @param ValidatorResultMerger $validatorResultMerger
     * @param ShipmentItemsValidatorInterface|null $itemsValidator
     */
    public function __construct(
        OrderValidatorInterface $orderValidator,
        ShipmentValidatorInterface $shipmentValidator,
        ValidatorResultMerger $validatorResultMerger,
        ShipmentItemsValidatorInterface $itemsValidator = null
    ) {
        $this->orderValidator = $orderValidator;
        $this->shipmentValidator = $shipmentValidator;
        $this->validatorResultMerger = $validatorResultMerger;
        $this->itemsValidator = $itemsValidator
            ?? ObjectManager::getInstance()->get(ShipmentItemsValidatorInterface::class);
    }

    /**
     * Order shipment validate
     *
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param ShipmentCommentCreationInterface|null $comment
     * @param array $tracks
     * @param array $packages
     * @param ShipmentCreationArgumentsInterface|null $arguments
     * @return ValidatorResultInterface
     */
    public function validate(
        $order,
        $shipment,
        array $items = [],
        $notify = false,
        $appendComment = false,
        ShipmentCommentCreationInterface $comment = null,
        array $tracks = [],
        array $packages = [],
        ShipmentCreationArgumentsInterface $arguments = null
    ) {
        $orderValidationResult = $this->orderValidator->validate(
            $order,
            [
                CanShip::class
            ]
        );
        $shipmentValidationResult = $this->shipmentValidator->validate(
            $shipment,
            [
                QuantityValidator::class,
                TrackValidator::class
            ]
        );

        $orderItems = $this->getRequestedOrderItems($items, $order);
        $itemsValidationResult = $this->itemsValidator->validate($orderItems);

        return $this->validatorResultMerger->merge(
            $orderValidationResult,
            $shipmentValidationResult,
            $itemsValidationResult->getMessages()
        );
    }

    /**
     * Return requested order items
     *
     * @param OrderItemInterface[] $items
     * @param OrderInterface $order
     * @return OrderItemInterface[]
     */
    private function getRequestedOrderItems(array $items, OrderInterface $order): array
    {
        $requestedItemIds = array_reduce(
            $items,
            function (array $result, ShipmentItemCreationInterface $item): array {
                $result[] = $item->getOrderItemId();
                return $result;
            },
            []
        );

        return array_filter(
            $order->getAllItems(),
            function (OrderItemInterface $orderItem) use ($requestedItemIds): bool {
                return in_array($orderItem->getId(), $requestedItemIds);
            }
        );
    }
}
