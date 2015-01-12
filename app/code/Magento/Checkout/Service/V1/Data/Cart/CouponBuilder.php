<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * @codeCoverageIgnore
 */
class CouponBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param string $value
     * @return $this
     */
    public function setCouponCode($value)
    {
        $this->_set(Coupon::COUPON_CODE, $value);
        return $this;
    }
}
