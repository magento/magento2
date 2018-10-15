<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * DTO used as the type for values of `$items` array passed to PlaceReservationsForSalesEventInterface::execute()
 * @see \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface
 *
 * @api
 */
interface ItemToSellInterface extends ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return float
     */
    public function getQuantity(): float;

    /**
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku);

    /**
     * @param float $qty
     * @return void
     */
    public function setQuantity(float $qty);

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventorySalesApi\Api\Data\ItemToSellExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySalesApi\Api\Data\ItemToSellExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(ItemToSellExtensionInterface $extensionAttributes);
}
