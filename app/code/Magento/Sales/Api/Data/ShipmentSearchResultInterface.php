<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Shipment search result interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package.
 * @api
 */
interface ShipmentSearchResultInterface extends SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
