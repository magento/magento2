<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
