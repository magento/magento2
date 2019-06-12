<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Api;

use Magento\Framework\Controller\Result\Redirect;

/**
 * Delegate related to orders customers operations to Customer module.
 */
interface OrderCustomerDelegateInterface
{
    /**
     * Redirect to Customer module new-account page to finish creating customer based on order data.
     *
     * @param int $orderId
     *
     * @return Redirect
     */
    public function delegateNew(int $orderId): Redirect;
}
