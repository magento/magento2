<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Formatter;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\OrderAddress;
use Magento\SalesGraphQl\Model\Order\OrderPayments;

/**
 * Format order model for graphql schema
 */
class Order
{
    /**
     * @var OrderAddress
     */
    private $orderAddress;

    /**
     * @var OrderPayments
     */
    private $orderPayments;

    /**
     * @param OrderAddress $orderAddress
     * @param OrderPayments $orderPayments
     */
    public function __construct(
        OrderAddress $orderAddress,
        OrderPayments $orderPayments
    ) {
        $this->orderAddress = $orderAddress;
        $this->orderPayments = $orderPayments;
    }

    /**
     * Format order model for graphql schema
     *
     * @param OrderInterface $orderModel
     * @return array
     */
    public function format(OrderInterface $orderModel): array
    {
        return [
            'created_at' => $orderModel->getCreatedAt(),
            'grand_total' => $orderModel->getGrandTotal(),
            'id' => base64_encode($orderModel->getEntityId()),
            'increment_id' => $orderModel->getIncrementId(),
            'number' => $orderModel->getIncrementId(),
            'order_date' => $orderModel->getCreatedAt(),
            'order_number' => $orderModel->getIncrementId(),
            'status' => $orderModel->getStatusLabel(),
            'shipping_method' => $orderModel->getShippingDescription(),
            'shipping_address' => $this->orderAddress->getOrderShippingAddress($orderModel),
            'billing_address' => $this->orderAddress->getOrderBillingAddress($orderModel),
            'payment_methods' => $this->orderPayments->getOrderPaymentMethod($orderModel),
            'model' => $orderModel,
        ];
    }
}
