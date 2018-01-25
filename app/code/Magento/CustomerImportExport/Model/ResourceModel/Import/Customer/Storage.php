<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\ResourceModel\Import\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\FilterBuilder;

class Storage
{
    /**
     * Flag to not load collection more than one time
     *
     * @var bool
     * @deprecated Collection is not used anymore.
     */
    protected $_isCollectionLoaded = false;

    /**
     * Customer collection
     *
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     * @deprecated
     */
    protected $_customerCollection;

    /**
     * Existing customers information. In form of:
     *
     * [customer email] => array(
     *    [website id 1] => customer_id 1,
     *    [website id 2] => customer_id 2,
     *           ...       =>     ...      ,
     *    [website id n] => customer_id n,
     * )
     *
     * @var array
     */
    protected $_customerIds = [];

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     * @deprecated
     */
    protected $_pageSize;

    /**
     * Collection by pages iterator
     *
     * @var \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
     * @deprecated
     */
    protected $_byPagesIterator;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory
     * @param array $data
     * @param CustomerRepositoryInterface|null $customerRepository
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param FilterBuilder|null $filterBuilder
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory,
        array $data = [],
        CustomerRepositoryInterface $customerRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterBuilder $filterBuilder = null
    ) {
        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $collectionFactory->create();
        $this->_pageSize = isset($data['page_size']) ? $data['page_size'] : 0;
        $this->_byPagesIterator = isset(
            $data['collection_by_pages_iterator']
        ) ? $data['collection_by_pages_iterator'] : $colIteratorFactory->create();
        $this->customerRepository = $customerRepository
            ?: ObjectManager::getInstance()
                ->get(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->filterBuilder = $filterBuilder
            ?: ObjectManager::getInstance()->get(FilterBuilder::class);
    }

    /**
     * Load needed data from customer collection
     *
     * @return void
     * @deprecated This method of loading customers is not used anymore.
     */
    public function load()
    {
        if ($this->_isCollectionLoaded == false) {
            $collection = clone $this->_customerCollection;
            $collection->removeAttributeToSelect();
            $tableName = $collection->getResource()->getEntityTable();
            $collection->getSelect()->from($tableName, ['entity_id', 'website_id', 'email']);

            $this->_byPagesIterator->iterate(
                $this->_customerCollection,
                $this->_pageSize,
                [[$this, 'addCustomer']]
            );

            $this->_isCollectionLoaded = true;
        }
    }

    /**
     * Add customer to array
     *
     * @param DataObject $customer
     * @return $this
     */
    public function addCustomer(DataObject $customer)
    {
        $email = strtolower(trim($customer->getEmail()));
        if (!isset($this->_customerIds[$email])) {
            $this->_customerIds[$email] = [];
        }
        $this->_customerIds[$email][$customer->getWebsiteId()] = $customer->getId();

        return $this;
    }

    /**
     * Get customer id
     *
     * @param string $email
     * @param int $websiteId
     * @return bool|int
     */
    public function getCustomerId($email, $websiteId)
    {
        //Trying to load the customer.
        if (!array_key_exists($email, $this->_customerIds)
            || !array_key_exists($websiteId, $this->_customerIds[$email])
        ) {
            try {
                $customer = $this->customerRepository->get($email, $websiteId);
                $customerData = new DataObject([
                    'id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'website_id' => $customer->getWebsiteId()
                ]);
            } catch (NoSuchEntityException $exception) {
                $customerData = new DataObject([
                    'id' => null,
                    'email' => $email,
                    'website_id' => $websiteId
                ]);
            }
            $this->addCustomer($customerData);
        }

        if (isset($this->_customerIds[$email][$websiteId])) {
            return $this->_customerIds[$email][$websiteId];
        }

        return false;
    }

    /**
     * Pre-load customers for future checks.
     *
     * @param array[] $customersToFind With keys: email, website_id.
     * @return void
     */
    public function prepareCustomers(array $customersToFind)
    {
        $customersData = [];
        $filters = [];
        foreach ($customersToFind as $customerToFind) {
            $email = $customerToFind['email'];
            $websiteId = $customerToFind['website_id'];
            if (!array_key_exists($email, $this->_customerIds)
                || !array_key_exists($websiteId, $this->_customerIds[$email])
            ) {
                //Only looking for customers we don't already have ID for.
                $customersData[] = $customerToFind;
                $filters[] = $this->filterBuilder
                    ->setField('email')
                    ->setValue($customerToFind['email'])
                    ->setConditionType('eq')
                    ->create();
            }
        }
        if (!$customersData) {
            return;
        }

        $this->searchCriteriaBuilder->addFilters($filters);

        //Adding customers that we found.
        $found = $this->customerRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
        foreach ($found->getItems() as $customer) {
            $this->addCustomer(new DataObject([
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'website_id' => $customer->getWebsiteId()
            ]));
        }
        //Adding customers that don't exist.
        foreach ($customersData as $customerData) {
            $email = $customerData['email'];
            $websiteId = $customerData['website_id'];
            if (!array_key_exists($email, $this->_customerIds)
                || !array_key_exists($websiteId, $this->_customerIds[$email])
            ) {
                $this->_customerIds[$email][$websiteId] = null;
            }
        }
    }
}
