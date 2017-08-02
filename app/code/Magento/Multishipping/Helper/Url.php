<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Multi Shipping urls helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Multishipping\Helper;

/**
 * Class \Magento\Multishipping\Helper\Url
 *
 * @since 2.0.0
 */
class Url extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Retrieve shopping cart url
     *
     * @return string
     * @since 2.0.0
     */
    public function getCartUrl()
    {
        return $this->_getUrl('checkout/cart');
    }

    /**
     * Retrieve checkout url
     *
     * @return string
     * @since 2.0.0
     */
    public function getMSCheckoutUrl()
    {
        return $this->_getUrl('multishipping/checkout');
    }

    /**
     * Retrieve login url
     *
     * @return string
     * @since 2.0.0
     */
    public function getMSLoginUrl()
    {
        return $this->_getUrl('multishipping/checkout/login', ['_secure' => true, '_current' => true]);
    }

    /**
     * Retrieve address url
     *
     * @return string
     * @since 2.0.0
     */
    public function getMSAddressesUrl()
    {
        return $this->_getUrl('multishipping/checkout/addresses');
    }

    /**
     * Retrieve shipping address save url
     *
     * @return string
     * @since 2.0.0
     */
    public function getMSShippingAddressSavedUrl()
    {
        return $this->_getUrl('multishipping/checkout_address/shippingSaved');
    }

    /**
     * Retrieve register url
     *
     * @return string
     * @since 2.0.0
     */
    public function getMSRegisterUrl()
    {
        return $this->_getUrl('multishipping/checkout/register');
    }
}
