<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer address grid
 */
class Grid extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    private $addressCollection;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CountryFactory $countryFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        AddressCollectionFactory $addressCollectionFactory,
        CountryFactory $countryFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->countryFactory = $countryFactory;

        parent::__construct($context, $data);
    }

    /**
     * Prepare the Address Book section layout
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->preparePager();
        return $this;
    }

    /**
     * Generate and return "New Address" URL
     *
     * @return string
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', ['_secure' => true]);
    }

    /**
     * Generate and return "Delete" URL
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('customer/address/delete');
    }

    /**
     * Generate and return "Edit Address" URL.
     *
     * Address ID passed in parameters
     *
     * @param int $addressId
     * @return string
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->getUrl('customer/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * Get current additional customer addresses
     *
     * Will return array of address interfaces if customer have additional addresses and false in other case.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->getAddressCollection();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array($address->getId(), $primaryAddressIds)) {
                $additional[] = $address->getDataModel();
            }
        }
        return empty($additional) ? false : $additional;
    }

    /**
     * Get current customer
     *
     * Check if customer is stored in current object and return it
     * or get customer by current customer ID through repository
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            try {
                $customer = $this->currentCustomer->getCustomer();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * Get one string street address from "two fields" array or just returns string if it was passed in parameters
     *
     * @param string|array $street
     * @return string
     */
    public function getStreetAddress($street)
    {
        if (is_array($street)) {
            $street = implode(', ', $street);
        }
        return $street;
    }

    /**
     * Get country name by $countryCode
     *
     * Using \Magento\Directory\Model\Country to get country name by $countryCode
     *
     * @param string $countryCode
     * @return string
     */
    public function getCountryByCode($countryCode)
    {
        /** @var \Magento\Directory\Model\Country $country */
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryCode)->getName();
    }


    /**
     * Get default billing address
     * Return address string if address found and null of not
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultBilling()
    {
        $customer = $this->getCustomer();

        return $customer->getDefaultBilling();
    }


    /**
     * Get default shipping address
     * Return address string if address found and null of not
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultShipping()
    {
        $customer = $this->getCustomer();

        return $customer->getDefaultShipping();
    }

    /**
     * Get pager layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function preparePager()
    {
        $addressCollection = $this->getAddressCollection();
        if (null !== $addressCollection) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.addresses.pager'
            )->setCollection($addressCollection);
            $this->setChild('pager', $pager);
        }
    }

    /**
     * Get customer addresses collection.
     *
     * Filters collection by customer id
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @throws NoSuchEntityException
     */
    private function getAddressCollection()
    {
        if (null === $this->addressCollection && $this->getCustomer()) {
            /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
            $collection = $this->addressCollectionFactory->create();
            $collection->setOrder('entity_id', 'desc')
                ->setCustomerFilter([$this->getCustomer()->getId()]);
            $this->addressCollection = $collection;
        } elseif (null === $this->getCustomer()) {
            throw new NoSuchEntityException(__('Customer not logged in'));
        }
        return $this->addressCollection;
    }
}
