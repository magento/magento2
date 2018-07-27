<?php
/**
 * Customer address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Model\Address as CustomerAddressModel;
use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

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
     * @var \Magento\Directory\Model\AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\Address $addressResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Directory\Model\AllowedCountries|null $allowedCountriesReader
     * @param \Magento\Customer\Model\Config\Share|null $shareConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\Directory\Model\AllowedCountries $allowedCountriesReader = null,
        \Magento\Customer\Model\Config\Share $shareConfig = null
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRegistry = $addressRegistry;
        $this->customerRegistry = $customerRegistry;
        $this->addressResourceModel = $addressResourceModel;
        $this->directoryData = $directoryData;
        $this->addressSearchResultsFactory = $addressSearchResultsFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: ObjectManager::getInstance()->get(\Magento\Directory\Model\AllowedCountries::class);
        $this->shareConfig = $shareConfig
            ?: ObjectManager::getInstance()->get(\Magento\Customer\Model\Config\Share::class);
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

        $inputException = $this->_validate($addressModel);
        if ($inputException->wasErrorAdded()) {
            throw $inputException;
        }
        $addressModel->save();
        $address->setId($addressModel->getId());
        // Clean up the customer registry since the Address save has a
        // side effect on customer : \Magento\Customer\Model\ResourceModel\Address::_afterSave
        $this->customerRegistry->remove($address->getCustomerId());
        $this->addressRegistry->push($addressModel);
        $customerModel->getAddressesCollection()->clear();

        return $addressModel->getDataModel();
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
        $searchResults = $this->addressSearchResultsFactory->create();

        /** @var Collection $collection */
        $collection = $this->addressCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, 'Magento\Customer\Api\Data\AddressInterface');
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $searchResults->setTotalCount($collection->getSize());
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = [];
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($collection->getItems() as $address) {
            $addresses[] = $this->getById($address->getId());
        }
        $searchResults->setItems($addresses);
        $searchResults->setSearchCriteria($searchCriteria);
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
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
        $this->addressResourceModel->delete($address);
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
        $customerModel->getAddressesCollection()->clear();
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Validate Customer Addresses attribute values.
     *
     * @param CustomerAddressModel $customerAddressModel the model to validate
     * @return InputException
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _validate(CustomerAddressModel $customerAddressModel)
    {
        $exception = new InputException();
        if ($customerAddressModel->getShouldIgnoreValidation()) {
            return $exception;
        }

        if (!\Zend_Validate::is($customerAddressModel->getFirstname(), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'firstname']));
        }

        if (!\Zend_Validate::is($customerAddressModel->getLastname(), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'lastname']));
        }

        if (!\Zend_Validate::is($customerAddressModel->getStreetLine(1), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'street']));
        }

        if (!\Zend_Validate::is($customerAddressModel->getCity(), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'city']));
        }

        if (!\Zend_Validate::is($customerAddressModel->getTelephone(), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'telephone']));
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($customerAddressModel->getCountryId(), $havingOptionalZip)
            && !\Zend_Validate::is($customerAddressModel->getPostcode(), 'NotEmpty')
        ) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'postcode']));
        }

        $countryId = (string)$customerAddressModel->getCountryId();
        if (!\Zend_Validate::is($countryId, 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'countryId']));
        } else {
            //Checking if such country exists.
            if (!in_array($countryId, $this->getWebsiteAllowedCountries($customerAddressModel), true)) {
                $exception->addError(
                    __(
                        'Invalid value of "%value" provided for the %fieldName field.',
                        [
                            'fieldName' => 'countryId',
                            'value' => htmlspecialchars($countryId)
                        ]
                    )
                );
            } else {
                //If country is valid then validating selected region ID.
                $countryModel = $customerAddressModel->getCountryModel();
                $regionCollection = $countryModel->getRegionCollection();
                $region = $customerAddressModel->getRegion();
                $regionId = (string)$customerAddressModel->getRegionId();
                $allowedRegions = $regionCollection->getAllIds();
                $isRegionRequired = $this->directoryData->isRegionRequired($countryId);

                if ($isRegionRequired && empty($allowedRegions) && !\Zend_Validate::is($region, 'NotEmpty')) {
                    //If region is required for country and country doesn't provide regions list
                    //region must be provided.
                    $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'region']));
                } elseif ($isRegionRequired && $allowedRegions && !\Zend_Validate::is($regionId, 'NotEmpty')) {
                    //If country actually has regions and requires you to
                    //select one then it must be selected.
                    $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'regionId']));
                } elseif ($regionId && !in_array($regionId, $allowedRegions, true)) {
                    //If a region is selected then checking if it exists.
                    $exception->addError(
                        __(
                            'Invalid value of "%value" provided for the %fieldName field.',
                            [
                                'fieldName' => 'regionId',
                                'value' => htmlspecialchars($regionId)
                            ]
                        )
                    );
                }
            }
        }

        return $exception;
    }

    /**
     * Return allowed counties per website.
     *
     * @param \Magento\Customer\Model\Address $customerAddressModel
     * @return array
     */
    private function getWebsiteAllowedCountries(\Magento\Customer\Model\Address $customerAddressModel)
    {
        $websiteId = null;

        if (!$this->shareConfig->isGlobalScope()) {
            $websiteId = $customerAddressModel->getCustomer()
                ? $customerAddressModel->getCustomer()->getWebsiteId()
                : null;
        }

        return $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }
}
