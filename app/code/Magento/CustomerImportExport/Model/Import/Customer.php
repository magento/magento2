<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer extends AbstractCustomer
{
    /**
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Magento\Customer\Model\ResourceModel\Attribute\Collection';

    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL = 'email';

    const COLUMN_STORE = '_store';

    const COLUMN_PASSWORD = 'password';

    /**#@-*/

    /**#@+
     * Error codes
     */
    const ERROR_DUPLICATE_EMAIL_SITE = 'duplicateEmailSite';

    const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';

    const ERROR_INVALID_STORE = 'invalidStore';

    const ERROR_EMAIL_SITE_NOT_FOUND = 'emailSiteNotFound';

    const ERROR_PASSWORD_LENGTH = 'passwordLength';

    /**#@-*/

    /**#@+
     * Keys which used to build result data array for future update
     */
    const ENTITIES_TO_CREATE_KEY = 'entities_to_create';

    const ENTITIES_TO_UPDATE_KEY = 'entities_to_update';

    const ATTRIBUTES_TO_SAVE_KEY = 'attributes_to_save';

    /**#@-*/

    /**
     * Minimum password length
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * Default customer group
     */
    const DEFAULT_GROUP_ID = 1;

    /**
     * Customers information from import file
     *
     * @var array
     */
    protected $_newCustomers = [];

    /**
     * Array of attribute codes which will be ignored in validation and import procedures.
     * For example, when entity attribute has own validation and import procedures
     * or just to deny this attribute processing.
     *
     * @var string[]
     */
    protected $_ignoredAttributes = ['website_id', 'store_id'];

    /**
     * Customer entity DB table name.
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Customer model
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * Id of next customer entity row
     *
     * @var int
     */
    protected $_nextEntityId;

    /**
     * Address attributes collection
     *
     * @var \Magento\Customer\Model\ResourceModel\Attribute\Collection
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * {@inheritdoc}
     */
    protected $masterAttributeCode = 'email';

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::COLUMN_DEFAULT_BILLING,
        self::COLUMN_DEFAULT_SHIPPING,
        self::COLUMN_PASSWORD,
    ];

    /**
     * Customer fields in file
     */
    protected $customerFields = [
        CustomerInterface::GROUP_ID,
        CustomerInterface::STORE_ID,
        CustomerInterface::UPDATED_AT,
        CustomerInterface::CREATED_AT,
        CustomerInterface::CREATED_IN,
        CustomerInterface::PREFIX,
        CustomerInterface::FIRSTNAME,
        CustomerInterface::MIDDLENAME,
        CustomerInterface::LASTNAME,
        CustomerInterface::SUFFIX,
        CustomerInterface::DOB,
        'password_hash',
        CustomerInterface::TAXVAT,
        CustomerInterface::CONFIRMATION,
        CustomerInterface::GENDER,
        'rp_token',
        'rp_token_created_at',
        'failures_num',
        'first_failure',
        'lock_expires',
    ];

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
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
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
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_resourceHelper = $resourceHelper;

        if (isset($data['attribute_collection'])) {
            $this->_attributeCollection = $data['attribute_collection'];
            unset($data['attribute_collection']);
        } else {
            $this->_attributeCollection = $attrCollectionFactory->create();
            $this->_attributeCollection->addSystemHiddenFilterWithPasswordHash();
            $data['attribute_collection'] = $this->_attributeCollection;
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

        $this->_specialAttributes[] = self::COLUMN_WEBSITE;
        $this->_specialAttributes[] = self::COLUMN_STORE;
        $this->_permanentAttributes[] = self::COLUMN_EMAIL;
        $this->_permanentAttributes[] = self::COLUMN_WEBSITE;
        $this->_indexValueAttributes[] = 'group_id';

        $this->addMessageTemplate(
            self::ERROR_DUPLICATE_EMAIL_SITE,
            __('This email is found more than once in the import file.')
        );
        $this->addMessageTemplate(
            self::ERROR_ROW_IS_ORPHAN,
            __('Orphan rows that will be skipped due default row errors')
        );
        $this->addMessageTemplate(
            self::ERROR_INVALID_STORE,
            __('Invalid value in Store column (store does not exists?)')
        );
        $this->addMessageTemplate(
            self::ERROR_EMAIL_SITE_NOT_FOUND,
            __('We can\'t find that email and website combination.')
        );
        $this->addMessageTemplate(self::ERROR_PASSWORD_LENGTH, __('Please enter a password with a valid length.'));

        $this->_initStores(true)->_initAttributes();

        $this->_customerModel = $customerFactory->create();
        /** @var $customerResource \Magento\Customer\Model\ResourceModel\Customer */
        $customerResource = $this->_customerModel->getResource();
        $this->_entityTable = $customerResource->getEntityTable();
    }

    /**
     * Update and insert data in entity table
     *
     * @param array $entitiesToCreate Rows for insert
     * @param array $entitiesToUpdate Rows for update
     * @return $this
     */
    protected function _saveCustomerEntities(array $entitiesToCreate, array $entitiesToUpdate)
    {
        if ($entitiesToCreate) {
            $this->_connection->insertMultiple($this->_entityTable, $entitiesToCreate);
        }

        if ($entitiesToUpdate) {
            $this->_connection->insertOnDuplicate(
                $this->_entityTable,
                $entitiesToUpdate,
                $this->customerFields
            );
        }

        return $this;
    }

    /**
     * Save customer attributes.
     *
     * @param array $attributesData
     * @return $this
     */
    protected function _saveCustomerAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $data) {
            $tableData = [];

            foreach ($data as $customerId => $attributeData) {
                foreach ($attributeData as $attributeId => $value) {
                    $tableData[] = [
                        'entity_id' => $customerId,
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
     * Delete list of customers
     *
     * @param array $entitiesToDelete customers id list
     * @return $this
     */
    protected function _deleteCustomerEntities(array $entitiesToDelete)
    {
        $condition = $this->_connection->quoteInto('entity_id IN (?)', $entitiesToDelete);
        $this->_connection->delete($this->_entityTable, $condition);

        return $this;
    }

    /**
     * Retrieve next customer entity id
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
     * Prepare customer data for update
     *
     * @param array $rowData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $attributesToSave = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $emailInLowercase = strtolower($rowData[self::COLUMN_EMAIL]);
        $newCustomer = false;
        $entityId = $this->_getCustomerId($emailInLowercase, $rowData[self::COLUMN_WEBSITE]);
        if (!$entityId) {
            // create
            $newCustomer = true;
            $entityId = $this->_getNextEntityId();
            $this->_newCustomers[$emailInLowercase][$rowData[self::COLUMN_WEBSITE]] = $entityId;
        }

        $entityRow = [
            'group_id' => empty($rowData['group_id']) ? self::DEFAULT_GROUP_ID : $rowData['group_id'],
            'store_id' => empty($rowData[self::COLUMN_STORE]) ? 0 : $this->_storeCodeToId[$rowData[self::COLUMN_STORE]],
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'entity_id' => $entityId,
        ];

        // password change/set
        if (isset($rowData['password']) && strlen($rowData['password'])) {
            $rowData['password_hash'] = $this->_customerModel->hashPassword($rowData['password']);
        }

        // attribute values
        foreach (array_intersect_key($rowData, $this->_attributes) as $attributeCode => $value) {
            if ($newCustomer && !strlen($value)) {
                continue;
            }

            $attributeParameters = $this->_attributes[$attributeCode];
            if ('select' == $attributeParameters['type']) {
                $value = isset($attributeParameters['options'][strtolower($value)])
                    ? $attributeParameters['options'][strtolower($value)]
                    : 0;
            } elseif ('datetime' == $attributeParameters['type'] && !empty($value)) {
                $value = (new \DateTime())->setTimestamp(strtotime($value));
                $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }

            if (!$this->_attributes[$attributeCode]['is_static']) {
                /** @var $attribute \Magento\Customer\Model\Attribute */
                $attribute = $this->_customerModel->getAttribute($attributeCode);
                $backendModel = $attribute->getBackendModel();
                if ($backendModel
                    && $attribute->getFrontendInput() != 'select'
                    && $attribute->getFrontendInput() != 'datetime') {
                    $attribute->getBackend()->beforeSave($this->_customerModel->setData($attributeCode, $value));
                    $value = $this->_customerModel->getData($attributeCode);
                }
                $attributesToSave[$attribute->getBackend()
                    ->getTable()][$entityId][$attributeParameters['id']] = $value;

                // restore 'backend_model' to avoid default setting
                $attribute->setBackendModel($backendModel);
            } else {
                $entityRow[$attributeCode] = $value;
            }
        }

        if ($newCustomer) {
            // create
            $entityRow['website_id'] = $this->_websiteCodeToId[$rowData[self::COLUMN_WEBSITE]];
            $entityRow['email'] = $emailInLowercase;
            $entityRow['is_active'] = 1;
            $entitiesToCreate[] = $entityRow;
        } else {
            // edit
            $entitiesToUpdate[] = $entityRow;
        }

        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate,
            self::ATTRIBUTES_TO_SAVE_KEY => $attributesToSave
        ];
    }

    /**
     * Import data rows
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
            $attributesToSave = [];

            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    $entitiesToDelete[] = $this->_getCustomerId(
                        $rowData[self::COLUMN_EMAIL],
                        $rowData[self::COLUMN_WEBSITE]
                    );
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $processedData = $this->_prepareDataForUpdate($rowData);
                    $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                    $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                    foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                        if (!isset($attributesToSave[$tableName])) {
                            $attributesToSave[$tableName] = [];
                        }
                        $attributesToSave[$tableName] = array_diff_key(
                            $attributesToSave[$tableName],
                            $customerAttributes
                        ) + $customerAttributes;
                    }
                }
            }
            $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);
            /**
             * Save prepared data
             */
            if ($entitiesToCreate || $entitiesToUpdate) {
                $this->_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
            }
            if ($attributesToSave) {
                $this->_saveCustomerAttributes($attributesToSave);
            }
            if ($entitiesToDelete) {
                $this->_deleteCustomerEntities($entitiesToDelete);
            }
        }

        return true;
    }

    /**
     * EAV entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->_attributeCollection->getEntityTypeCode();
    }

    /**
     * Validate row data for add/update behaviour
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

            if (isset($this->_newCustomers[strtolower($rowData[self::COLUMN_EMAIL])][$website])) {
                $this->addRowError(self::ERROR_DUPLICATE_EMAIL_SITE, $rowNumber);
            }
            $this->_newCustomers[$email][$website] = false;

            if (!empty($rowData[self::COLUMN_STORE]) && !isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
            }
            // check password
            if (isset(
                $rowData['password']
            ) && strlen(
                $rowData['password']
            ) && $this->string->strlen(
                $rowData['password']
            ) < self::MIN_PASSWORD_LENGTH
            ) {
                $this->addRowError(self::ERROR_PASSWORD_LENGTH, $rowNumber);
            }
            // check simple attributes
            foreach ($this->_attributes as $attributeCode => $attributeParams) {
                if (in_array($attributeCode, $this->_ignoredAttributes)) {
                    continue;
                }
                if (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                    $this->isAttributeValid($attributeCode, $attributeParams, $rowData, $rowNumber);
                } elseif ($attributeParams['is_required'] && !$this->_getCustomerId($email, $website)) {
                    $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, $attributeCode);
                }
            }
        }
    }

    /**
     * Validate row data for delete behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->_checkUniqueKey($rowData, $rowNumber)) {
            if (!$this->_getCustomerId($rowData[self::COLUMN_EMAIL], $rowData[self::COLUMN_WEBSITE])) {
                $this->addRowError(self::ERROR_CUSTOMER_NOT_FOUND, $rowNumber);
            }
        }
    }

    /**
     * Entity table name getter
     *
     * @return string
     */
    public function getEntityTable()
    {
        return $this->_entityTable;
    }

    /**
     * @inheritDoc
     */
    public function getValidColumnNames()
    {
        $this->validColumnNames = array_merge(
            $this->validColumnNames,
            $this->customerFields
        );

        return $this->validColumnNames;
    }
}
