<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Orders;

use Magento\Sales\Model\Order;

/**
 * Discounts applied to the order
 */
class GetDiscounts
{
    /**
     * @param $orderModel
     * @return array|null
     */
    public function execute($orderModel)
    {
        return $this->getDiscountDetails($orderModel);
    }

    /**
     * Returns information about an applied discount
     *
     * @param Order $order
     * @return array|null
     */
    private function getDiscountDetails(Order $order)
    {

        $discounts [] = [
            'label' =>  $order->getDiscountDescription() ?? "null",
            'amount' => ['value' => $order->getDiscountAmount(), 'currency' => $order->getOrderCurrencyCode()]
        ];
        return $discounts;
    }
}
