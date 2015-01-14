<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

interface PaymentMethodInterface
{
    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getTitle();
}
