<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipping extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * Available Carriers Instances
     * @var null|array
     */
    protected $carriers = null;

    /**
     * Estimate Rates
     * @var array
     */
    protected $rates = [];

    /**
     * Address Model
     *
     * @var array
     */
    protected $address = [];

    /**
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    protected $carrierFactory;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Directory\Model\Resource\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\Resource\Region\Collection
     */
    protected $regionCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param \Magento\Directory\Model\Resource\Country\Collection $countryCollection
     * @param \Magento\Directory\Model\Resource\Region\Collection $regionCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\Resource\Country\Collection $countryCollection,
        \Magento\Directory\Model\Resource\Region\Collection $regionCollection,
        array $data = []
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->configProvider = $configProvider;
        $this->merger = $merger;
        $this->countryCollection = $countryCollection;
        $this->regionCollection = $regionCollection;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get Estimate Rates
     *
     * @return array
     */
    public function getEstimateRates()
    {
        if (empty($this->rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            $this->rates = $groups;
        }
        return $this->rates;
    }

    /**
     * Get Address Model
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        if (empty($this->address)) {
            $this->address = $this->getQuote()->getShippingAddress();
        }
        return $this->address;
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
     * Obtain available carriers instances
     *
     * @return array
     */
    public function getCarriers()
    {
        if (null === $this->carriers) {
            $this->carriers = [];
            $this->getEstimateRates();
            foreach ($this->rates as $rateGroup) {
                if (!empty($rateGroup)) {
                    foreach ($rateGroup as $rate) {
                        $this->carriers[] = $this->carrierFactory->get($rate->getCarrier());
                    }
                }
            }
        }
        return $this->carriers;
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
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        $elements = [
            'city' => [
                'visible' => $this->getCityActive(),
                'formElement' => 'input',
                'label' => __('City'),
                'validation' => ['required-entry' => $this->isCityRequired()]
            ],
            'country_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('Country'),
                'options' => $this->countryCollection->load()->toOptionArray()
            ],
            'region_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('State/Province'),
                'options' => $this->regionCollection->load()->toOptionArray(),
                'validation' => ['required-entry' => $this->isStateProvinceRequired()]
            ],
            'postcode' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
                'validation' => ['required-entry' => $this->isZipCodeRequired()]
            ]
        ];

        if (isset($this->jsLayout['components']['block-summary']['children']['block-shipping']['children']
            ['address-fieldsets']['children'])
        ) {
            $fieldSetPointer = &$this->jsLayout['components']['block-summary']['children']['block-shipping']
            ['children']['address-fieldsets']['children'];
            $fieldSetPointer = $this->merger->merge($elements, 'checkoutProvider', 'shippingAddress', $fieldSetPointer);
        }
        return json_encode($this->jsLayout);
    }
}
