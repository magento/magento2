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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Block\Cart;

class Shipping extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * Available Carriers Instances
     * @var null|array
     */
    protected $_carriers = null;

    /**
     * Estimate Rates
     * @var array
     */
    protected $_rates = array();

    /**
     * Address Model
     *
     * @var array
     */
    protected $_address = array();

    /**
     * @var \Magento\Directory\Block\Data
     */
    protected $_directoryBlock;

    /**
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Block\Data $directoryBlock
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Block\Data $directoryBlock,
        array $data = array()
    ) {
        $this->_directoryBlock = $directoryBlock;
        parent::__construct($catalogData, $coreData, $context, $customerSession, $checkoutSession, $data);
    }

    /**
     * Get config
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_storeConfig->getConfig($path);
    }

    /**
     * @return \Magento\Directory\Block\Data
     */
    public function getDirectoryBlock()
    {
        return $this->_directoryBlock;
    }

    /**
     * Get Estimate Rates
     *
     * @return array
     */
    public function getEstimateRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            $this->_rates = $groups;
        }
        return $this->_rates;
    }

    /**
     * Get Address Model
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    /**
     * Get Carrier Name
     *
     * @param string $carrierCode
     * @return mixed
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_storeConfig->getConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Get Shipping Method
     *
     * @return string
     */
    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * Get Estimate Country Id
     *
     * @return string
     */
    public function getEstimateCountryId()
    {
        return $this->getAddress()->getCountryId();
    }

    /**
     * Get Estimate Postcode
     *
     * @return string
     */
    public function getEstimatePostcode()
    {
        return $this->getAddress()->getPostcode();
    }

    /**
     * Get Estimate City
     *
     * @return string
     */
    public function getEstimateCity()
    {
        return $this->getAddress()->getCity();
    }

    /**
     * Get Estimate Region Id
     *
     * @return mixed
     */
    public function getEstimateRegionId()
    {
        return $this->getAddress()->getRegionId();
    }

    /**
     * Get Estimate Region
     *
     * @return string
     */
    public function getEstimateRegion()
    {
        return $this->getAddress()->getRegion();
    }

    /**
     * Show City in Shipping Estimation
     *
     * @return bool
     */
    public function getCityActive()
    {
        return (bool)$this->_storeConfig->getConfig('carriers/dhl/active')
            || (bool)$this->_storeConfig->getConfig('carriers/dhlint/active');
    }

    /**
     * Show State in Shipping Estimation
     *
     * @return bool
     */
    public function getStateActive()
    {
        return (bool)$this->_storeConfig->getConfig('carriers/dhl/active')
            || (bool)$this->_storeConfig->getConfig('carriers/tablerate/active')
            || (bool)$this->_storeConfig->getConfig('carriers/dhlint/active');
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @return float
     */
    public function formatPrice($price)
    {
        return $this->getQuote()->getStore()->convertPrice($price, true);
    }

    /**
     * Get Shipping Price
     *
     * @param float $price
     * @param bool $flag
     * @return float
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->formatPrice($this->helper('Magento\Tax\Helper\Data')->getShippingPrice(
            $price,
            $flag,
            $this->getAddress(),
            $this->getQuote()->getCustomerTaxClassId()
        ));
    }

    /**
     * Obtain available carriers instances
     *
     * @return array
     */
    public function getCarriers()
    {
        if (null === $this->_carriers) {
            $this->_carriers = array();
            $this->getEstimateRates();
            foreach ($this->_rates as $rateGroup) {
                if (!empty($rateGroup)) {
                    foreach ($rateGroup as $rate) {
                        $this->_carriers[] = $rate->getCarrierInstance();
                    }
                }
            }
        }
        return $this->_carriers;
    }

    /**
     * Check if one of carriers require state/province
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        foreach ($this->getCarriers() as $carrier) {
            if ($carrier->isStateProvinceRequired()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if one of carriers require city
     *
     * @return bool
     */
    public function isCityRequired()
    {
        foreach ($this->getCarriers() as $carrier) {
            if ($carrier->isCityRequired()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if one of carriers require zip code
     *
     * @return bool
     */
    public function isZipCodeRequired()
    {
        foreach ($this->getCarriers() as $carrier) {
            if ($carrier->isZipCodeRequired($this->getEstimateCountryId())) {
                return true;
            }
        }
        return false;
    }
}
