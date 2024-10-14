<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Multi Shipping urls helper
 */
namespace Magento\Multishipping\Helper;

class Url extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Retrieve shopping cart url
     *
     * @return string
     */
    public function getCartUrl()
    {
        return $this->_getUrl('checkout/cart');
    }

    /**
     * Retrieve checkout url
     *
     * @return string
     */
    public function getMSCheckoutUrl()
    {
        return $this->_getUrl('multishipping/checkout');
    }

    /**
     * Retrieve login url
     *
     * @return string
     */
    public function getMSLoginUrl()
    {
        return $this->_getUrl('multishipping/checkout/login', ['_secure' => true, '_current' => true]);
    }

    /**
     * Retrieve address url
     *
     * @return string
     */
    public function getMSAddressesUrl()
    {
        return $this->_getUrl('multishipping/checkout/addresses');
    }

    /**
     * Retrieve shipping address save url
     *
     * @return string
     */
    public function getMSShippingAddressSavedUrl()
    {
        return $this->_getUrl('multishipping/checkout_address/shippingSaved');
    }

    /**
     * Retrieve register url
     *
     * @return string
     */
    public function getMSNewShippingUrl()
    {
        return $this->_getUrl('multishipping/checkout_address/newShipping');
    }

    /**
     * Retrieve register url
     *
     * @return string
     */
    public function getMSRegisterUrl()
    {
        return $this->_getUrl('multishipping/checkout/register');
    }
}
