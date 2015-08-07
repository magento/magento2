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
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $defaultShippingAddress = null;

    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param \Magento\Directory\Model\Resource\Country\Collection $countryCollection
     * @param \Magento\Directory\Model\Resource\Region\Collection $regionCollection
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\Resource\Country\Collection $countryCollection,
        \Magento\Directory\Model\Resource\Region\Collection $regionCollection,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->merger = $merger;
        $this->countryCollection = $countryCollection;
        $this->regionCollection = $regionCollection;
        $this->carrierFactory = $carrierFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Obtain available carriers instances
     *
     * @return array
     */
    protected function getCarriers()
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
    protected function getAddress()
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
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomer()
    {
        if (!$this->customer) {
            if ($this->customerSession->isLoggedIn()) {
                $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            } else {
                return null;
            }
        }
        return $this->customer;
    }

    /**
     * Get Estimate Rates
     *
     * @return array
     */
    protected function getEstimateRates()
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
    protected function getEstimatedCountryId()
    {
        return $this->getAddress()->getCountryId();
    }

    /**
     * Show City in Shipping Estimation
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function isCityActive()
    {
        return false;
    }

    /**
     * Show State in Shipping Estimation
     *
     * @return bool
     */
    protected function isStateActive()
    {
        return false;
    }

    /**
     * Check if one of carriers require city
     *
     * @return bool
     */
    protected function isCityRequired()
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
    protected function isStateProvinceRequired()
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
    protected function isZipCodeRequired()
    {
        foreach ($this->getCarriers() as $carrier) {
            if ($carrier->isZipCodeRequired($this->getEstimatedCountryId())) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    protected function getDefaultShippingAddress()
    {
        if ($this->defaultShippingAddress == null) {
            $customer = $this->getCustomer();
            if ($customer && $customer->getAddresses()) {
                foreach ($customer->getAddresses() as $address) {
                    if ($address->isDefaultShipping()) {
                        $this->defaultShippingAddress = $address;
                        return $this->defaultShippingAddress;
                    }
                }
            }
        }
        return $this->defaultShippingAddress;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $defaultAddress = $this->getDefaultShippingAddress();
        $elements = [
            'city' => [
                'visible' => $this->isCityActive(),
                'formElement' => 'input',
                'label' => __('City'),
                'validation' => $this->isCityRequired() ? ['required-entry' => true] : null,
                'value' => $defaultAddress ? $defaultAddress->getCity() : null
            ],
            'country_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('Country'),
                'options' => $this->countryCollection->load()->toOptionArray(),
                'value' => $defaultAddress ? $defaultAddress->getCountryId() : null
            ],
            'region_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('State/Province'),
                'options' => $this->regionCollection->load()->toOptionArray(),
                'validation' => $this->isStateProvinceRequired() ? ['required-entry' => true] : null,
                'value' => $defaultAddress ? $defaultAddress->getRegionId() : null
            ],
            'postcode' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
                'validation' => $this->isZipCodeRequired() ? ['required-entry' => true] : null,
                'value' => $defaultAddress ? $defaultAddress->getPostcode() : null
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
