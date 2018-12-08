<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
declare(strict_types=1);

namespace Magento\Sales\Api;

use Magento\Framework\Controller\Result\Redirect;

/**
 * Delegate related to orders customers operations to Customer module.
 */
interface OrderCustomerDelegateInterface
{
    /**
<<<<<<< HEAD
     * Redirect to Customer module new-account page to finish creating
     * customer based on order data.
=======
     * Redirect to Customer module new-account page to finish creating customer based on order data.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param int $orderId
     *
     * @return Redirect
     */
    public function delegateNew(int $orderId): Redirect;
}
