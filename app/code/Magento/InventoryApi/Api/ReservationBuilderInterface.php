<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\Framework\Validation\ValidationException;
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
     * @param int $stockId
     * @return self
     */
    public function setStockId(int $stockId): ReservationBuilderInterface;

    /**
     * @param string $sku
     * @return self
     */
    public function setSku(string $sku): ReservationBuilderInterface;

    /**
     * @param float $quantity
     * @return self
     */
    public function setQuantity(float $quantity): ReservationBuilderInterface;

    /**
     * @param string $metadata
     * @return self
     */
    public function setMetadata($metadata): ReservationBuilderInterface;

    /**
     * @return ReservationInterface
     * @throws ValidationException
     */
    public function build(): ReservationInterface;
}
