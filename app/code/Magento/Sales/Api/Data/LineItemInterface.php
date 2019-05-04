<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api\Data;

/**
 * Base line item interface for order entities
 *
 * Interface LineItemInterface
 * @api
 * @since 100.1.2
 */
interface LineItemInterface
{
    /**
     * Gets the order item ID for the item.
     *
     * @return int Order item ID.
     * @since 100.1.2
     */
    public function getOrderItemId();

    /**
     * Sets the order item ID for the item.
     *
     * @param int $id
     * @return $this
     * @since 100.1.2
     */
    public function setOrderItemId($id);

    /**
     * Gets the quantity for the item.
     *
     * @return float Quantity.
     * @since 100.1.2
     */
    public function getQty();

    /**
     * Sets the quantity for the item.
     *
     * @param float $qty
     * @return $this
     * @since 100.1.2
     */
    public function setQty($qty);
}
