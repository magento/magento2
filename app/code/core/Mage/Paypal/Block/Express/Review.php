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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Paypal Express Onepage checkout block
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Block_Express_Review extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * Currently selected shipping rate
     *
     * @var Mage_Sales_Model_Quote_Address_Rate
     */
    protected $_currentShippingRate = null;

    /**
     * Paypal action prefix
     *
     * @var string
     */
    protected $_paypalActionPrefix = 'paypal';

    /**
     * Quote object setter
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Paypal_Block_Express_Review
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Return quote billing address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        return $this->_quote->getBillingAddress();
    }

    /**
     * Return quote shipping address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        if ($this->_quote->getIsVirtual()) {
            return false;
        }
        return $this->_quote->getShippingAddress();
    }

    /**
     * Get HTML output for specified address
     *
     * @param Mage_Sales_Model_Quote_Address
     * @return string
     */
    public function renderAddress($address)
    {
        return $address->getFormated(true);
    }

    /**
     * Return carrier name from config, base on carrier code
     *
     * @param $carrierCode string
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig("carriers/{$carrierCode}/title")) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Get either shipping rate code or empty value on error
     *
     * @param Varien_Object $rate
     * @return string
     */
    public function renderShippingRateValue(Varien_Object $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }
        return $rate->getCode();
    }

    /**
     * Get shipping rate code title and its price or error message
     *
     * @param Varien_Object $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return string
     */
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->_getShippingPrice(
                $rate->getPrice(),
                $this->helper('Mage_Tax_Helper_Data')->displayShippingPriceIncludingTax()
            );
            $incl = $this->_getShippingPrice($rate->getPrice(), true);
            if (($incl != $price) && $this->helper('Mage_Tax_Helper_Data')->displayShippingBothPrices()) {
                $renderedInclTax = sprintf(
                    $inclTaxFormat,
                    Mage::helper('Mage_Tax_Helper_Data')->__('Incl. Tax'),
                    $incl
                );
            }
        }
        return sprintf($format, $rate->getMethodTitle(), $price, $renderedInclTax);
    }

    /**
     * Getter for current shipping rate
     *
     * @return Mage_Sales_Model_Quote_Address_Rate
     */
    public function getCurrentShippingRate()
    {
        return $this->_currentShippingRate;
    }

    /**
     * Set paypal actions prefix
     */
    public function setPaypalActionPrefix($prefix)
    {
        $this->_paypalActionPrefix = $prefix;
    }

    /**
     * Return formatted shipping price
     *
     * @param float $price
     * @param bool $isInclTax
     *
     * @return bool
     */
    protected function _getShippingPrice($price, $isInclTax)
    {
        return $this->_formatPrice(
            $this->helper('Mage_Tax_Helper_Data')->getShippingPrice(
                $price,
                $isInclTax,
                $this->_address
            )
        );
    }

    /**
     * Format price base on store convert price method
     *
     * @param float $price
     * @return string
     */
    protected function _formatPrice($price)
    {
        return $this->_quote->getStore()->convertPrice($price, true);
    }

    /**
     * Retrieve payment method and assign additional template values
     *
     * @return Mage_Paypal_Block_Express_Review
     */
    protected function _beforeToHtml()
    {
        $methodInstance = $this->_quote->getPayment()->getMethodInstance();
        $this->setPaymentMethodTitle($methodInstance->getTitle());

        $this->setShippingRateRequired(true);
        if ($this->_quote->getIsVirtual()) {
            $this->setShippingRateRequired(false);
        } else {
            // prepare shipping rates
            $this->_address = $this->_quote->getShippingAddress();
            $groups = $this->_address->getGroupedAllShippingRates();
            if ($groups && $this->_address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($this->_address->getShippingMethod() == $rate->getCode()) {
                            $this->_currentShippingRate = $rate;
                            break(2);
                        }
                    }
                }
            }

            // misc shipping parameters
            $this->setShippingMethodSubmitUrl($this->getUrl("{$this->_paypalActionPrefix}/express/saveShippingMethod"))
                ->setCanEditShippingAddress($this->_quote->getMayEditShippingAddress())
                ->setCanEditShippingMethod($this->_quote->getMayEditShippingMethod())
            ;
        }

        $this->setEditUrl($this->getUrl("{$this->_paypalActionPrefix}/express/edit"))
            ->setPlaceOrderUrl($this->getUrl("{$this->_paypalActionPrefix}/express/placeOrder"));

        return parent::_beforeToHtml();
    }
}
