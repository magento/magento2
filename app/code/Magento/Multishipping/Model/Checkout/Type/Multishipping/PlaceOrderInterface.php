<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place orders during multishipping checkout flow.
<<<<<<< HEAD
=======
 *
 * @api
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
