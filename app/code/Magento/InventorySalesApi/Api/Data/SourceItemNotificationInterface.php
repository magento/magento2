<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySalesApi\Api\Data;

/**
 * Represents amount of product on physical storage
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemNotificationInterface
{
    /**
     * Constant for fields in data array.
     */
    const INVENTORY_NOTIFY_QTY = 'notify_quantity';

    /**
     * Get source item id.
     *
     * @return int
     */
    public function getSourceItemId(): int;

    /**
     * Set source item id.
     *
     * @param $itemId
     */
    public function setSourceItemId(int $itemId);

    /**
     * Get the notification quantity.
     *
     * @return float|null
     */
    public function getNotifyQuantity(): float;

    /**
     * Set notification quantity.
     *
     * @param float|null $quantity
     * @return void
     */
    public function setNotifyQuantity($quantity);
}