<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place orders during multishipping checkout flow.
 *
 * @api
 * @since 100.2.1
 */
interface PlaceOrderInterface
{
    /**
     * Place orders.
     *
     * @param OrderInterface[] $orderList
     * @return array
     * @since 100.2.1
     */
    public function place(array $orderList): array;
}
