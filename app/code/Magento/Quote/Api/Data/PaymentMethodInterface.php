<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface PaymentMethodInterface
 * @api
 * @since 100.0.2
 */
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
