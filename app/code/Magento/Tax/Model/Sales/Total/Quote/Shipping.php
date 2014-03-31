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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Sales\Model\Quote\Address;

class Shipping extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Tax calculation model
     *
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculator = null;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config = null;

    /**
     * Flag which is initialized when collect method is started and catalog prices include tax.
     * It is used for checking if store tax and customer tax requests are similar
     *
     * @var bool
     */
    protected $_areTaxRequestsSimilar = false;

    /**
     * Request which can be used for tax rate calculation
     *
     * @var \Magento\Object
     */
    protected $_storeTaxRequest = null;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(\Magento\Tax\Model\Calculation $calculation, \Magento\Tax\Model\Config $taxConfig)
    {
        $this->setCode('shipping');
        $this->_calculator = $calculation;
        $this->_config = $taxConfig;
    }

    /**
     * Collect totals information about shipping
     *
     * @param   Address $address
     * @return  $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $calc = $this->_calculator;
        $store = $address->getQuote()->getStore();
        $storeTaxRequest = $calc->getRateOriginRequest($store);
        $addressTaxRequest = $calc->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );

        $shippingTaxClass = $this->_config->getShippingTaxClass($store);
        $storeTaxRequest->setProductClassId($shippingTaxClass);
        $addressTaxRequest->setProductClassId($shippingTaxClass);

        $priceIncludesTax = $this->_config->shippingPriceIncludesTax($store);
        if ($priceIncludesTax) {
            $this->_areTaxRequestsSimilar = $calc->compareRequests($addressTaxRequest, $storeTaxRequest);
        }

        $shipping = $taxShipping = $address->getShippingAmount();
        $baseShipping = $baseTaxShipping = $address->getBaseShippingAmount();
        $rate = $calc->getRate($addressTaxRequest);
        if ($priceIncludesTax) {
            if ($this->_areTaxRequestsSimilar) {
                $tax = $this->_round($calc->calcTaxAmount($shipping, $rate, true, false), $rate, true);
                $baseTax = $this->_round($calc->calcTaxAmount($baseShipping, $rate, true, false), $rate, true, 'base');
                $taxShipping = $shipping;
                $baseTaxShipping = $baseShipping;
                $shipping = $shipping - $tax;
                $baseShipping = $baseShipping - $baseTax;
                $taxable = $taxShipping;
                $baseTaxable = $baseTaxShipping;
                $isPriceInclTax = true;
            } else {
                $storeRate = $calc->getStoreRate($addressTaxRequest, $store);
                $storeTax = $calc->calcTaxAmount($shipping, $storeRate, true, false);
                $baseStoreTax = $calc->calcTaxAmount($baseShipping, $storeRate, true, false);
                $shipping = $calc->round($shipping - $storeTax);
                $baseShipping = $calc->round($baseShipping - $baseStoreTax);
                $tax = $this->_round($calc->calcTaxAmount($shipping, $rate, false, false), $rate, false);
                $baseTax = $this->_round(
                    $calc->calcTaxAmount($baseShipping, $rate, false, false),
                    $rate,
                    false,
                    'base'
                );
                $taxShipping = $shipping + $tax;
                $baseTaxShipping = $baseShipping + $baseTax;
                $taxable = $shipping;
                $baseTaxable = $baseShipping;
                $isPriceInclTax = false;
            }
        } else {
            $tax = $this->_round($calc->calcTaxAmount($shipping, $rate, false, false), $rate, false);
            $baseTax = $this->_round($calc->calcTaxAmount($baseShipping, $rate, false, false), $rate, false, 'base');
            $taxShipping = $shipping + $tax;
            $baseTaxShipping = $baseShipping + $baseTax;
            $taxable = $shipping;
            $baseTaxable = $baseShipping;
            $isPriceInclTax = false;
        }
        $address->setTotalAmount('shipping', $shipping);
        $address->setBaseTotalAmount('shipping', $baseShipping);
        $address->setShippingInclTax($taxShipping);
        $address->setBaseShippingInclTax($baseTaxShipping);
        $address->setShippingTaxable($taxable);
        $address->setBaseShippingTaxable($baseTaxable);
        $address->setIsShippingInclTax($isPriceInclTax);
        if ($this->_config->discountTax($store)) {
            $address->setShippingAmountForDiscount($taxShipping);
            $address->setBaseShippingAmountForDiscount($baseTaxShipping);
        }
        return $this;
    }

    /**
     * Round price based on tax rounding settings
     *
     * @param float $price
     * @param string $rate
     * @param bool $direction
     * @param string $type
     * @return float
     */
    protected function _round($price, $rate, $direction, $type = 'regular')
    {
        if (!$price) {
            return $this->_calculator->round($price);
        }

        $deltas = $this->_address->getRoundingDeltas();
        $key = $type . $direction;
        $rate = (string)$rate;
        $delta = isset($deltas[$key][$rate]) ? $deltas[$key][$rate] : 0;
        return $this->_calculator->round($price + $delta);
    }
}
