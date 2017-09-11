<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Reservation;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\Reservation\Validator\ReservationValidatorInterface;
use Magento\Inventory\Model\SnakeToCamelCaseConverter;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;

/**
 * @inheritdoc
 */
class ReservationBuilder implements ReservationBuilderInterface
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $metadata;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ReservationValidatorInterface
     */
    private $reservationValidator;

    /**
     * @var SnakeToCamelCaseConverter
     */
    private $snakeToCamelCaseConverter;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ReservationValidatorInterface $reservationValidator
     * @param SnakeToCamelCaseConverter $snakeToCamelCaseConverter
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ReservationValidatorInterface $reservationValidator,
        SnakeToCamelCaseConverter $snakeToCamelCaseConverter
    ) {
        $this->objectManager = $objectManager;
        $this->reservationValidator = $reservationValidator;
        $this->snakeToCamelCaseConverter = $snakeToCamelCaseConverter;
    }

    /**
     * @inheritdoc
     */
    public function setStockId(int $stockId): ReservationBuilderInterface
    {
        $this->stockId = $stockId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSku(string $sku): ReservationBuilderInterface
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(float $quantity): ReservationBuilderInterface
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMetadata(string $metadata = null): ReservationBuilderInterface
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function build(): ReservationInterface
    {
        $data = [
            ReservationInterface::STOCK_ID => $this->stockId,
            ReservationInterface::SKU => $this->sku,
            ReservationInterface::QUANTITY => $this->quantity,
            ReservationInterface::METADATA => $this->metadata,
        ];

        $arguments = $this->convertArrayKeysFromSnakeToCamelCase($data);
        $reservation = $this->objectManager->create(ReservationInterface::class, $arguments);
        $this->reset();

        $validationResult = $this->reservationValidator->validate($reservation);
        if (!$validationResult->isValid()) {
            throw new ValidationException($validationResult);
        }
        return $reservation;
    }

    /**
     * Used to clean state after object creation.
     */
    private function reset()
    {
        $this->stockId = null;
        $this->sku = null;
        $this->quantity = null;
        $this->metadata = null;
    }

    /**
     * Used to convert database field names (that use snake case) into constructor parameter names (that use camel case)
     * to avoid to define them twice in domain model interface.
     *
     * @param array $array
     * @return array
     */
    private function convertArrayKeysFromSnakeToCamelCase(array $array)
    {
        $convertedArrayKeys = $this->snakeToCamelCaseConverter->convert(array_keys($array));
        return array_combine($convertedArrayKeys, array_values($array));
    }
}
