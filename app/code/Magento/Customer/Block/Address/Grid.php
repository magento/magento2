<?php
declare(strict_types=1);
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
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout(): void
    {
        parent::_prepareLayout();
        $this->preparePager();
    }

    /**
     * Generate and return "New Address" URL
     *
     * @return string
     */
    public function getAddAddressUrl(): string
    {
        return $this->getUrl('customer/address/new', ['_secure' => true]);
    }

    /**
     * Generate and return "Delete" URL
     *
     * @return string
     */
    public function getDeleteUrl(): string
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
    public function getAddressEditUrl($addressId): string
    {
        return $this->getUrl('customer/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * Get current additional customer addresses
     *
     * Will return array of address interfaces if customer have additional addresses and false in other case.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAdditionalAddresses(): array
    {
        $additional = [];
        $addresses = $this->getAddressCollection();
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array((int)$address->getId(), $primaryAddressIds, true)) {
                $additional[] = $address->getDataModel();
            }
        }
        return $additional;
    }

    /**
     * Get current customer
     *
     * Return stored customer or get it from session
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer(): \Magento\Customer\Api\Data\CustomerInterface
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            $customer = $this->currentCustomer->getCustomer();
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
    public function getStreetAddress($street): string
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
    public function getCountryByCode($countryCode): string
    {
        /** @var \Magento\Directory\Model\Country $country */
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryCode)->getName();
    }


    /**
     * Get default billing address
     * Return address string if address found and null of not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultBilling(): int
    {
        $customer = $this->getCustomer();

        return (int)$customer->getDefaultBilling();
    }


    /**
     * Get default shipping address
     * Return address string if address found and null of not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultShipping(): int
    {
        $customer = $this->getCustomer();

        return (int)$customer->getDefaultShipping();
    }

    /**
     * Get pager layout
     *
     * @return f
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function preparePager(): void
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
    private function getAddressCollection(): \Magento\Customer\Model\ResourceModel\Address\Collection
    {
        if (null === $this->addressCollection) {
            if (null === $this->getCustomer()) {
                throw new NoSuchEntityException(__('Customer not logged in'));
            }
            /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
            $collection = $this->addressCollectionFactory->create();
            $collection->setOrder('entity_id', 'desc')
                ->setCustomerFilter([$this->getCustomer()->getId()]);
            $this->addressCollection = $collection;
        }
        return $this->addressCollection;
    }
}
