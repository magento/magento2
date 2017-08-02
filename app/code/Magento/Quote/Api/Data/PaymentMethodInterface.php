<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface PaymentMethodInterface
 * @api
 * @since 2.0.0
 */
interface PaymentMethodInterface
{
    /**
     * Get payment method code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Get payment method title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle();
}
