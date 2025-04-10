<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator;

use Magento\OrderCancellation\Model\Config\Config;
use Magento\Sales\Model\Order;

/**
 * Validate cancellation reason of order
 */
class ValidateOrderCancellationReason
{
    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * ValidateOrderCancellationReason Constructor
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Validate cancellation reason
     *
     * @param Order $order
     * @param string $reason
     * @return bool
     */
    public function validateReason(
        Order $order,
        string $reason
    ): bool {
        $cancellationReasons = array_map(
            'strtolower',
            $this->config->getCancellationReasons($order->getStore())
        );

        return !in_array(strtolower(trim($reason)), $cancellationReasons);
    }
}
