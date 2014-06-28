<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CustomerImportExport\Model\Resource\Import\Customer;

class Storage
{
    /**
     * Flag to not load collection more than one time
     *
     * @var bool
     */
    protected $_isCollectionLoaded = false;

    /**
     * Customer collection
     *
     * @var \Magento\Customer\Model\Resource\Customer\Collection
     */
    protected $_customerCollection;

    /**
     * Existing customers information. In form of:
     *
     * [customer e-mail] => array(
     *    [website id 1] => customer_id 1,
     *    [website id 2] => customer_id 2,
     *           ...       =>     ...      ,
     *    [website id n] => customer_id n,
     * )
     *
     * @var array
     */
    protected $_customerIds = array();

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     */
    protected $_pageSize;

    /**
     * Collection by pages iterator
     *
     * @var \Magento\ImportExport\Model\Resource\CollectionByPagesIterator
     */
    protected $_byPagesIterator;

    /**
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $collectionFactory
     * @param \Magento\ImportExport\Model\Resource\CollectionByPagesIteratorFactory $colIteratorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $collectionFactory,
        \Magento\ImportExport\Model\Resource\CollectionByPagesIteratorFactory $colIteratorFactory,
        array $data = array()
    ) {
        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $collectionFactory->create();
        $this->_pageSize = isset($data['page_size']) ? $data['page_size'] : 0;
        $this->_byPagesIterator = isset(
            $data['collection_by_pages_iterator']
        ) ? $data['collection_by_pages_iterator'] : $colIteratorFactory->create();
    }

    /**
     * Load needed data from customer collection
     *
     * @return void
     */
    public function load()
    {
        if ($this->_isCollectionLoaded == false) {
            $collection = clone $this->_customerCollection;
            $collection->removeAttributeToSelect();
            $tableName = $collection->getResource()->getEntityTable();
            $collection->getSelect()->from($tableName, array('entity_id', 'website_id', 'email'));

            $this->_byPagesIterator->iterate(
                $this->_customerCollection,
                $this->_pageSize,
                array(array($this, 'addCustomer'))
            );

            $this->_isCollectionLoaded = true;
        }
    }

    /**
     * Add customer to array
     *
     * @param \Magento\Framework\Object|\Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function addCustomer(\Magento\Framework\Object $customer)
    {
        $email = strtolower(trim($customer->getEmail()));
        if (!isset($this->_customerIds[$email])) {
            $this->_customerIds[$email] = array();
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
        // lazy loading
        $this->load();

        if (isset($this->_customerIds[$email][$websiteId])) {
            return $this->_customerIds[$email][$websiteId];
        }

        return false;
    }
}
