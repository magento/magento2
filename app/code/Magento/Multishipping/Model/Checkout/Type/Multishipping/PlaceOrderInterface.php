<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place orders during multishipping checkout flow.
<<<<<<< HEAD
 *
 * @api
=======
>>>>>>> upstream/2.2-develop
 */
interface PlaceOrderInterface
{
    /**
     * Place orders.
     *
     * @param OrderInterface[] $orderList
     * @return array
     */
    public function place(array $orderList): array;
}
