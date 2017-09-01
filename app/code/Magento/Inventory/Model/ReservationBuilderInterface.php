<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * Used to instantiate ReservationInterface objects
 *
 * @api
 * @see ReservationInterface
 */
interface ReservationBuilderInterface
{
    /**
     * @param int $reservationId
     * @return ReservationBuilder
     */
    public function setReservationId($reservationId): ReservationBuilder;

    /**
     * @param int $stockId
     * @return ReservationBuilder
     */
    public function setStockId(int $stockId): ReservationBuilder;

    /**
     * @param string $sku
     * @return ReservationBuilder
     */
    public function setSku(string $sku): ReservationBuilder;

    /**
     * @param float $quantity
     * @return ReservationBuilder
     */
    public function setQuantity(float $quantity): ReservationBuilder;

    /**
     * @param string $metadata
     * @return ReservationBuilder
     */
    public function setMetadata($metadata): ReservationBuilder;

    /**
     * @return ReservationInterface
     * @throws ValidationException
     */
    public function build(): ReservationInterface;
}
