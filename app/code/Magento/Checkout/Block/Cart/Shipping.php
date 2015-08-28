<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    protected $_rates = [];

    /**
     * Address Model
     *
     * @var array
     */
    protected $_address = [];

    /**
     * @var \Magento\Directory\Block\Data
     */
    protected $_directoryBlock;

    /**
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    protected $_carrierFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var EstimateAddressInterfaceFactory
     */
    protected $estimatedAddressFactory;

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManager;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\Cart\CollectQuote
     */
    protected $collectQuote;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Block\Data $directoryBlock
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param EstimateAddressInterfaceFactory $estimatedAddressFactory
     * @param ShippingMethodManagementInterface $shippingMethodManager
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Checkout\Model\Cart\CollectQuote $collectQuote
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        PriceCurrencyInterface $priceCurrency,
        EstimateAddressInterfaceFactory $estimatedAddressFactory,
        ShippingMethodManagementInterface $shippingMethodManager,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        QuoteRepository $quoteRepository,
        \Magento\Checkout\Model\Cart\CollectQuote $collectQuote,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_directoryBlock = $directoryBlock;
        $this->_carrierFactory = $carrierFactory;
        $this->estimatedAddressFactory = $estimatedAddressFactory;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->collectQuote = $collectQuote;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
    }

    /** Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
     * @return \Magento\Quote\Model\Quote\Address
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
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCityActive()
    {
        return false;
    }

    /**
     * Show State in Shipping Estimation. Result updated using plugins
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStateActive()
    {
        return false;
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @return float
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * Obtain available carriers instances
     *
     * @return array
     */
    public function getCarriers()
    {
        if (null === $this->_carriers) {
            $this->_carriers = [];
            $this->getEstimateRates();
            foreach ($this->_rates as $rateGroup) {
                if (!empty($rateGroup)) {
                    foreach ($rateGroup as $rate) {
                        $this->_carriers[] = $this->_carrierFactory->get($rate->getCarrier());
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

    /**
     * Get shipping price html
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $shippingRate
     * @return string
     */
    public function getShippingPriceHtml(\Magento\Quote\Model\Quote\Address\Rate $shippingRate)
    {
        /** @var \Magento\Checkout\Block\Shipping\Price $block */
        $block = $this->getLayout()->getBlock('checkout.shipping.price');
        $block->setShippingRate($shippingRate);
        return $block->toHtml();
    }

    /**
     * {@inheritDoc}
     */
    protected function _beforeToHtml()
    {
        $this->collectQuote->collect($this->getQuote());
        return parent::_beforeToHtml();
    }

    /**
     * Check if multiple countries for shipping is allowed
     *
     * @return bool
     */
    public function isMultipleCountriesAllowed()
    {
        $collection = $this->_directoryBlock->getCountryCollection();
        return $collection->count() > 1 ? true : false;
    }
}
