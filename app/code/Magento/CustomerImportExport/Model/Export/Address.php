<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Export;

use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer address export
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Address extends AbstractEav
{
    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute.
     * This name convention is for to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL = '_email';

    const COLUMN_WEBSITE = '_website';

    const COLUMN_ADDRESS_ID = '_entity_id';

    /**#@-*/

    /**
     * Country column name for index value
     */
    const COLUMN_COUNTRY_ID = 'country_id';

    /**
     * Name of region id column
     */
    const COLUMN_REGION_ID = 'region_id';

    /**#@+
     * Particular columns that contains of customer default addresses
     */
    const COLUMN_NAME_DEFAULT_BILLING = '_address_default_billing_';

    const COLUMN_NAME_DEFAULT_SHIPPING = '_address_default_shipping_';

    /**#@-*/

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection::class;

    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/address';

    /**#@-*/

    /**#@-*/
    protected $_permanentAttributes = [self::COLUMN_WEBSITE, self::COLUMN_EMAIL, self::COLUMN_ADDRESS_ID];

    /**
     * Attributes with index (not label) value
     *
     * @var string[]
     * @since 100.2.0
     */
    protected $_indexValueAttributes = [self::COLUMN_COUNTRY_ID];

    /**
     * Default addresses column names to appropriate customer attribute code
     *
     * @var array
     */
    protected static $_defaultAddressAttributeMapping = [
        self::COLUMN_NAME_DEFAULT_BILLING => 'default_billing',
        self::COLUMN_NAME_DEFAULT_SHIPPING => 'default_shipping',
    ];

    /**
     * Customers whose addresses are exported
     *
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $_customerCollection;

    /**
     * Customer addresses collection
     *
     * @var Collection
     */
    protected $_addressCollection;

    /**
     * Customers whose address are exported
     *
     * @var Customer
     */
    protected $_customerEntity;

    /**
     * Existing customers information.
     *
     * In form of:
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
    protected $_customers;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerColFactory
     * @param CustomerFactory $eavCustomerFactory
     * @param CollectionFactory $addressColFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        TimezoneInterface $localeDate,
        Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerColFactory,
        CustomerFactory $eavCustomerFactory,
        CollectionFactory $addressColFactory,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $data
        );

        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $customerColFactory->create();

        $this->_customerEntity = isset(
            $data['customer_entity']
        ) ? $data['customer_entity'] : $eavCustomerFactory->create();

        $this->_addressCollection = isset(
            $data['address_collection']
        ) ? $data['address_collection'] : $addressColFactory->create();

        $this->_initAttributeValues()->_initAttributeTypes()->_initWebsites(true);
        $this->setFileName($this->getEntityTypeCode());
    }

    /**
     * Initialize existent customers data
     *
     * @return $this
     */
    protected function _initCustomers()
    {
        if ($this->_customers === null) {
            $this->_customers = [];
            // add customer default addresses column name to customer attribute mapping array
            $this->_customerCollection->addAttributeToSelect(self::$_defaultAddressAttributeMapping);
            // filter customer collection
            $this->_customerCollection = $this->_customerEntity->filterEntityCollection($this->_customerCollection);

            $selectIds = $this->_customerCollection->getAllIdsSql();
            $this->_customerCollection->setPageSize($this->_pageSize);
            $pageCount = $this->_customerCollection->getLastPageNumber();

            for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
                $this->_customers += $this->loadCustomerData($selectIds, $pageNum);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        return array_merge(
            $this->_permanentAttributes,
            $this->_getExportAttributeCodes(),
            array_keys(self::$_defaultAddressAttributeMapping)
        );
    }

    /**
     * Get customers collection
     *
     * @return Collection
     */
    protected function _getEntityCollection()
    {
        return $this->_addressCollection;
    }

    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        // skip and filter by customer address attributes
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $this->_getEntityCollection()->setCustomerFilter(array_keys($this->getCustomers()));

        // prepare headers
        $this->getWriter()->setHeaderCols($this->_getHeaderColumns());

        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $this->getWriter()->getContents();
    }

    /**
     * Export given customer address data plus related customer data (required for import)
     *
     * @param \Magento\Customer\Model\Address $item
     * @return void
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);

        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->getCustomers()[$item->getParentId()];

        // Fill row with default address attributes values
        foreach (self::$_defaultAddressAttributeMapping as $columnName => $attributeCode) {
            if (!empty($customer[$attributeCode]) && $customer[$attributeCode] == $item->getId()) {
                $row[$columnName] = 1;
            }
        }

        // Unique key
        $row[self::COLUMN_ADDRESS_ID] = $item['entity_id'];
        $row[self::COLUMN_EMAIL] = $customer['email'];
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$customer['website_id']];
        $row[self::COLUMN_REGION_ID] = $item->getRegionId();

        $this->getWriter()->writeRow($row);
    }

    /**
     * Set parameters (push filters from post into export customer model)
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        // push filters from post into export customer model
        $this->_customerEntity->setParameters($parameters);
        return parent::setParameters($parameters);
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getAttributeCollection()->getEntityTypeCode();
    }

    /**
     * Get Customers Data
     *
     * @return array
     */
    private function getCustomers(): array
    {
        $this->_initCustomers();
        return $this->_customers;
    }

    /**
     * Load Customers Data
     *
     * @param Select $selectIds
     * @param int $pageNum
     * @return array
     */
    private function loadCustomerData(Select $selectIds, int $pageNum = 0): array
    {
        $select = $this->_customerCollection->getConnection()->select();
        $select->from(
            ['customer' => $this->_customerCollection->getTable('customer_entity')],
            ['entity_id', 'email', 'store_id', 'website_id', 'default_billing', 'default_shipping']
        )->where(
            'customer.entity_id IN (?)', $selectIds
        );

        if ($pageNum > 0) {
           $select->limitPage($pageNum, $this->_pageSize);
        }

        return $this->_customerCollection->getConnection()->fetchAssoc($select);
    }
}
