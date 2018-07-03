<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

/**
 * Represents the sales event that brings to appending reservations.
 *
 * @api
 */
interface SalesEventInterface
{
    /**#@+
     * Constants for event types
     */
    const EVENT_ORDER_PLACED = 'order_placed';
    const EVENT_ORDER_CANCELED = 'order_canceled';
    const EVENT_SHIPMENT_CREATED = 'shipment_created';
    const EVENT_CREDITMEMO_CREATED = 'creditmemo_created';
    const EVENT_INVOICE_CREATED = 'invoice_created';
    /**#@-*/

    /**#@+
     * Constants for event object types
     */
    const OBJECT_TYPE_ORDER = 'order';
    /**#@-*/

    public function getType(): string;

    public function getObjectType(): string;

    public function getObjectId(): string;

    /**
     * Convert this object to an associative array whose keys represent object properties.
     * This method is used to facilitate object serialization.
     */
    public function toArray(): array;
}
