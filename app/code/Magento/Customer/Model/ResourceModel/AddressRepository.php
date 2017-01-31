<?php
/**
 * Customer address entity resource model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Model\Address as CustomerAddressModel;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements \Magento\Customer\Api\AddressRepositoryInterface
{
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address
     */
    protected $addressResourceModel;

    /**
     * @var \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory
     */
    protected $addressSearchResultsFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\Address $addressResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\ResourceModel\Address $addressResourceModel,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRegistry = $addressRegistry;
        $this->customerRegistry = $customerRegistry;
        $this->addressResource = $addressResourceModel;
        $this->directoryData = $directoryData;
        $this->addressSearchResultsFactory = $addressSearchResultsFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save customer address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $addressModel = null;
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        if ($address->getId()) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
        }

        if ($addressModel === null) {
            /** @var \Magento\Customer\Model\Address $addressModel */
            $addressModel = $this->addressFactory->create();
            $addressModel->updateData($address);
            $addressModel->setCustomer($customerModel);
        } else {
            $addressModel->updateData($address);
        }

        $errors = $addressModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
        $addressModel->save();
        $address->setId($addressModel->getId());
        // Clean up the customer registry since the Address save has a
        // side effect on customer : \Magento\Customer\Model\ResourceModel\Address::_afterSave
        $this->addressRegistry->push($addressModel);
        $this->updateAddressCollection($customerModel, $addressModel);

        $defaultBillingAddressId = null;
        if (!is_null($customerModel->getDefaultBillingAddress())) {
            $defaultBillingAddressId = $customerModel->getDefaultBillingAddress()->getId();
        }

        $defaultShippingAddressId = null;
        if (!is_null($customerModel->getDefaultShippingAddress())) {
            $defaultShippingAddressId = $customerModel->getDefaultShippingAddress()->getId();
        }

        return $addressModel->getDataModel($defaultBillingAddressId, $defaultShippingAddressId);
    }

    /**
     * @param Customer $customer
     * @param Address $address
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateAddressCollection(CustomerModel $customer, CustomerAddressModel $address)
    {
        $customer->getAddressesCollection()->removeItemByKey($address->getId());
        $customer->getAddressesCollection()->addItem($address);
    }

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        return $address->getDataModel();
    }

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Customer\Api\Data\AddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->addressCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = [];
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($collection->getItems() as $address) {
            $addresses[] = $this->getById($address->getId());
        }

        /** @var \Magento\Customer\Api\Data\AddressSearchResultsInterface $searchResults */
        $searchResults = $this->addressSearchResultsFactory->create();
        $searchResults->setItems($addresses);
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Delete customer address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $addressId = $address->getId();
        $address = $this->addressRegistry->retrieve($addressId);
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        $customerModel->getAddressesCollection()->clear();
        $this->addressResource->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        $customerModel->getAddressesCollection()->removeItemByKey($addressId);
        $this->addressResource->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
