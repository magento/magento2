<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param int $orderId
     *
     * @return Redirect
     */
    public function delegateNew(int $orderId): Redirect;
}
