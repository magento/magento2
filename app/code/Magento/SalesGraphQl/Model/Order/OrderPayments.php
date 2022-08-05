<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to get the order payment details
 */
class OrderPayments
{
    /**
     * Get the order payment method
     *
     * @param OrderInterface $orderModel
     * @return array
     */
    public function getOrderPaymentMethod(OrderInterface $orderModel): array
    {
        $orderPayment = $orderModel->getPayment();
        if (!$orderPayment) {
            return [];
        }
        return [
            [
                'name' => $orderPayment->getAdditionalInformation()['method_title'] ?? '',
                'type' => $orderPayment->getMethod(),
                'additional_data' => []
            ]
        ];
    }
}
