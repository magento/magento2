<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Coupon data for quote.
 *
 * @codeCoverageIgnore
 */
class Coupon extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Coupon code.
     */
    const COUPON_CODE = 'coupon_code';

    /**
     * Returns the coupon code.
     *
     * @return string Coupon code.
     */
    public function getCouponCode()
    {
        return $this->_get(self::COUPON_CODE);
    }
}
