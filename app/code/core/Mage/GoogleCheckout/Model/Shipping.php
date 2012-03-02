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
 * @category    Mage
 * @package     Mage_GoogleCheckout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Checkout shipping model
 *
 * @category   Mage
 * @package    Mage_GoogleCheckout
 */
class Mage_GoogleCheckout_Model_Shipping extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_code = 'googlecheckout';

    /**
     * Collects rates for user request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // dummy placeholder
        return $this;
    }

    /**
     * Returns array(methodCode => methodName) of possible methods for this carrier
     * Used to automatically show it in config and so on
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array();
    }

    /**
     * Returns array(methodCode => methodName) of internally used methods.
     * They are possible only as result of completing Google Checkout.
     *
     * @return array
     */
    public function getInternallyAllowedMethods()
    {
        return array(
            'carrier'  => 'Carrier',
            'merchant' => 'Merchant',
            'flatrate' => 'Flat Rate',
            'pickup'   => 'Pickup'
        );
    }
}
