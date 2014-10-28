<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multi Shipping urls helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Multishipping\Helper;

class Url extends \Magento\Core\Helper\Url
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
        return $this->_getUrl('multishipping/checkout/login', array('_secure' => true, '_current' => true));
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
    public function getMSRegisterUrl()
    {
        return $this->_getUrl('multishipping/checkout/register');
    }
}
