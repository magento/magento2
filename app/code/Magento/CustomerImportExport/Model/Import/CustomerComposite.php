<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Import entity customer combined model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerComposite extends \Magento\ImportExport\Model\Import\AbstractEntity
{
    /**#@+
     * Particular column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_ADDRESS_PREFIX = '_address_';

    const COLUMN_DEFAULT_BILLING = '_address_default_billing_';

    const COLUMN_DEFAULT_SHIPPING = '_address_default_shipping_';

    /**#@-*/

    /**#@+
     * Data row scopes
     */
    const SCOPE_DEFAULT = 1;

    const SCOPE_ADDRESS = -1;

    /**#@-*/

    /**#@+
     * Component entity names
     */
    const COMPONENT_ENTITY_CUSTOMER = 'customer';

    const COMPONENT_ENTITY_ADDRESS = 'address';

    /**#@-*/

    /**
     * Error code for orphan rows
     */
    const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';

    /**
     * @var \Magento\CustomerImportExport\Model\Import\Customer
     */
    protected $_customerEntity;

    /**
     * @var \Magento\CustomerImportExport\Model\Import\Address
     */
    protected $_addressEntity;

    /**
     * Column names that holds values with particular meaning
     *
     * @var string[]
     */
    protected $_specialAttributes = [
        Customer::COLUMN_WEBSITE,
        Customer::COLUMN_STORE,
        self::COLUMN_DEFAULT_BILLING,
        self::COLUMN_DEFAULT_SHIPPING,
    ];

    /**
     * Permanent entity columns
     *
     * @var string[]
     */
    protected $_permanentAttributes = [
        Customer::COLUMN_EMAIL,
        Customer::COLUMN_WEBSITE,
    ];

    /**
     * Customer attributes
     *
     * @var string[]
     */
    protected $_customerAttributes = [];

    /**
     * Address attributes
     *
     * @var string[]
     */
    protected $_addressAttributes = [];

    /**
     * Website code of current customer row
     *
     * @var string
     */
    protected $_currentWebsiteCode;

    /**
     * Email of current customer
     *
     * @var string
     */
    protected $_currentEmail;

    /**
     * Next customer entity ID
     *
     * @var int
     */
    protected $_nextCustomerId;

    /**
     * DB data source models
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data[]
     */
    protected $_dataSourceModels;

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        Customer::COLUMN_DEFAULT_BILLING,
        Customer::COLUMN_DEFAULT_SHIPPING,
        Customer::COLUMN_PASSWORD,
    ];

    /**
     * {@inheritdoc}
     */
    protected $masterAttributeCode = 'email';

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory
     * @param \Magento\CustomerImportExport\Model\Import\CustomerFactory $customerFactory
     * @param \Magento\CustomerImportExport\Model\Import\AddressFactory $addressFactory
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory,
        \Magento\CustomerImportExport\Model\Import\CustomerFactory $customerFactory,
        \Magento\CustomerImportExport\Model\Import\AddressFactory $addressFactory,
        array $data = []
    ) {
        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);

        $this->addMessageTemplate(
            self::ERROR_ROW_IS_ORPHAN,
            __('Orphan rows that will be skipped due default row errors')
        );

        $this->_availableBehaviors = [
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
            \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
        ];

        // customer entity stuff
        if (isset($data['customer_data_source_model'])) {
            $this->_dataSourceModels['customer'] = $data['customer_data_source_model'];
        } else {
            $arguments = [
                'entity_type' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
            ];
            $this->_dataSourceModels['customer'] = $dataFactory->create(['arguments' => $arguments]);
        }
        if (isset($data['customer_entity'])) {
            $this->_customerEntity = $data['customer_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['customer'];
            $this->_customerEntity = $customerFactory->create(['data' => $data]);
            unset($data['data_source_model']);
        }
        $this->_initCustomerAttributes();

        // address entity stuff
        if (isset($data['address_data_source_model'])) {
            $this->_dataSourceModels['address'] = $data['address_data_source_model'];
        } else {
            $arguments = [
                'entity_type' => CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                'customer_attributes' => $this->_customerAttributes,
            ];
            $this->_dataSourceModels['address'] = $dataFactory->create(['arguments' => $arguments]);
        }
        if (isset($data['address_entity'])) {
            $this->_addressEntity = $data['address_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['address'];
            $this->_addressEntity = $addressFactory->create(['data' => $data]);
            unset($data['data_source_model']);
        }
        $this->_initAddressAttributes();

        // next customer id
        if (isset($data['next_customer_id'])) {
            $this->_nextCustomerId = $data['next_customer_id'];
        } else {
            $this->_nextCustomerId = $resourceHelper->getNextAutoincrement($this->_customerEntity->getEntityTable());
        }
    }

    /**
     * Collect customer attributes
     *
     * @return $this
     */
    protected function _initCustomerAttributes()
    {
        /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
        foreach ($this->_customerEntity->getAttributeCollection() as $attribute) {
            $this->_customerAttributes[] = $attribute->getAttributeCode();
        }

        return $this;
    }

    /**
     * Collect address attributes
     *
     * @return $this
     */
    protected function _initAddressAttributes()
    {
        /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
        foreach ($this->_addressEntity->getAttributeCollection() as $attribute) {
            $this->_addressAttributes[] = $attribute->getAttributeCode();
        }

        return $this;
    }

    /**
     * Import data rows
     *
     * @return bool
     */
    protected function _importData()
    {
        $result = $this->_customerEntity->importData();
        if ($this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            return $result && $this->_addressEntity->setCustomerAttributes($this->_customerAttributes)->importData();
        }

        return $result;
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer_composite';
    }

    /**
     * @inheritDoc
     */
    public function validateData()
    {
        //Preparing both customer and address imports for mass validation.
        $source = $this->getSource();
        $this->_customerEntity->prepareCustomerData($source);
        $source->rewind();
        $rows = [];
        foreach ($source as $row) {
            $rows[] = [
                Address::COLUMN_EMAIL => $row[Customer::COLUMN_EMAIL],
                Address::COLUMN_WEBSITE => $row[Customer::COLUMN_WEBSITE]
            ];
        }
        $source->rewind();
        $this->_addressEntity->prepareCustomerData(new \ArrayObject($rows));

        return parent::validateData();
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        $rowScope = $this->_getRowScope($rowData);
        if ($rowScope == self::SCOPE_DEFAULT) {
            if ($this->_customerEntity->validateRow($rowData, $rowNumber)) {
                $this->_currentWebsiteCode =
                    $rowData[Customer::COLUMN_WEBSITE];
                $this->_currentEmail = strtolower(
                    $rowData[Customer::COLUMN_EMAIL]
                );

                // Add new customer data into customer storage for address entity instance
                $websiteId = $this->_customerEntity->getWebsiteId($this->_currentWebsiteCode);
                if (!$this->_addressEntity->getCustomerStorage()->getCustomerId($this->_currentEmail, $websiteId)) {
                    $customerData = new \Magento\Framework\DataObject(
                        [
                            'id' => $this->_nextCustomerId,
                            'email' => $this->_currentEmail,
                            'website_id' => $websiteId,
                        ]
                    );
                    $this->_addressEntity->getCustomerStorage()->addCustomer($customerData);
                    $this->_nextCustomerId++;
                }

                return $this->_validateAddressRow($rowData, $rowNumber);
            } else {
                $this->_currentWebsiteCode = null;
                $this->_currentEmail = null;
            }
        } else {
            if (!empty($this->_currentWebsiteCode) && !empty($this->_currentEmail)) {
                return $this->_validateAddressRow($rowData, $rowNumber);
            } else {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNumber);
            }
        }

        return false;
    }

    /**
     * Validate address row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateAddressRow(array $rowData, $rowNumber)
    {
        if ($this->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            return true;
        }

        $rowData = $this->_prepareAddressRowData($rowData);
        if (empty($rowData)) {
            return true;
        } else {
            $rowData[Address::COLUMN_WEBSITE] =
                $this->_currentWebsiteCode;
            $rowData[Address::COLUMN_EMAIL] =
                $this->_currentEmail;
            $rowData[Address::COLUMN_ADDRESS_ID] = null;

            return $this->_addressEntity->validateRow($rowData, $rowNumber);
        }
    }

    /**
     * Prepare data row for address entity validation or import
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareAddressRowData(array $rowData)
    {
        $excludedAttributes = [self::COLUMN_DEFAULT_BILLING, self::COLUMN_DEFAULT_SHIPPING];

        unset(
            $rowData[Customer::COLUMN_WEBSITE],
            $rowData[Customer::COLUMN_STORE]
        );

        $result = [];
        foreach ($rowData as $key => $value) {
            if (!in_array($key, $this->_customerAttributes) && !empty($value)) {
                if (!in_array($key, $excludedAttributes)) {
                    $key = str_replace(self::COLUMN_ADDRESS_PREFIX, '', $key);
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Obtain scope of the row from row data
     *
     * @param array $rowData
     * @return int
     */
    protected function _getRowScope(array $rowData)
    {
        if (!isset($rowData[Customer::COLUMN_EMAIL])) {
            return self::SCOPE_ADDRESS;
        }
        return strlen(
            trim($rowData[Customer::COLUMN_EMAIL])
        ) ? self::SCOPE_DEFAULT : self::SCOPE_ADDRESS;
    }

    /**
     * Set data from outside to change behavior
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        parent::setParameters($parameters);

        if ($this->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
            $parameters['behavior'] = \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE;
        }

        $this->_customerEntity->setParameters($parameters);
        $this->_addressEntity->setParameters($parameters);

        return $this;
    }

    /**
     * Source model setter
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @return \Magento\ImportExport\Model\Import\AbstractEntity
     */
    public function setSource(\Magento\ImportExport\Model\Import\AbstractSource $source)
    {
        $this->_customerEntity->setSource($source);
        $this->_addressEntity->setSource($source);

        return parent::setSource($source);
    }

    /**
     * Returns number of checked entities
     *
     * @return int
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_customerEntity->getProcessedEntitiesCount() +
            $this->_addressEntity->getProcessedEntitiesCount();
    }

    /**
     * Is attribute contains particular data (not plain customer attribute)
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeParticular($attributeCode)
    {
        if (in_array(str_replace(self::COLUMN_ADDRESS_PREFIX, '', $attributeCode), $this->_addressAttributes)) {
            return true;
        } else {
            return parent::isAttributeParticular($attributeCode);
        }
    }

    /**
     * Prepare validated row data for saving to db
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData['_scope'] = $this->_getRowScope($rowData);
        $rowData[Address::COLUMN_WEBSITE] =
            $this->_currentWebsiteCode;
        $rowData[Address::COLUMN_EMAIL] = $this->_currentEmail;
        $rowData[Address::COLUMN_ADDRESS_ID] = null;

        return parent::_prepareRowForDb($rowData);
    }

    /**
     * @inheritDoc
     */
    public function getValidColumnNames()
    {
        $this->validColumnNames = array_merge(
            $this->validColumnNames,
            $this->_customerAttributes,
            $this->_addressAttributes,
            $this->_customerEntity->getValidColumnNames()
        );

        return $this->validColumnNames;
    }
}
