<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Customer address import
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends AbstractCustomer
{
    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection::class;

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

    const COLUMN_POSTCODE = 'postcode';

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

    /**#@-*/
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
     * @var \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection
     */
    protected $_attributeCollection;

    /**
     * Collection of existent addresses
     *
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    protected $_addressCollection;

    /**
     * Store imported row primary keys
     *
     * @var array
     */
    protected $_importedRowPks = [];

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
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
     * Customer attributes
     *
     * @var string[]
     */
    protected $_customerAttributes = [];

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        "region_id", "vat_is_valid", "vat_request_date", "vat_request_id", "vat_request_success"
    ];

    /**
     * @var \Magento\Customer\Model\Address\Validator\Postcode
     * @since 2.1.0
     */
    protected $postcodeValidator;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionColFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressColFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory $attributesFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\Address\Validator\Postcode $postcodeValidator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionColFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressColFactory,
        \Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory $attributesFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Address\Validator\Postcode $postcodeValidator,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_eavConfig = $eavConfig;
        $this->_resourceHelper = $resourceHelper;
        $this->dateTime = $dateTime;
        $this->postcodeValidator = $postcodeValidator;

        if (!isset($data['attribute_collection'])) {
            /** @var $attributeCollection \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection */
            $attributeCollection = $attributesFactory->create();
            $attributeCollection->addSystemHiddenFilter()->addExcludeHiddenFrontendFilter();
            $data['attribute_collection'] = $attributeCollection;
        }
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
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
            __('We can\'t find that customer address.')
        );
        $this->addMessageTemplate(self::ERROR_INVALID_REGION, __('Please enter a valid region.'));
        $this->addMessageTemplate(
            self::ERROR_DUPLICATE_PK,
            __('We found another row with this email, website and address ID combination.')
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
     * Get next address entity ID
     *
     * @return int
     */
    protected function _getNextEntityId()
    {
        if (!$this->_nextEntityId) {
            /** @var $addressResource \Magento\Customer\Model\ResourceModel\Address */
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $newRows = [];
            $updateRows = [];
            $attributes = [];
            $defaults = [];
            // customer default addresses (billing/shipping) data
            $deleteRowIds = [];

            foreach ($bunch as $rowNumber => $rowData) {
                // check row data
                if ($this->_isOptionalAddressEmpty($rowData) || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $addUpdateResult = $this->_prepareDataForUpdate($rowData);
                    if ($addUpdateResult['entity_row_new']) {
                        $newRows[] = $addUpdateResult['entity_row_new'];
                    }
                    if ($addUpdateResult['entity_row_update']) {
                        $updateRows[] = $addUpdateResult['entity_row_update'];
                    }
                    $attributes = $this->_mergeEntityAttributes($addUpdateResult['attributes'], $attributes);
                    $defaults = $this->_mergeEntityAttributes($addUpdateResult['defaults'], $defaults);
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    $deleteRowIds[] = $rowData[self::COLUMN_ADDRESS_ID];
                }
            }
            $this->updateItemsCounterStats($newRows, $updateRows, $deleteRowIds);

            $this->_saveAddressEntities(
                $newRows,
                $updateRows
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $email = strtolower($rowData[self::COLUMN_EMAIL]);
        $customerId = $this->_getCustomerId($email, $rowData[self::COLUMN_WEBSITE]);
        // entity table data
        $entityRowNew = [];
        $entityRowUpdate = [];
        // attribute values
        $attributes = [];
        // customer default addresses
        $defaults = [];
        $newAddress = true;
        // get address id
        if (isset(
            $this->_addresses[$customerId]
        ) && in_array(
            $rowData[self::COLUMN_ADDRESS_ID],
            $this->_addresses[$customerId]
        )
        ) {
            $newAddress = false;
            $addressId = $rowData[self::COLUMN_ADDRESS_ID];
        } else {
            $addressId = $this->_getNextEntityId();
        }
        $entityRow = [
            'entity_id' => $addressId,
            'parent_id' => $customerId,
            'updated_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
        ];
        foreach ($this->_attributes as $attributeAlias => $attributeParams) {
            if (array_key_exists($attributeAlias, $rowData)) {
                if (!strlen($rowData[$attributeAlias])) {
                    if ($newAddress) {
                        $value = null;
                    } else {
                        continue;
                    }
                } elseif ($newAddress && !strlen($rowData[$attributeAlias])) {
                } elseif ('select' == $attributeParams['type']) {
                    $value = $attributeParams['options'][strtolower($rowData[$attributeAlias])];
                } elseif ('datetime' == $attributeParams['type']) {
                    $value = (new \DateTime())->setTimestamp(strtotime($rowData[$attributeAlias]));
                    $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                } elseif ('multiselect' == $attributeParams['type']) {
                    $separator = isset($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR]) ?
                        $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR] :
                        Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
                    $value = str_replace(
                        $separator,
                        Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        $rowData[$attributeAlias]
                    );
                } else {
                    $value = $rowData[$attributeAlias];
                }
                if ($attributeParams['is_static']) {
                    $entityRow[$attributeAlias] = $value;
                } else {
                    $attributes[$attributeParams['table']][$addressId][$attributeParams['id']]= $value;
                }
            }
        }
        foreach (self::getDefaultAddressAttributeMapping() as $columnName => $attributeCode) {
            if (!empty($rowData[$columnName])) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
                $table = $this->_getCustomerEntity()->getResource()->getTable('customer_entity');
                $defaults[$table][$customerId][$attributeCode] = $addressId;
            }
        }
        // let's try to find region ID
        $entityRow['region_id'] = null;
        if (!empty($rowData[self::COLUMN_REGION])) {
            $countryNormalized = strtolower($rowData[self::COLUMN_COUNTRY_ID]);
            $regionNormalized = strtolower($rowData[self::COLUMN_REGION]);

            if (isset($this->_countryRegions[$countryNormalized][$regionNormalized])) {
                $regionId = $this->_countryRegions[$countryNormalized][$regionNormalized];
                $entityRow[self::COLUMN_REGION] = $this->_regions[$regionId];
                $entityRow['region_id'] = $regionId;
            }
        }
        if ($newAddress) {
            $entityRowNew = $entityRow;
            $entityRowNew['created_at'] =
                (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        } else {
            $entityRowUpdate = $entityRow;
        }

        return [
            'entity_row_new' => $entityRowNew,
            'entity_row_update' => $entityRowUpdate,
            'attributes' => $attributes,
            'defaults' => $defaults
        ];
    }

    /**
     * Update and insert data in entity table
     *
     * @param array $addRows Rows for insert
     * @param array $updateRows Rows for update
     * @return $this
     */
    protected function _saveAddressEntities(array $addRows, array $updateRows)
    {
        if ($addRows) {
            $this->_connection->insertMultiple($this->_entityTable, $addRows);
        }
        if ($updateRows) {
            //list of updated fields can be different for addresses. We can not use insertOnDuplicate for whole rows.
            foreach ($updateRows as $row) {
                $fields = array_diff(array_keys($row), ['entity_id', 'parent_id', 'created_at']);
                $this->_connection->insertOnDuplicate($this->_entityTable, $row, $fields);
            }
        }
        return $this;
    }

    /**
     * Save custom customer address attributes
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _saveCustomerDefaults(array $defaults)
    {
        foreach ($defaults as $tableName => $data) {
            foreach ($data as $customerId => $defaultsData) {
                $data = array_merge(
                    ['entity_id' => $customerId],
                    $defaultsData
                );
                $this->_connection->insertOnDuplicate($tableName, $data, array_keys($defaultsData));
            }
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
     * check if address for import is empty (for customer composite mode)
     *
     * @param array $rowData
     * @return array
     */
    protected function _isOptionalAddressEmpty(array $rowData)
    {
        if (empty($this->_customerAttributes)) {
            return false;
        }
        unset(
            $rowData[Customer::COLUMN_WEBSITE],
            $rowData[Customer::COLUMN_STORE],
            $rowData['_email']
        );

        foreach ($rowData as $key => $value) {
            if (!in_array($key, $this->_customerAttributes) && !empty($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate row for add/update action
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
                            $multiSeparator = isset($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR]) ?
                                $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR] :
                                Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
                            $this->isAttributeValid(
                                $attributeCode,
                                $attributeParams,
                                $rowData,
                                $rowNumber,
                                $multiSeparator
                            );
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

                    if (isset($rowData[self::COLUMN_POSTCODE])
                        && isset($rowData[self::COLUMN_COUNTRY_ID])
                        && !$this->postcodeValidator->isValid(
                            $rowData[self::COLUMN_COUNTRY_ID],
                            $rowData[self::COLUMN_POSTCODE]
                        )
                    ) {
                        $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, self::COLUMN_POSTCODE);
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

    /**
     * set customer attributes
     *
     * @param array $customerAttributes
     * @return $this
     */
    public function setCustomerAttributes($customerAttributes)
    {
        $this->_customerAttributes = $customerAttributes;
        return $this;
    }
}
