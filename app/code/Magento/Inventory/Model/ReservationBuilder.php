<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * Used to instantiate ReservationInterface objects
 */
class ReservationBuilder implements ReservationBuilderInterface
{
    /**
     * @var int
     */
    private $reservationId;

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
    protected $_objectManager = null;

    /**
     * ReservationBuilder constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function setReservationId($reservationId): ReservationBuilder
    {
        $this->reservationId = $reservationId;
        return $this;
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
        $data = [
            ReservationInterface::RESERVATION_ID => $this->reservationId,
            ReservationInterface::STOCK_ID => $this->stockId,
            ReservationInterface::SKU => $this->sku,
            ReservationInterface::QUANTITY => $this->quantity,
            ReservationInterface::METADATA => $this->metadata,
        ];

        $arguments = [
            'data' => $data,
        ];

        return $this->_objectManager->create(ReservationInterface::class, $arguments);
    }
}
