<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;

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
     * @var SnakeToCamelCaseConverter
     */
    private $snakeToCamelCaseConverter;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SnakeToCamelCaseConverter $snakeToCamelCaseConverter
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SnakeToCamelCaseConverter $snakeToCamelCaseConverter,
        ValidationResultFactory $validationResultFactory
    ) {
        $this->objectManager = $objectManager;
        $this->snakeToCamelCaseConverter = $snakeToCamelCaseConverter;
        $this->validationResultFactory = $validationResultFactory;
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
        /** @var ValidationResult $validationResult */
        $validationResult = $this->validate();
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation error'), null, 0, $validationResult);
        }

        $data = [
            ReservationInterface::RESERVATION_ID => null,
            ReservationInterface::STOCK_ID => $this->stockId,
            ReservationInterface::SKU => $this->sku,
            ReservationInterface::QUANTITY => $this->quantity,
            ReservationInterface::METADATA => $this->metadata,
        ];

        $arguments = $this->convertArrayKeysFromSnakeToCamelCase($data);
        $reservation = $this->objectManager->create(ReservationInterface::class, $arguments);

        $this->reset();

        return $reservation;
    }

    /**
     * @return ValidationResult
     */
    private function validate()
    {
        $errors = [];

        if (null === $this->stockId) {
            $errors[] = __('"%field" is expected to be a number.', ['field' => ReservationInterface::STOCK_ID]);
        }

        if (null === $this->sku || '' === trim($this->sku)) {
            $errors[] = __('"%field" can not be empty.', ['field' => ReservationInterface::SKU]);
        }

        if (null === $this->quantity) {
            $errors[] = __('"%field" can not be null.', ['field' => ReservationInterface::QUANTITY]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * Used to clean state after object creation
     * @return void
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
    private function convertArrayKeysFromSnakeToCamelCase(array $array): array
    {
        $convertedArrayKeys = $this->snakeToCamelCaseConverter->convert(array_keys($array));
        return array_combine($convertedArrayKeys, array_values($array));
    }
}
