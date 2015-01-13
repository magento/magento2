<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource\Customer\Grid;

use Magento\Core\Model\EntityFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Customer Grid Collection backed by Services
 */
class ServiceCollection extends AbstractServiceCollection
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->customerRepository->getList($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            /** @var CustomerInterface[] $customers */
            $customers = $searchResults->getItems();
            foreach ($customers as $customer) {
                $this->_addItem($this->createCustomerItem($customer));
            }
            $this->_setIsLoaded();
        }
        return $this;
    }

    /**
     * Creates a collection item that represents a customer for the customer Grid.
     *
     * @param CustomerInterface $customer Input data for creating the item.
     * @return \Magento\Framework\Object Collection item that represents a customer
     */
    protected function createCustomerItem(CustomerInterface $customer)
    {
        $customerNameParts = [
            $customer->getPrefix(),
            $customer->getFirstname(),
            $customer->getMiddlename(),
            $customer->getLastname(),
            $customer->getSuffix(),
        ];
        $customerItem = new \Magento\Framework\Object();
        $customerItem->setId($customer->getId());
        $customerItem->setEntityId($customer->getId());
        // All parts of the customer name must be displayed in the name column of the grid
        $customerItem->setName(implode(' ', array_filter($customerNameParts)));
        $customerItem->setEmail($customer->getEmail());
        $customerItem->setWebsiteId($customer->getWebsiteId());
        $customerItem->setCreatedAt($customer->getCreatedAt());
        $customerItem->setGroupId($customer->getGroupId());

        $billingAddress = null;
        foreach ($customer->getAddresses() as $address) {
            if ($address->isDefaultBilling()) {
                $billingAddress = $address;
                break;
            }
        }
        if ($billingAddress !== null) {
            $customerItem->setBillingTelephone($billingAddress->getTelephone());
            $customerItem->setBillingPostcode($billingAddress->getPostcode());
            $customerItem->setBillingCountryId($billingAddress->getCountryId());
            $region = is_null($billingAddress->getRegion()) ? '' : $billingAddress->getRegion()->getRegion();
            $customerItem->setBillingRegion($region);
        }
        return $customerItem;
    }
}
