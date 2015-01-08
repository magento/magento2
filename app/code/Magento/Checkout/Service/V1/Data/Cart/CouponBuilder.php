<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
