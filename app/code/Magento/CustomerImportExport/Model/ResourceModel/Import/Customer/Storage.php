<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\ResourceModel\Import\Customer;

<<<<<<< HEAD
use Magento\CustomerImportExport\Test\Unit\Model\Import\CustomerCompositeTest;
=======
>>>>>>> upstream/2.2-develop
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator;

<<<<<<< HEAD
class Storage
{
    /**
=======
/**
 * Helper class to help dealing with existent customers.
 */
class Storage
{
    /**
     * @deprecated
     */
    protected $_isCollectionLoaded = false;

    /**
     * @deprecated
     */
    protected $_customerCollection;

    /**
>>>>>>> upstream/2.2-develop
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
     */
    protected $_pageSize;

    /**
     * Collection by pages iterator.
     *
     * @var CollectionByPagesIterator
     */
    protected $_byPagesIterator;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @param CustomerCollectionFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $colIteratorFactory
     * @param array $data
     */
    public function __construct(
        CustomerCollectionFactory $collectionFactory,
        CollectionByPagesIteratorFactory $colIteratorFactory,
        array $data = []
    ) {
        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $collectionFactory->create();
        $this->_pageSize = isset($data['page_size']) ? $data['page_size'] : 0;
        $this->_byPagesIterator = isset(
            $data['collection_by_pages_iterator']
        ) ? $data['collection_by_pages_iterator'] : $colIteratorFactory->create();
        $this->customerCollectionFactory = $collectionFactory;
    }

    /**
<<<<<<< HEAD
     * Create new collection to load customer data with proper filters.
     *
     * @param array[] $customerIdentifiers With keys "email" and "website_id".
     *
     * @return CustomerCollection
     */
    private function prepareCollection(array $customerIdentifiers): CustomerCollection
    {
        /** @var CustomerCollection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->removeAttributeToSelect();
        $select = $collection->getSelect();
        $customerTableId = array_keys($select->getPart(Select::FROM))[0];
        $select->where(
            $customerTableId .'.email in (?)',
            array_map(
                function (array $customer) {
                    return $customer['email'];
                },
                $customerIdentifiers
            )
        );

        return $collection;
    }

    /**
     * Load customers' data that can be found by given identifiers.
     *
     * @param array $customerIdentifiers With keys "email" and "website_id".
     *
=======
>>>>>>> upstream/2.2-develop
     * @return void
     *
     * @deprecated
     * @see prepareCustomers
     */
    private function loadCustomersData(array $customerIdentifiers)
    {
        $this->_byPagesIterator->iterate(
            $this->prepareCollection($customerIdentifiers),
            $this->_pageSize,
            [[$this, 'addCustomer']]
        );
    }

    /**
     * @param array $customer
     * @return $this
     */
    public function addCustomerByArray(array $customer): Storage
    {
        $email = strtolower(trim($customer['email']));
        if (!isset($this->_customerIds[$email])) {
            $this->_customerIds[$email] = [];
        }
        $this->_customerIds[$email][$customer['website_id']] = $customer['entity_id'];

        return $this;
    }

    /**
     * Create new collection to load customer data with proper filters.
     *
     * @param array[] $customerIdentifiers With keys "email" and "website_id".
     *
     * @return CustomerCollection
     */
    private function prepareCollection(
        array $customerIdentifiers
    ): CustomerCollection {
        /** @var CustomerCollection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->removeAttributeToSelect();
        $select = $collection->getSelect();
        $customerTableId = array_keys($select->getPart(Select::FROM))[0];
        $select->where(
            $customerTableId .'.email in (?)',
            array_map(
                function (array $customer) {
                    return $customer['email'];
                },
                $customerIdentifiers
            )
        );

        return $collection;
    }

    /**
     * Load customers' data that can be found by given identifiers.
     *
     * @param array $customerIdentifiers With keys "email" and "website_id".
     *
     * @return void
     */
    private function loadCustomersData(array $customerIdentifiers)
    {
        $this->_byPagesIterator->iterate(
            $this->prepareCollection($customerIdentifiers),
            $this->_pageSize,
            [[$this, 'addCustomer']]
        );
    }

    /**
     * Add customer to array
     *
<<<<<<< HEAD
     * @deprecated @see addCustomerByArray
     * @param DataObject $customer
     * @return $this
     */
    public function addCustomer(DataObject $customer): Storage
=======
     * @param DataObject $customer
     * @return $this
     */
    public function addCustomer(DataObject $customer)
>>>>>>> upstream/2.2-develop
    {
        $customerData = $customer->toArray();
        if (!isset($customerData['entity_id']) && isset($customer['id'])) {
            $customerData['entity_id'] = $customerData['id'];
        }
<<<<<<< HEAD
        $this->addCustomerByArray($customerData);
=======
        $this->_customerIds[$email][$customer->getWebsiteId()]
            = $customer->getId();
>>>>>>> upstream/2.2-develop

        return $this;
    }

    /**
     * Find customer ID for unique pair of email and website ID.
     *
     * @param string $email
     * @param int $websiteId
     * @return bool|int
     */
    public function getCustomerId(string $email, int $websiteId)
    {
        $email = mb_strtolower($email);
        //Trying to load the customer.
<<<<<<< HEAD
        if (!array_key_exists($email, $this->_customerIds) || !array_key_exists($websiteId, $this->_customerIds[$email])
        ) {
            $this->loadCustomersData([['email' => $email, 'website_id' => $websiteId]]);
=======
        if (!array_key_exists($email, $this->_customerIds)
            || !array_key_exists($websiteId, $this->_customerIds[$email])
        ) {
            $this->loadCustomersData([
                ['email' => $email, 'website_id' => $websiteId]
            ]);
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
    public function prepareCustomers(array $customersToFind): void
=======
    public function prepareCustomers(array $customersToFind)
>>>>>>> upstream/2.2-develop
    {
        $identifiers = [];
        foreach ($customersToFind as $customerToFind) {
            $email = mb_strtolower($customerToFind['email']);
            $websiteId = $customerToFind['website_id'];
            if (!array_key_exists($email, $this->_customerIds)
                || !array_key_exists($websiteId, $this->_customerIds[$email])
            ) {
                //Only looking for customers we don't already have ID for.
                //We need unique identifiers.
                $uniqueKey = $email .'_' .$websiteId;
                $identifiers[$uniqueKey] = [
                    'email' => $email,
<<<<<<< HEAD
                    'website_id' => $websiteId,
=======
                    'website_id' => $websiteId
>>>>>>> upstream/2.2-develop
                ];
                //Recording that we've searched for a customer.
                if (!array_key_exists($email, $this->_customerIds)) {
                    $this->_customerIds[$email] = [];
                }
                $this->_customerIds[$email][$websiteId] = null;
            }
        }
        if (!$identifiers) {
            return;
        }

        //Loading customers data.
        $this->loadCustomersData($identifiers);
    }
}
