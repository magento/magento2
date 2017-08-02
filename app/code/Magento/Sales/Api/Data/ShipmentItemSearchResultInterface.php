<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment item search result interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. A product is an item in a shipment.
 * @api
 * @since 2.0.0
 */
interface ShipmentItemSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[] Array of collection items.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
