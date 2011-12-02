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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart api
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Checkout_Model_Cart_Shipping_Api extends Mage_Checkout_Model_Api_Resource
{
    public function __construct()
    {
        $this->_ignoredAttributeCodes['quote_shipping_rate'] = array('address_id', 'created_at', 'updated_at', 'rate_id', 'carrier_sort_order');
    }

    /**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    public function setShippingMethod($quoteId, $shippingMethod, $store = null)
    {
        $quote = $this->_getQuote($quoteId, $store);

        $quoteShippingAddress = $quote->getShippingAddress();
        if(is_null($quoteShippingAddress->getId()) ) {
            $this->_fault("shipping_address_is_not_set");
        }

        $rate = $quote->getShippingAddress()->collectShippingRates()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            $this->_fault('shipping_method_is_not_available');
        }

        try {
            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            $quote->collectTotals()->save();
        } catch(Mage_Core_Exception $e) {
            $this->_fault('shipping_method_is_not_set', $e->getMessage());
        }

        return true;
    }

    /**
     * Get list of available shipping methods
     *
     * @param  $quoteId
     * @param  $store
     * @return array
     */
    public function getShippingMethodsList($quoteId, $store=null)
    {
        $quote = $this->_getQuote($quoteId, $store);

        $quoteShippingAddress = $quote->getShippingAddress();
        if (is_null($quoteShippingAddress->getId())) {
            $this->_fault("shipping_address_is_not_set");
        }

        try {
            $quoteShippingAddress->collectShippingRates()->save();
            $groupedRates = $quoteShippingAddress->getGroupedAllShippingRates();

            $ratesResult = array();
            foreach ($groupedRates as $carrierCode => $rates ) {
                $carrierName = $carrierCode;
                if (!is_null(Mage::getStoreConfig('carriers/'.$carrierCode.'/title'))) {
                    $carrierName = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
                }

                foreach ($rates as $rate) {
                    $rateItem = $this->_getAttributes($rate, "quote_shipping_rate");
                    $rateItem['carrierName'] = $carrierName;
                    $ratesResult[] = $rateItem;
                    unset($rateItem);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('shipping_methods_list_could_not_be_retrived', $e->getMessage());
        }

        return $ratesResult;
    }


}
