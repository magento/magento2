<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Zend\Filter\Word\UnderscoreToCamelCase;

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
    private $objectManager = null;

    /**
     * @var \Magento\Inventory\Model\ReservationBuilder\Validator\ReservationBuilderValidatorInterface
     */
    private $reservationBuilderValidator;

    /**
     * String Service instance
     *
     * @var \Magento\Inventory\Model\String\Service
     */
    private $stringService;

    /**
     * ReservationBuilder constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Inventory\Model\ReservationBuilder\Validator\ReservationBuilderValidatorInterface $reservationBuilderValidator,
        \Magento\Inventory\Model\String\Service $stringService
    ) {
        $this->objectManager = $objectManager;
        $this->reservationBuilderValidator = $reservationBuilderValidator;
        $this->stringService = $stringService;
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
     * @return int|null
     */
    public function getStockId()
    {
        return $this->stockId;
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
     * @return string|null
     */
    public function getSku()
    {
        return $this->sku;
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
     * @return float|null
     */
    public function getQuantity()
    {
        return $this->quantity;
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

        $arguments = $this->stringService->convertArrayKeysFromSnakeToCamelCase($data);
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
}
