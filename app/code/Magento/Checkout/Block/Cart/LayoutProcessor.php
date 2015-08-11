<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
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
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Directory\Model\Resource\Country\Collection $countryCollection,
        \Magento\Directory\Model\Resource\Region\Collection $regionCollection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->merger = $merger;
        $this->countryCollection = $countryCollection;
        $this->regionCollection = $regionCollection;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
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
     * Show City in Shipping Estimation
     *
     * @return bool
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
                'value' => $defaultAddress ? $defaultAddress->getRegionId() : null
            ],
            'postcode' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
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
