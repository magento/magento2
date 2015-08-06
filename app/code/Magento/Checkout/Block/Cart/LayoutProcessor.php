<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
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
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

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
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    protected $carrierFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param \Magento\Directory\Model\Resource\Country\Collection $countryCollection
     * @param \Magento\Directory\Model\Resource\Region\Collection $regionCollection
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\Resource\Country\Collection $countryCollection,
        \Magento\Directory\Model\Resource\Region\Collection $regionCollection,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->merger = $merger;
        $this->countryCollection = $countryCollection;
        $this->regionCollection = $regionCollection;
        $this->carrierFactory = $carrierFactory;
        $this->checkoutSession = $checkoutSession;
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
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
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
     * Show State in Shipping Estimation
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStateActive()
    {
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
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $elements = [
            'city' => [
                'visible' => $this->getCityActive(),
                'formElement' => 'input',
                'label' => __('City'),
                'validation' => $this->isCityRequired() ? ['required-entry' => true] : null
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
                'validation' => $this->isStateProvinceRequired() ? ['required-entry' => true] : null
            ],
            'postcode' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
                'validation' => $this->isZipCodeRequired() ? ['required-entry' => true] : null
            ]
        ];

        if (isset($jsLayout['components']['block-summary']['children']['block-shipping']['children']
            ['address-fieldsets']['children'])
        ) {
            $fieldSetPointer = &$jsLayout['components']['block-summary']['children']['block-shipping']
            ['children']['address-fieldsets']['children'];
            $fieldSetPointer = $this->merger->merge($elements, 'checkoutProvider', 'shippingAddress', $fieldSetPointer);
        }
        return $jsLayout;
    }
}
