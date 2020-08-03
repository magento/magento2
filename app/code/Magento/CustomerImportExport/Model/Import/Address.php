<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites as CountryWithWebsitesSource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Store\Model\Store;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Address\Storage as AddressStorage;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\Customer\Model\Indexer\Processor;

/**
 * Customer address import
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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

    const COLUMN_REGION_ID = 'region_id';

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
     * Region collection instance
     *
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    private $_regionCollection;

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
     * @deprecated 100.3.4 field not in use
     */
    protected $_regionParameters;

    /**
     * Address attributes collection
     *
     * @var \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection
     */
    protected $_attributeCollection;

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
     * @deprecated 100.3.4 field not-in use
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     * @deprecated 100.3.4 not utilized anymore
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @deprecated 100.3.4 the property isn't used
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
     */
    protected $postcodeValidator;

    /**
     * @var CountryWithWebsitesSource
     */
    private $countryWithWebsites;

    /**
     * Options for certain attributes sorted by websites.
     *
     * @var array[][] With path as <attributeCode> => <websiteID> => options[].
     */
    private $optionsByWebsite = [];

    /**
     * @var AddressStorage
     */
    private $addressStorage;

    /**
     * @var Processor
     */
    private $indexerProcessor;

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
     * @param \Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory $attributesFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\Address\Validator\Postcode $postcodeValidator
     * @param array $data
     * @param CountryWithWebsitesSource|null $countryWithWebsites
     * @param AddressStorage|null $addressStorage
     * @param Processor $indexerProcessor
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
        \Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory $attributesFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Address\Validator\Postcode $postcodeValidator,
        array $data = [],
        ?CountryWithWebsitesSource $countryWithWebsites = null,
        ?AddressStorage $addressStorage = null,
        ?Processor $indexerProcessor = null
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_eavConfig = $eavConfig;
        $this->_resourceHelper = $resourceHelper;
        $this->dateTime = $dateTime;
        $this->postcodeValidator = $postcodeValidator;
        $this->countryWithWebsites = $countryWithWebsites ?:
            ObjectManager::getInstance()->get(CountryWithWebsitesSource::class);

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
        $this->addressStorage = $addressStorage
            ?: ObjectManager::getInstance()->get(AddressStorage::class);

        $this->indexerProcessor = $indexerProcessor
            ?: ObjectManager::getInstance()->get(Processor::class);

        $this->_initAttributes();
        $this->_initCountryRegions();
    }

    /**
     * @inheritDoc
     */
    public function getAttributeOptions(AbstractAttribute $attribute, array $indexAttributes = [])
    {
        $standardOptions = parent::getAttributeOptions($attribute, $indexAttributes);

        if ($attribute->getAttributeCode() === 'country_id') {
            //If we want to get available options for country field then we have to use alternative source
            // to get actual data for each website.
            $options = $this->countryWithWebsites->getAllOptions();
            //Available country options now will be sorted by websites.
            $code = $attribute->getAttributeCode();
            $websiteOptions = [Store::DEFAULT_STORE_ID => $standardOptions];
            //Sorting options by website.
            foreach ($options as $option) {
                if (array_key_exists('website_ids', $option)) {
                    foreach ($option['website_ids'] as $websiteId) {
                        if (!array_key_exists($websiteId, $websiteOptions)) {
                            $websiteOptions[$websiteId] = [];
                        }
                        $optionId = mb_strtolower($option['value']);
                        $websiteOptions[$websiteId][$optionId] = $option['value'];
                    }
                }
            }
            //Storing sorted
            $this->optionsByWebsite[$code] = $websiteOptions;
        }

        return $standardOptions;
    }

    /**
     * Attributes' data may vary depending on website settings,
     * this method adjusts an attribute's data from $this->_attributes to
     * website-specific data.
     *
     * @param array $attributeData Data from $this->_attributes.
     * @param int $websiteId
     *
     * @return array Adjusted data in the same format.
     */
    private function adjustAttributeDataForWebsite(array $attributeData, int $websiteId): array
    {
        if ($attributeData['code'] === 'country_id') {
            $attributeOptions = $this->optionsByWebsite[$attributeData['code']];
            if (array_key_exists($websiteId, $attributeOptions)) {
                $attributeData['options'] = $attributeOptions[$websiteId];
            }
        }

        return $attributeData;
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
            $this->_nextEntityId = $this->_resourceHelper->getNextAutoincrement($this->_entityTable);
        }
        return $this->_nextEntityId++;
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
     * Pre-loading customers for existing customers checks in order
     * to perform mass validation/import efficiently.
     * Also loading existing addresses for requested customers.
     *
     * @param array|AbstractSource $rows Each row must contain data from columns email
     * and website code.
     *
     * @return void
     */
    public function prepareCustomerData($rows): void
    {
        $customersPresent = [];
        foreach ($rows as $rowData) {
            $email = $rowData[static::COLUMN_EMAIL] ?? null;
            $websiteId = isset($rowData[static::COLUMN_WEBSITE])
                ? $this->getWebsiteId($rowData[static::COLUMN_WEBSITE]) : false;
            if ($email && $websiteId !== false) {
                $customersPresent[] = [
                    'email' => $email,
                    'website_id' => $websiteId,
                ];
            }
        }
        $this->getCustomerStorage()->prepareCustomers($customersPresent);

        $ids = [];
        foreach ($customersPresent as $customerData) {
            $id = $this->getCustomerStorage()->getLoadedCustomerId(
                $customerData['email'],
                $customerData['website_id']
            );
            if ($id) {
                $ids[] = $id;
            }
        }

        $this->addressStorage->prepareAddresses($ids);
    }

    /**
     * @inheritDoc
     */
    public function validateData()
    {
        $this->prepareCustomerData($this->getSource());

        return parent::validateData();
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
        //Preparing data for mass validation/import.
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $rows = array_merge($rows, $bunch);
        }
        $this->prepareCustomerData($rows);
        unset($bunch, $rows);
        $this->_dataSourceModel->getIterator()->rewind();

        //Importing
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

            $this->_saveAddressEntities($newRows, $updateRows)
                ->_saveAddressAttributes($attributes)
                ->_saveCustomerDefaults($defaults);

            $this->_deleteAddressEntities($deleteRowIds);
        }
        $this->indexerProcessor->markIndexerAsInvalid();
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
    protected function _prepareDataForUpdate(array $rowData):array
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
        if ($rowData[self::COLUMN_ADDRESS_ID]
            && $customerId
            && $this->addressStorage->doesExist(
                (int) $rowData[self::COLUMN_ADDRESS_ID],
                $customerId
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
        $websiteId = $this->_websiteCodeToId[$rowData[self::COLUMN_WEBSITE]];

        foreach ($this->_attributes as $attributeAlias => $attributeParams) {
            if (array_key_exists($attributeAlias, $rowData)) {
                $attributeParams = $this->adjustAttributeDataForWebsite($attributeParams, $websiteId);

                $value = $rowData[$attributeAlias];

                if (!strlen($rowData[$attributeAlias])) {
                    if (!$newAddress) {
                        continue;
                    }

                    $value = null;
                } elseif (in_array($attributeParams['type'], ['select', 'boolean', 'datetime', 'multiselect'])) {
                    $value = $this->getValueByAttributeType($rowData[$attributeAlias], $attributeParams);
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
                $table = $this->_getCustomerEntity()->getResource()->getTable('customer_entity');
                $defaults[$table][$customerId][$attributeCode] = $addressId;
            }
        }

        if (!empty($entityRow[self::COLUMN_REGION]) && !empty($entityRow[self::COLUMN_COUNTRY_ID])) {
            $entityRow[self::COLUMN_REGION_ID] = $this->getCountryRegionId(
                $entityRow[self::COLUMN_COUNTRY_ID],
                $entityRow[self::COLUMN_REGION]
            );
            // override the region name with its proper name if region ID is found
            $entityRow[self::COLUMN_REGION] = $entityRow[self::COLUMN_REGION_ID] !== null
                ? $this->_regions[$entityRow[self::COLUMN_REGION_ID]]
                : $entityRow[self::COLUMN_REGION];
        } elseif ($newAddress) {
            $entityRow[self::COLUMN_REGION_ID] = null;
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
     * Process row data, based on attirbute type
     *
     * @param string $rowAttributeData
     * @param array $attributeParams
     * @return \DateTime|int|string
     * @throws \Exception
     */
    protected function getValueByAttributeType(string $rowAttributeData, array $attributeParams)
    {
        $multiSeparator = $this->getMultipleValueSeparator();
        $value = $rowAttributeData;
        switch ($attributeParams['type']) {
            case 'select':
            case 'boolean':
                $value = $this->getSelectAttrIdByValue($attributeParams, mb_strtolower($rowAttributeData));
                break;
            case 'datetime':
                $value = (new \DateTime())->setTimestamp(strtotime($rowAttributeData));
                $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                break;
            case 'multiselect':
                $ids = [];
                foreach (explode($multiSeparator, mb_strtolower($rowAttributeData)) as $subValue) {
                    $ids[] = $this->getSelectAttrIdByValue($attributeParams, $subValue);
                }
                $value = implode(',', $ids);
                break;
        }

        return $value;
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
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
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
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getDefaultAddressAttributeMapping()
    {
        return self::$_defaultAddressAttributeMapping;
    }
    // phpcs:enable

    /**
     * Check if address for import is empty (for customer composite mode)
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
     */
    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $multiSeparator = $this->getMultipleValueSeparator();
        if ($this->_checkUniqueKey($rowData, $rowNumber)) {
            $email = strtolower($rowData[self::COLUMN_EMAIL]);
            $website = $rowData[self::COLUMN_WEBSITE];
            $addressId = (int) $rowData[self::COLUMN_ADDRESS_ID];
            $customerId = $this->_getCustomerId($email, $website);

            if ($customerId === false) {
                $this->addRowError(self::ERROR_CUSTOMER_NOT_FOUND, $rowNumber);
            } elseif ($this->_checkRowDuplicate($customerId, $addressId)) {
                $this->addRowError(self::ERROR_DUPLICATE_PK, $rowNumber);
            } else {
                // check simple attributes
                foreach ($this->_attributes as $attributeCode => $attributeParams) {
                    $websiteId = $this->_websiteCodeToId[$website];
                    $attributeParams = $this->adjustAttributeDataForWebsite($attributeParams, $websiteId);

                    if (in_array($attributeCode, $this->_ignoredAttributes)) {
                        continue;
                    } elseif (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                        $this->isAttributeValid(
                            $attributeCode,
                            $attributeParams,
                            $rowData,
                            $rowNumber,
                            $multiSeparator
                        );
                    } elseif ($attributeParams['is_required']
                        && !$this->addressStorage->doesExist(
                            $addressId,
                            $customerId
                        )
                    ) {
                        $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, $attributeCode);
                    }
                }

                if (!empty($rowData[self::COLUMN_COUNTRY_ID])) {
                    if (isset($rowData[self::COLUMN_POSTCODE])
                        && !$this->postcodeValidator->isValid(
                            $rowData[self::COLUMN_COUNTRY_ID],
                            $rowData[self::COLUMN_POSTCODE]
                        )
                    ) {
                        $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, self::COLUMN_POSTCODE);
                    }

                    if (!empty($rowData[self::COLUMN_REGION])
                        && count($this->getCountryRegions($rowData[self::COLUMN_COUNTRY_ID])) > 0
                        && null === $this->getCountryRegionId(
                            $rowData[self::COLUMN_COUNTRY_ID],
                            $rowData[self::COLUMN_REGION]
                        )
                    ) {
                        $this->addRowError(self::ERROR_INVALID_REGION, $rowNumber, self::COLUMN_REGION);
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
            $addressId = (int) $rowData[self::COLUMN_ADDRESS_ID];

            $customerId = $this->_getCustomerId($email, $website);
            if ($customerId === false) {
                $this->addRowError(self::ERROR_CUSTOMER_NOT_FOUND, $rowNumber);
            } elseif (!$addressId) {
                $this->addRowError(self::ERROR_ADDRESS_ID_IS_EMPTY, $rowNumber);
            } elseif (!$this->addressStorage->doesExist(
                $addressId,
                $customerId
            )) {
                $this->addRowError(self::ERROR_ADDRESS_NOT_FOUND, $rowNumber);
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
    protected function _checkRowDuplicate(int $customerId, int $addressId)
    {
        $isAddressExists = $this->addressStorage->doesExist(
            $addressId,
            $customerId
        );

        $isPkRowSet = isset($this->_importedRowPks[$customerId][$addressId]);

        if ($isAddressExists && !$isPkRowSet) {
            $this->_importedRowPks[$customerId][$addressId] = true;
        }

        return $isAddressExists && $isPkRowSet;
    }

    /**
     * Set customer attributes
     *
     * @param array $customerAttributes
     * @return $this
     */
    public function setCustomerAttributes($customerAttributes)
    {
        $this->_customerAttributes = $customerAttributes;
        return $this;
    }

    /**
     * Get RegionID from the initialized data
     *
     * @param string $countryId
     * @param string $region
     * @return int|null
     */
    private function getCountryRegionId(string $countryId, string $region): ?int
    {
        $countryRegions = $this->getCountryRegions($countryId);
        return $countryRegions[strtolower($region)] ?? null;
    }

    /**
     * Get country regions
     *
     * @param string $countryId
     * @return array
     */
    private function getCountryRegions(string $countryId): array
    {
        return $this->_countryRegions[strtolower($countryId)] ?? [];
    }
}
