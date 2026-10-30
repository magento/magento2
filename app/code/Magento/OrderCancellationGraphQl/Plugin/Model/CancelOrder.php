<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Plugin\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\OrderCancellationGraphQl\Model\Validator\ValidateOrderCancellationReason;
use Magento\Sales\Model\Order;
use Magento\OrderCancellation\Model\CancelOrder as Subject;

/**
 * Plugin for cancel order model
 */
class CancelOrder
{
    /**
     * @var ValidateOrderCancellationReason $validateOrderCancellationReason
     */
    private ValidateOrderCancellationReason $validateOrderCancellationReason;

    /**
     * @param ValidateOrderCancellationReason $validateOrderCancellationReason
     */
    public function __construct(
        ValidateOrderCancellationReason $validateOrderCancellationReason
    ) {
        $this->validateOrderCancellationReason = $validateOrderCancellationReason;
    }

    /**
     * Before plugin for reason validation
     *
     * @param Subject $subject
     * @param Order $order
     * @param string $reason
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        Subject $subject,
        Order $order,
        string $reason
    ) {
        if (!empty($reason)) {
            if ($this->validateOrderCancellationReason->validateReason($order, $reason)) {
                throw new GraphQlInputException(__('Order cancellation reason is invalid.'));
            }
        }

        return [$order, $reason];
    }
}
