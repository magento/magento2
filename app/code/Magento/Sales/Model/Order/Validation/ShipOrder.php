<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\TrackValidator;
use Magento\Sales\Model\ValidatorResultMerger;

/**
 * Class ShipOrder
 * @since 2.2.0
 */
class ShipOrder implements ShipOrderInterface
{
    /**
     * @var OrderValidatorInterface
     * @since 2.2.0
     */
    private $orderValidator;

    /**
     * @var ShipmentValidatorInterface
     * @since 2.2.0
     */
    private $shipmentValidator;

    /**
     * @var ValidatorResultMerger
     * @since 2.2.0
     */
    private $validatorResultMerger;

    /**
     * ShipOrder constructor.
     *
     * @param OrderValidatorInterface $orderValidator
     * @param ShipmentValidatorInterface $shipmentValidator
     * @param ValidatorResultMerger $validatorResultMerger
     * @since 2.2.0
     */
    public function __construct(
        OrderValidatorInterface $orderValidator,
        ShipmentValidatorInterface $shipmentValidator,
        ValidatorResultMerger $validatorResultMerger
    ) {
        $this->orderValidator = $orderValidator;
        $this->shipmentValidator = $shipmentValidator;
        $this->validatorResultMerger = $validatorResultMerger;
    }

    /**
     * @param OrderInterface $order
     * @param ShipmentInterface $shipment
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationInterface|null $comment
     * @param array $tracks
     * @param array $packages
     * @param \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface|null $arguments
     * @return \Magento\Sales\Model\ValidatorResultInterface
     * @since 2.2.0
     */
    public function validate(
        $order,
        $shipment,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        array $tracks = [],
        array $packages = [],
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface $arguments = null
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

        return $this->validatorResultMerger->merge($orderValidationResult, $shipmentValidationResult);
    }
}
