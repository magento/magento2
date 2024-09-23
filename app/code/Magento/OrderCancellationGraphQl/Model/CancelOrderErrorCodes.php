<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model;

use Magento\Sales\Model\Order;

/**
 * Identify error code based on the error message
 */
class CancelOrderErrorCodes
{
    private const MESSAGE_CODES_MAPPER = [
        "order cancellation is not enabled for requested store." => 'ORDER_CANCELLATION_DISABLED',
        "current user is not authorized to cancel this order" => 'UNAUTHORISED',
        "the entity that was requested doesn't exist. verify the entity and try again." => 'ORDER_NOT_FOUND',
        "order with one or more items shipped cannot be cancelled" => 'PARTIAL_ORDER_ITEM_SHIPPED',
        "order already closed, complete, cancelled or on hold" => 'INVALID_ORDER_STATUS',
    ];

    /**
     * Get message error code.
     *
     * @param string $messageCode
     * @return string
     */
    public function getErrorCodeFromMapper(string $messageCode): string
    {
        return self::MESSAGE_CODES_MAPPER[strtolower($messageCode)] ?? 'UNDEFINED';
    }
}
