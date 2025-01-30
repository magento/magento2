<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\ResourceModel\Import\Customer;

use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;

/**
 * Storage to check existing customers.
 */
class Storage
{
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
     */
    protected $_pageSize;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var CustomerCollection
     */
    public $_customerCollection;

    /**
     * Existing customers store IDs. In form of:
     *
     * [customer email] => array(
     *    [website id 1] => store id 1,
     *    [website id 2] => store id 2,
     *           ...       =>     ...      ,
     *    [website id n] => store id n,
     * )
     *
     * @var array
     */
    private $customerStoreIds = [];

    /**
     * @var Share
     */
    private $configShare;

    /**
     * @param CustomerCollectionFactory $collectionFactory
     * @param Share $configShare
     * @param array $data
     */
    public function __construct(
        CustomerCollectionFactory $collectionFactory,
        Share $configShare,
        array $data = []
    ) {
        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $collectionFactory->create();
        $this->_pageSize = isset($data['page_size']) ? (int) $data['page_size'] : 0;
        $this->customerCollectionFactory = $collectionFactory;
        $this->configShare = $configShare;
    }

    /**
     * Load customer's data that can be found by given identifiers.
     *
     * @param array $customerIdentifiers With keys "email" and "website_id".
     * @return void
     */
    private function loadCustomersData(array $customerIdentifiers): void
    {
        /** @var CustomerCollection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->removeAttributeToSelect();
        $select = $collection->getSelect();
        $customerTableId = array_keys($select->getPart(Select::FROM))[0];

        $pageSize = $this->_pageSize ?: count($customerIdentifiers);
        $getChuck = function (int $offset) use ($customerIdentifiers, $pageSize) {
            return array_slice($customerIdentifiers, $offset, $pageSize);
        };
        $offset = 0;
        for ($chunk = $getChuck($offset); !empty($chunk); $offset += $pageSize, $chunk = $getChuck($offset)) {
            $customerWebsites = array_reduce($chunk, function ($customerWebsiteByEmail, $customer) {
                $customerWebsiteByEmail[$customer['email']][] = $customer['website_id'];
                return $customerWebsiteByEmail;
            }, []);
            $chunkSelect = clone $select;
            $chunkSelect->where($customerTableId . '.email IN (?)', array_keys($customerWebsites));
            $customers = $collection->getConnection()->fetchAll($chunkSelect);
            foreach ($customers as $customer) {
                $this->addCustomerByArray($customer);
                if ($this->configShare->isGlobalScope() &&
                    !in_array((int) $customer['website_id'], $customerWebsites[$customer['email']], true)
                ) {
                    foreach ($customerWebsites[$customer['email']] as $websiteId) {
                        $customer['website_id'] = $websiteId;
                        $this->addCustomerByArray($customer);
                    }
                }
            }
        }
    }

    /**
     * Add a customer by an array
     *
     * @param array $customer
     * @return $this
     */
    public function addCustomerByArray(array $customer): Storage
    {
        $email = isset($customer['email']) ? mb_strtolower(trim($customer['email'])) : '';
        if (!isset($this->_customerIds[$email])) {
            $this->_customerIds[$email] = [];
        }
        if (!isset($this->customerStoreIds[$email])) {
            $this->customerStoreIds[$email] = [];
        }
        $websiteId = (int) $customer['website_id'];
        $this->_customerIds[$email][$websiteId] = (int) $customer['entity_id'];
        $this->customerStoreIds[$email][$websiteId] = $customer['store_id'] ?? null;

        return $this;
    }

    /**
     * Add customer to array
     *
     * @deprecated 100.3.0
     * @see addCustomerByArray
     * @param DataObject $customer
     * @return $this
     */
    public function addCustomer(DataObject $customer): Storage
    {
        $customerData = $customer->toArray();
        if (!isset($customerData['entity_id']) && isset($customer['id'])) {
            $customerData['entity_id'] = $customerData['id'];
        }
        $this->addCustomerByArray($customerData);

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
        $this->loadCustomerData($email, $websiteId);

        if (isset($this->_customerIds[$email][$websiteId])) {
            return $this->_customerIds[$email][$websiteId];
        }

        return false;
    }

    /**
     * Get previously loaded customer id.
     *
     * @param string $email
     * @param int $websiteId
     * @return int|null
     */
    public function getLoadedCustomerId(string $email, int $websiteId): ?int
    {
        return $this->_customerIds[mb_strtolower($email)][$websiteId] ?? null;
    }

    /**
     * Find customer store ID for unique pair of email and website ID.
     *
     * @param string $email
     * @param int $websiteId
     * @return bool|int
     */
    public function getCustomerStoreId(string $email, int $websiteId)
    {
        $email = mb_strtolower($email);
        $this->loadCustomerData($email, $websiteId);

        if (isset($this->customerStoreIds[$email][$websiteId])) {
            return $this->customerStoreIds[$email][$websiteId];
        }

        return false;
    }

    /**
     * Pre-load customers for future checks.
     *
     * @param array[] $customersToFind With keys: email, website_id.
     * @return void
     */
    public function prepareCustomers(array $customersToFind): void
    {
        $identifiers = [];
        foreach ($customersToFind as $customerToFind) {
            $email = isset($customerToFind['email']) ? mb_strtolower($customerToFind['email']) : '';
            $websiteId = $customerToFind['website_id'];
            if (!$this->isLoadedCustomerData($email, $websiteId)) {
                //Only looking for customers we don't already have ID for.
                //We need unique identifiers.
                $uniqueKey = $email . '_' . $websiteId;
                $identifiers[$uniqueKey] = [
                    'email' => $email,
                    'website_id' => $websiteId,
                ];
                //Recording that we've searched for a customer.
                if (!array_key_exists($email, $this->_customerIds)) {
                    $this->_customerIds[$email] = [];
                    $this->customerStoreIds[$email] = [];
                }
                $this->_customerIds[$email][$websiteId] = null;
                $this->customerStoreIds[$email][$websiteId] = null;
            }
        }
        if (!$identifiers) {
            return;
        }

        //Loading customers data.
        $this->loadCustomersData($identifiers);
    }

    /**
     * Load customer data if it's not loaded.
     *
     * @param string $email
     * @param int $websiteId
     * @return void
     */
    private function loadCustomerData(string $email, int $websiteId): void
    {
        if (!$this->isLoadedCustomerData($email, $websiteId)) {
            $this->loadCustomersData([['email' => $email, 'website_id' => $websiteId]]);
        }
    }

    /**
     * Check if customer data is loaded
     *
     * @param string $email
     * @param int $websiteId
     * @return bool
     */
    private function isLoadedCustomerData(string $email, int $websiteId): bool
    {
        return array_key_exists($email, $this->_customerIds)
            && array_key_exists($websiteId, $this->_customerIds[$email]);
    }
}
