<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;

/**
 * Used to instantiate ReservationInterface objects
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
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Inventory\Model\ReservationBuilder\Validator\ReservationBuilderValidatorInterface
     */
    private $reservationBuilderValidator;

    /**
     * @var \Magento\Inventory\Model\SnakeToCamelCaseConvertor
     */
    private $snakeToCamelCaseConvertor;

    /**
     * ReservationBuilder constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Inventory\Model\ReservationBuilder\Validator\ReservationBuilderValidatorInterface $reservationBuilderValidator,
        \Magento\Inventory\Model\SnakeToCamelCaseConvertor $snakeToCamelCaseConvertor
    ) {
        $this->objectManager = $objectManager;
        $this->reservationBuilderValidator = $reservationBuilderValidator;
        $this->snakeToCamelCaseConvertor = $snakeToCamelCaseConvertor;
    }

    /**
     * @inheritdoc
     */
    public function setStockId(int $stockId): ReservationBuilder
    {
        $this->stockId = $stockId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSku(string $sku): ReservationBuilder
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(float $quantity): ReservationBuilder
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMetadata($metadata): ReservationBuilder
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function build(): ReservationInterface
    {
        $validationResult = $this->reservationBuilderValidator->validate($this);

        if (!$validationResult->isValid()) {
            throw new ValidationException($validationResult);
        }

        $data = [
            ReservationInterface::STOCK_ID => $this->stockId,
            ReservationInterface::SKU => $this->sku,
            ReservationInterface::QUANTITY => $this->quantity,
            ReservationInterface::METADATA => $this->metadata,
        ];

        $arguments = $this->convertArrayKeysFromSnakeToCamelCase($data);
        $reservationInstance = $this->objectManager->create(ReservationInterface::class, $arguments);
        $this->reset();
        return $reservationInstance;
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
        $convertedArrayKeys = $this->snakeToCamelCaseConvertor->convert(array_keys($array));
        return array_combine($convertedArrayKeys, array_values($array));
    }
}
