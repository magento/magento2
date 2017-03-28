<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api\Data;

/**
 * Base line item interface for order entities
 *
 * Interface LineItemInterface
 * @api
 */
interface LineItemInterface
{
    /**
     * Gets the order item ID for the item.
     *
     * @return int Order item ID.
     */
    public function getOrderItemId();

    /**
     * Sets the order item ID for the item.
     *
     * @param int $id
     * @return $this
     */
    public function setOrderItemId($id);

    /**
     * Gets the quantity for the item.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Sets the quantity for the item.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
}
