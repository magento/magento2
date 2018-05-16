<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationsApi\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryReservationsApi\Model\ReservationInterface;

/**
 * Used to build ReservationInterface objects
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
    public function setStockId(int $stockId): self;

    /**
     * @param string $sku
     * @return self
     */
    public function setSku(string $sku): self;

    /**
     * @param float $quantity
     * @return self
     */
    public function setQuantity(float $quantity): self;

    /**
     * @param string|null $metadata
     * @return self
     */
    public function setMetadata(string $metadata = null): self;

    /**
     * @return ReservationInterface
     * @throws ValidationException
     */
    public function build(): ReservationInterface;
}
