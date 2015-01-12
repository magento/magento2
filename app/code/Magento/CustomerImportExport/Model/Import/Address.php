<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

class Address extends AbstractCustomer
{
    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Magento\Customer\Model\Resource\Address\Attribute\Collection';

    /**#@-*/

    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute.
     * This name convention is for to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL = '_email';

    const COLUMN_ADDRESS_ID = '_entity_id';

    /**#@-*/

    /**#@+
     * Required column names
     */
    const COLUMN_REGION = 'region';

    const COLUMN_COUNTRY_ID = 'country_id';

    /**#@-*/

    /**#@+
     * Particular columns that contains of customer default addresses
     */
    const COLUMN_DEFAULT_BILLING = '_address_default_billing_';

    const COLUMN_DEFAULT_SHIPPING = '_address_default_shipping_';

    /**#@-*/

    /**#@+
     * Error codes
     */
    const ERROR_ADDRESS_ID_IS_EMPTY = 'addressIdIsEmpty';

    const ERROR_ADDRESS_NOT_FOUND = 'addressNotFound';

    const ERROR_INVALID_REGION = 'invalidRegion';

    const ERROR_DUPLICATE_PK = 'duplicateAddressId';

    /**#@-*/

    /**
     * Default addresses column names to appropriate customer attribute code
     *
     * @var array
     */
    protected static $_defaultAddressAttributeMapping = [
        self::COLUMN_DEFAULT_BILLING => 'default_billing',
        self::COLUMN_DEFAULT_SHIPPING => 'default_shipping',
    ];

    /**
     * Permanent entity columns
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COLUMN_WEBSITE, self::COLUMN_EMAIL, self::COLUMN_ADDRESS_ID];

    /**
     * Existing addresses
     *
     * Example Array: [customer ID] => array(
     *     address ID 1,
     *     address ID 2,
     *     ...
     *     address ID N
     * )
     *
     * @var array
     */
    protected $_addresses = [];

    /**
     * Attributes with index (not label) value
     *
     * @var string[]
     */
    protected $_indexValueAttributes = [self::COLUMN_COUNTRY_ID];

    /**
     * Customer entity DB table name
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Countries and regions
     *
     * Example array: array(
     *   [country_id_lowercased_1] => array(
     *     [region_code_lowercased_1]         => region_id_1,
     *     [region_default_name_lowercased_1] => region_id_1,
     *     ...,
     *     [region_code_lowercased_n]         => region_id_n,
     *     [region_default_name_lowercased_n] => region_id_n
     *   ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_countryRegions = [];

    /**
     * Region ID to region default name pairs
     *
     * @var array
     */
    protected $_regions = [];

    /**
     * Column names that holds values with particular meaning
     *
     * @var string[]
     */
    protected $_specialAttributes = [
        self::COLUMN_ACTION,
        self::COLUMN_WEBSITE,
        self::COLUMN_EMAIL,
        self::COLUMN_ADDRESS_ID,
        self::COLUMN_DEFAULT_BILLING,
        self::COLUMN_DEFAULT_SHIPPING,
    ];

    /**
     * Customer entity
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerEntity;

    /**
     * Entity ID incremented value
     *
     * @var int
     */
    protected $_nextEntityId;

    /**
     * Array of region parameters
     *
     * @var array
     */
    protected $_regionParameters;

    /**
     * Address attributes collection
     *
     * @var \Magento\Customer\Model\Resource\Address\Attribute\Collection
     */
    protected $_attributeCollection;

    /**
     * Collection of existent addresses
     *
     * @var \Magento\Customer\Model\Resource\Address\Collection
     */
    protected $_addressCollection;

    /**
     * Store imported row primary keys
     *
     * @var array
     */
    protected $_importedRowPks = [];

    /**
     * @var \Magento\ImportExport\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CustomerImportExport\Model\Resource\Import\Customer\StorageFactory $storageFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionColFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Resource\Address\CollectionFactory $addressColFactory
     * @param \Magento\Customer\Model\Resource\Address\Attribute\CollectionFactory $attributesFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CustomerImportExport\Model\Resource\Import\Customer\StorageFactory $storageFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionColFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Resource\Address\CollectionFactory $addressColFactory,
        \Magento\Customer\Model\Resource\Address\Attribute\CollectionFactory $attributesFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_eavConfig = $eavConfig;
        $this->_resourceHelper = $resourceHelper;
        $this->dateTime = $dateTime;

        if (!isset($data['attribute_collection'])) {
            /** @var $attributeCollection \Magento\Customer\Model\Resource\Address\Attribute\Collection */
            $attributeCollection = $attributesFactory->create();
            $attributeCollection->addSystemHiddenFilter()->addExcludeHiddenFrontendFilter();
            $data['attribute_collection'] = $attributeCollection;
        }
        parent::__construct(
            $coreData,
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $storeManager,
            $collectionFactory,
            $eavConfig,
            $storageFactory,
            $data
        );

        $this->_addressCollection = isset(
            $data['address_collection']
        ) ? $data['address_collection'] : $addressColFactory->create();
        $this->_entityTable = isset(
            $data['entity_table']
        ) ? $data['entity_table'] : $addressFactory->create()->getResource()->getEntityTable();
        $this->_regionCollection = isset(
            $data['region_collection']
        ) ? $data['region_collection'] : $regionColFactory->create();

        $this->addMessageTemplate(self::ERROR_ADDRESS_ID_IS_EMPTY, __('Customer address id column is not specified'));
        $this->addMessageTemplate(
            self::ERROR_ADDRESS_NOT_FOUND,
            __("Customer address for such customer doesn't exist")
        );
        $this->addMessageTemplate(self::ERROR_INVALID_REGION, __('Region is invalid'));
        $this->addMessageTemplate(
            self::ERROR_DUPLICATE_PK,
            __('Row with such email, website and address id combination was already found.')
        );

        $this->_initAttributes();
        $this->_initAddresses()->_initCountryRegions();
    }

    /**
     * Customer entity getter
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function _getCustomerEntity()
    {
        if (!$this->_customerEntity) {
            $this->_customerEntity = $this->_customerFactory->create();
        }
        return $this->_customerEntity;
    }

    /**
     * Get region parameters
     *
     * @return array
     */
    protected function _getRegionParameters()
    {
        if (!$this->_regionParameters) {
            $this->_regionParameters = [];
            /** @var $regionIdAttribute \Magento\Customer\Model\Attribute */
            $regionIdAttribute = $this->_eavConfig->getAttribute($this->getEntityTypeCode(), 'region_id');
            $this->_regionParameters['table'] = $regionIdAttribute->getBackend()->getTable();
            $this->_regionParameters['attribute_id'] = $regionIdAttribute->getId();
        }
        return $this->_regionParameters;
    }

    /**
     * Get next address entity ID
     *
     * @return int
     */
    protected function _getNextEntityId()
    {
        if (!$this->_nextEntityId) {
            /** @var $addressResource \Magento\Customer\Model\Resource\Address */
            $addressResource = $this->_addressFactory->create()->getResource();
            $addressTable = $addressResource->getEntityTable();
            $this->_nextEntityId = $this->_resourceHelper->getNextAutoincrement($addressTable);
        }
        return $this->_nextEntityId++;
    }

    /**
     * Initialize existent addresses data
     *
     * @return $this
     */
    protected function _initAddresses()
    {
        /** @var $address \Magento\Customer\Model\Address */
        foreach ($this->_addressCollection as $address) {
            $customerId = $address->getParentId();
            if (!isset($this->_addresses[$customerId])) {
                $this->_addresses[$customerId] = [];
            }
            $addressId = $address->getId();
            if (!in_array($addressId, $this->_addresses[$customerId])) {
                $this->_addresses[$customerId][] = $addressId;
            }
        }
        return $this;
    }

    /**
     * Initialize country regions hash for clever recognition
     *
     * @return $this
     */
    protected function _initCountryRegions()
    {
        /** @var $region \Magento\Directory\Model\Region */
        foreach ($this->_regionCollection as $region) {
            $countryNormalized = strtolower($region->getCountryId());
            $regionCode = strtolower($region->getCode());
            $regionName = strtolower($region->getDefaultName());
            $this->_countryRegions[$countryNormalized][$regionCode] = $region->getId();
            $this->_countryRegions[$countryNormalized][$regionName] = $region->getId();
            $this->_regions[$region->getId()] = $region->getDefaultName();
        }
        return $this;
    }

    /**
     * Import data rows
     *
     * @abstract
     * @return boolean
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $addUpdateRows = [];
            $attributes = [];
            $defaults = [];
            // customer default addresses (billing/shipping) data
            $deleteRowIds = [];

            foreach ($bunch as $rowNumber => $rowData) {
                // check row data
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $addUpdateResult = $this->_prepareDataForUpdate($rowData);
                    $addUpdateRows[] = $addUpdateResult['entity_row'];
                    $attributes = $this->_mergeEntityAttributes($addUpdateResult['attributes'], $attributes);
                    $defaults = $this->_mergeEntityAttributes($addUpdateResult['defaults'], $defaults);
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    $deleteRowIds[] = $rowData[self::COLUMN_ADDRESS_ID];
                }
            }

            $this->_saveAddressEntities(
                $addUpdateRows
            )->_saveAddressAttributes(
                $attributes
            )->_saveCustomerDefaults(
                $defaults
            );

            $this->_deleteAddressEntities($deleteRowIds);
        }
        return true;
    }

    /**
     * Merge attributes
     *
     * @param array $newAttributes
     * @param array $attributes
     * @return array
     */
    protected function _mergeEntityAttributes(array $newAttributes, array $attributes)
    {
        foreach ($newAttributes as $tableName => $tableData) {
            foreach ($tableData as $entityId => $entityData) {
                foreach ($entityData as $attributeId => $attributeValue) {
                    $attributes[$tableName][$entityId][$attributeId] = $attributeValue;
                }
            }
        }
        return $attributes;
    }

    /**
     * Prepare data for add/update action
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $email = strtolower($rowData[self::COLUMN_EMAIL]);
        $customerId = $this->_getCustomerId($email, $rowData[self::COLUMN_WEBSITE]);

        $regionParameters = $this->_getRegionParameters();
        $regionIdTable = $regionParameters['table'];
        $regionIdAttributeId = $regionParameters['attribute_id'];

        // get address attributes
        $addressAttributes = [];
        foreach ($this->_attributes as $attributeAlias => $attributeParams) {
            if (isset($rowData[$attributeAlias]) && strlen($rowData[$attributeAlias])) {
                if ('select' == $attributeParams['type']) {
                    $value = $attributeParams['options'][strtolower($rowData[$attributeAlias])];
                } elseif ('datetime' == $attributeParams['type']) {
                    $value = new \DateTime('@' . strtotime($rowData[$attributeAlias]));
                    $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                } else {
                    $value = $rowData[$attributeAlias];
                }
                $addressAttributes[$attributeParams['id']] = $value;
            }
        }

        // get address id
        if (isset(
            $this->_addresses[$customerId]
        ) && in_array(
            $rowData[self::COLUMN_ADDRESS_ID],
            $this->_addresses[$customerId]
        )
        ) {
            $addressId = $rowData[self::COLUMN_ADDRESS_ID];
        } else {
            $addressId = $this->_getNextEntityId();
        }

        // entity table data
        $entityRow = [
            'entity_id' => $addressId,
            'entity_type_id' => $this->getEntityTypeId(),
            'parent_id' => $customerId,
            'created_at' => $this->dateTime->now(),
            'updated_at' => $this->dateTime->now(),
        ];

        // attribute values
        $attributes = [];
        foreach ($this->_attributes as $attributeParams) {
            if (isset($addressAttributes[$attributeParams['id']])) {
                $attributes[$attributeParams['table']][$addressId][$attributeParams['id']]
                    = $addressAttributes[$attributeParams['id']];
            }
        }

        // customer default addresses
        $defaults = [];
        foreach (self::getDefaultAddressAttributeMapping() as $columnName => $attributeCode) {
            if (!empty($rowData[$columnName])) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
                $attribute = $this->_getCustomerEntity()->getAttribute($attributeCode);
                $defaults[$attribute->getBackend()->getTable()][$customerId][$attribute->getId()] = $addressId;
            }
        }

        // let's try to find region ID
        if (!empty($rowData[self::COLUMN_REGION])) {
            $countryNormalized = strtolower($rowData[self::COLUMN_COUNTRY_ID]);
            $regionNormalized = strtolower($rowData[self::COLUMN_REGION]);

            if (isset($this->_countryRegions[$countryNormalized][$regionNormalized])) {
                $regionId = $this->_countryRegions[$countryNormalized][$regionNormalized];
                $attributes[$regionIdTable][$addressId][$regionIdAttributeId] = $regionId;
                $tableName = $this->_attributes[self::COLUMN_REGION]['table'];
                $regionColumnNameId = $this->_attributes[self::COLUMN_REGION]['id'];
                $attributes[$tableName][$addressId][$regionColumnNameId] = $this->_regions[$regionId];
            }
        }

        return ['entity_row' => $entityRow, 'attributes' => $attributes, 'defaults' => $defaults];
    }

    /**
     * Update and insert data in entity table
     *
     * @param array $entityRows Rows for insert
     * @return $this
     */
    protected function _saveAddressEntities(array $entityRows)
    {
        if ($entityRows) {
            $this->_connection->insertOnDuplicate($this->_entityTable, $entityRows, ['updated_at']);
        }
        return $this;
    }

    /**
     * Save customer address attributes
     *
     * @param array $attributesData
     * @return $this
     */
    protected function _saveAddressAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $data) {
            $tableData = [];
            foreach ($data as $addressId => $attributeData) {
                foreach ($attributeData as $attributeId => $value) {
                    $tableData[] = [
                        'entity_id' => $addressId,
                        'entity_type_id' => $this->getEntityTypeId(),
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ];
                }
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }
        return $this;
    }

    /**
     * Save customer default addresses
     *
     * @param array $defaults
     * @return $this
     */
    protected function _saveCustomerDefaults(array $defaults)
    {
        /** @var $entity \Magento\Customer\Model\Customer */
        $entity = $this->_customerFactory->create();
        $entityTypeId = $entity->getEntityTypeId();

        foreach ($defaults as $tableName => $data) {
            $tableData = [];
            foreach ($data as $customerId => $attributeData) {
                foreach ($attributeData as $attributeId => $value) {
                    $tableData[] = [
                        'entity_id' => $customerId,
                        'entity_type_id' => $entityTypeId,
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ];
                }
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }
        return $this;
    }

    /**
     * Delete data from entity table
     *
     * @param array $entityRowIds Row IDs for delete
     * @return $this
     */
    protected function _deleteAddressEntities(array $entityRowIds)
    {
        if ($entityRowIds) {
            $this->_connection->delete($this->_entityTable, ['entity_id IN (?)' => $entityRowIds]);
        }
        return $this;
    }

    /**
     * EAV entity type code getter
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer_address';
    }

    /**
     * Customer default addresses column name to customer attribute mapping array
     *
     * @static
     * @return array
     */
    public static function getDefaultAddressAttributeMapping()
    {
        return self::$_defaultAddressAttributeMapping;
    }

    /**
     * Validate row for add/update action
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->_checkUniqueKey($rowData, $rowNumber)) {
            $email = strtolower($rowData[self::COLUMN_EMAIL]);
            $website = $rowData[self::COLUMN_WEBSITE];
            $addressId = $rowData[self::COLUMN_ADDRESS_ID];
            $customerId = $this->_getCustomerId($email, $website);

            if ($customerId === false) {
                $this->addRowError(self::ERROR_CUSTOMER_NOT_FOUND, $rowNumber);
            } else {
                if ($this->_checkRowDuplicate($customerId, $addressId)) {
                    $this->addRowError(self::ERROR_DUPLICATE_PK, $rowNumber);
                } else {
                    // check simple attributes
                    foreach ($this->_attributes as $attributeCode => $attributeParams) {
                        if (in_array($attributeCode, $this->_ignoredAttributes)) {
                            continue;
                        }
                        if (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                            $this->isAttributeValid($attributeCode, $attributeParams, $rowData, $rowNumber);
                        } elseif ($attributeParams['is_required'] && (!isset(
                            $this->_addresses[$customerId]
                        ) || !in_array(
                            $addressId,
                            $this->_addresses[$customerId]
                        ))
                        ) {
                            $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, $attributeCode);
                        }
                    }

                    if (isset($rowData[self::COLUMN_COUNTRY_ID]) && isset($rowData[self::COLUMN_REGION])) {
                        $countryRegions = isset(
                            $this->_countryRegions[strtolower($rowData[self::COLUMN_COUNTRY_ID])]
                        ) ? $this->_countryRegions[strtolower(
                            $rowData[self::COLUMN_COUNTRY_ID]
                        )] : [];

                        if (!empty($rowData[self::COLUMN_REGION]) && !empty($countryRegions) && !isset(
                            $countryRegions[strtolower($rowData[self::COLUMN_REGION])]
                        )
                        ) {
                            $this->addRowError(self::ERROR_INVALID_REGION, $rowNumber, self::COLUMN_REGION);
                        }
                    }
                }
            }
        }
    }

    /**
     * Validate row for delete action
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->_checkUniqueKey($rowData, $rowNumber)) {
            $email = strtolower($rowData[self::COLUMN_EMAIL]);
            $website = $rowData[self::COLUMN_WEBSITE];
            $addressId = $rowData[self::COLUMN_ADDRESS_ID];

            $customerId = $this->_getCustomerId($email, $website);
            if ($customerId === false) {
                $this->addRowError(self::ERROR_CUSTOMER_NOT_FOUND, $rowNumber);
            } else {
                if (!strlen($addressId)) {
                    $this->addRowError(self::ERROR_ADDRESS_ID_IS_EMPTY, $rowNumber);
                } elseif (!in_array($addressId, $this->_addresses[$customerId])) {
                    $this->addRowError(self::ERROR_ADDRESS_NOT_FOUND, $rowNumber);
                }
            }
        }
    }

    /**
     * Check whether row with such address id was already found in import file
     *
     * @param int $customerId
     * @param int $addressId
     * @return bool
     */
    protected function _checkRowDuplicate($customerId, $addressId)
    {
        if (isset($this->_addresses[$customerId]) && in_array($addressId, $this->_addresses[$customerId])) {
            if (!isset($this->_importedRowPks[$customerId][$addressId])) {
                $this->_importedRowPks[$customerId][$addressId] = true;
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
