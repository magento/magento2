<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;


/**
 * Import entity abstract model
 */
abstract class AbstractEntity
{
    /**
     * Custom row import behavior column name
     */
    const COLUMN_ACTION = '_action';

    /**
     * Value in custom column for delete behaviour
     */
    const COLUMN_ACTION_VALUE_DELETE = 'delete';

    /**#@+
     * XML paths to parameters
     */
    const XML_PATH_BUNCH_SIZE = 'import/format_v2/bunch_size';

    const XML_PATH_PAGE_SIZE = 'import/format_v2/page_size';

    /**#@-*/

    /**#@+
     * Database constants
     */
    const DB_MAX_VARCHAR_LENGTH = 256;

    const DB_MAX_TEXT_LENGTH = 65536;

    /**#@-*/

    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Has data process validation done?
     *
     * @var bool
     */
    protected $_dataValidated = false;

    /**
     * DB data source model
     *
     * @var \Magento\ImportExport\Model\Resource\Import\Data
     */
    protected $_dataSourceModel;

    /**
     * Error codes with arrays of corresponding row numbers
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Error counter
     *
     * @var int
     */
    protected $_errorsCount = 0;

    /**
     * Limit of errors after which pre-processing will exit
     *
     * @var int
     */
    protected $_errorsLimit = 100;

    /**
     * Flag to disable import
     *
     * @var bool
     */
    protected $_importAllowed = true;

    /**
     * Array of invalid rows numbers
     *
     * @var array
     */
    protected $_invalidRows = [];

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [];

    /**
     * Notice messages
     *
     * @var string[]
     */
    protected $_notices = [];

    /**
     * Helper to encode/decode json
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * Entity model parameters
     *
     * @var array
     */
    protected $_parameters = [];

    /**
     * Column names that holds values with particular meaning
     *
     * @var string[]
     */
    protected $_specialAttributes = [self::COLUMN_ACTION];

    /**
     * Permanent entity columns
     *
     * @var string[]
     */
    protected $_permanentAttributes = [];

    /**
     * Number of entities processed by validation
     *
     * @var int
     */
    protected $_processedEntitiesCount = 0;

    /**
     * Number of rows processed by validation
     *
     * @var int
     */
    protected $_processedRowsCount = 0;

    /**
     * Rows which will be skipped during import
     *
     * [Row number 1] => true,
     * ...
     * [Row number N] => true
     *
     * @var array
     */
    protected $_skippedRows = [];

    /**
     * Array of numbers of validated rows as keys and boolean TRUE as values
     *
     * @var array
     */
    protected $_validatedRows = [];

    /**
     * Source model
     *
     * @var AbstractSource
     */
    protected $_source;

    /**
     * Array of unique attributes
     *
     * @var array
     */
    protected $_uniqueAttributes = [];

    /**
     * List of available behaviors
     *
     * @var string[]
     */
    protected $_availableBehaviors = [
        \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
    ];

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     */
    protected $_pageSize;

    /**
     * Maximum size of packet, that can be sent to DB
     *
     * @var int
     */
    protected $_maxDataSize;

    /**
     * Number of items to save to the db in one query
     *
     * @var int
     */
    protected $_bunchSize;

    /**
     * Code of a primary attribute which identifies the entity group if import contains of multiple rows
     *
     * @var string
     */
    protected $masterAttributeCode;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\App\Resource $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\App\Resource $resource,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_dataSourceModel = isset(
            $data['data_source_model']
        ) ? $data['data_source_model'] : $importFactory->create()->getDataSourceModel();
        $this->_connection = isset($data['connection']) ? $data['connection'] : $resource->getConnection('write');
        $this->_jsonHelper = $coreData;
        $this->string = $string;
        $this->_pageSize = isset(
            $data['page_size']
        ) ? $data['page_size'] : (static::XML_PATH_PAGE_SIZE ? (int)$this->_scopeConfig->getValue(
            static::XML_PATH_PAGE_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) : 0);
        $this->_maxDataSize = isset(
            $data['max_data_size']
        ) ? $data['max_data_size'] : $resourceHelper->getMaxDataSize();
        $this->_bunchSize = isset(
            $data['bunch_size']
        ) ? $data['bunch_size'] : (static::XML_PATH_BUNCH_SIZE ? (int)$this->_scopeConfig->getValue(
            static::XML_PATH_BUNCH_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) : 0);
    }

    /**
     * Import data rows
     *
     * @abstract
     * @return boolean
     */
    abstract protected function _importData();

    /**
     * Imported entity type code getter
     *
     * @abstract
     * @return string
     */
    abstract public function getEntityTypeCode();

    /**
     * Change row data before saving in DB table
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        /**
         * Convert all empty strings to null values, as
         * a) we don't use empty string in DB
         * b) empty strings instead of numeric values will product errors in Sql Server
         */
        foreach ($rowData as $key => $val) {
            if ($val === '') {
                $rowData[$key] = null;
            }
        }
        return $rowData;
    }

    /**
     * Validate data rows and save bunches to DB
     *
     * @return $this
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->getSource();
        $bunchRows = [];
        $startNewBunch = false;

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $masterAttributeCode = $this->getMasterAttributeCode();

        while ($source->valid() || count($bunchRows) || isset($entityGroup)) {
            if ($startNewBunch || !$source->valid()) {
                /* If the end approached add last validated entity group to the bunch */
                if (!$source->valid() && isset($entityGroup)) {
                    foreach ($entityGroup as $key => $value) {
                        $bunchRows[$key] = $value;
                    }
                    unset($entityGroup);
                }
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = [];
                $startNewBunch = false;
            }
            if ($source->valid()) {
                // errors limit check
                if ($this->_errorsCount >= $this->_errorsLimit) {
                    return $this;
                }
                $rowData = $source->current();

                if (isset($rowData[$masterAttributeCode]) && trim($rowData[$masterAttributeCode])) {
                    /* Add entity group that passed validation to bunch */
                    if (isset($entityGroup)) {
                        foreach ($entityGroup as $key => $value) {
                            $bunchRows[$key] = $value;
                        }
                        $productDataSize = strlen(serialize($bunchRows));

                        /* Check if the new bunch should be started */
                        $isBunchSizeExceeded = ($this->_bunchSize > 0 && count($bunchRows) >= $this->_bunchSize);
                        $startNewBunch = $productDataSize >= $this->_maxDataSize || $isBunchSizeExceeded;
                    }

                    /* And start a new one */
                    $entityGroup = [];
                }

                if (isset($entityGroup) && $this->validateRow($rowData, $source->key())) {
                    /* Add row to entity group */
                    $entityGroup[$source->key()] = $this->_prepareRowForDb($rowData);
                } elseif (isset($entityGroup)) {
                    /* In case validation of one line of the group fails kill the entire group */
                    unset($entityGroup);
                }

                $this->_processedRowsCount++;
                $source->next();
            }
        }
        return $this;
    }

    /**
     * Add error with corresponding current data source row number
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number
     * @param string $columnName OPTIONAL Column name
     * @return $this
     */
    public function addRowError($errorCode, $errorRowNum, $columnName = null)
    {
        $errorCode = (string)$errorCode;
        $this->_errors[$errorCode][] = [$errorRowNum + 1, $columnName];
        // one added for human readability
        $this->_invalidRows[$errorRowNum] = true;
        $this->_errorsCount++;

        return $this;
    }

    /**
     * Add message template for specific error code from outside
     *
     * @param string $errorCode Error code
     * @param string $message Message template
     * @return $this
     */
    public function addMessageTemplate($errorCode, $message)
    {
        $this->_messageTemplates[$errorCode] = $message;

        return $this;
    }

    /**
     * Import behavior getter
     *
     * @param array $rowData
     * @return string
     */
    public function getBehavior(array $rowData = null)
    {
        if (isset(
            $this->_parameters['behavior']
        ) && in_array(
            $this->_parameters['behavior'],
            $this->_availableBehaviors
        )
        ) {
            $behavior = $this->_parameters['behavior'];
            if ($rowData !== null && $behavior == \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM) {
                // try analyze value in self::COLUMN_CUSTOM column and return behavior for given $rowData
                if (array_key_exists(self::COLUMN_ACTION, $rowData)) {
                    if (strtolower($rowData[self::COLUMN_ACTION]) == self::COLUMN_ACTION_VALUE_DELETE) {
                        $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE;
                    } else {
                        // as per task description, if column value is different to self::COLUMN_CUSTOM_VALUE_DELETE,
                        // we should always use default behavior
                        return self::getDefaultBehavior();
                    }
                    if (in_array($behavior, $this->_availableBehaviors)) {
                        return $behavior;
                    }
                }
            } else {
                // if method is invoked without $rowData we should just return $this->_parameters['behavior']
                return $behavior;
            }
        }

        return self::getDefaultBehavior();
    }

    /**
     * Get default import behavior
     *
     * @return string
     */
    public static function getDefaultBehavior()
    {
        return \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE;
    }

    /**
     * Returns error information grouped by error types and translated (if possible)
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = [];
        foreach ($this->_errors as $errorCode => $errorRows) {
            if (isset($this->_messageTemplates[$errorCode])) {
                $errorCode = (string)__($this->_messageTemplates[$errorCode]);
            }
            foreach ($errorRows as $errorRowData) {
                $key = $errorRowData[1] ? sprintf($errorCode, $errorRowData[1]) : $errorCode;
                $messages[$key][] = $errorRowData[0];
            }
        }
        return $messages;
    }

    /**
     * Returns error counter value
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return $this->_errorsCount;
    }

    /**
     * Returns error limit value
     *
     * @return int
     */
    public function getErrorsLimit()
    {
        return $this->_errorsLimit;
    }

    /**
     * Returns invalid rows count
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return count($this->_invalidRows);
    }

    /**
     * Returns model notices
     *
     * @return string[]
     */
    public function getNotices()
    {
        return $this->_notices;
    }

    /**
     * Returns number of checked entities
     *
     * @return int
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_processedEntitiesCount;
    }

    /**
     * Returns number of checked rows
     *
     * @return int
     */
    public function getProcessedRowsCount()
    {
        return $this->_processedRowsCount;
    }

    /**
     * Source object getter
     *
     * @return AbstractSource
     * @throws \Magento\Framework\Model\Exception
     */
    public function getSource()
    {
        if (!$this->_source) {
            throw new \Magento\Framework\Model\Exception(__('Source is not set'));
        }
        return $this->_source;
    }

    /**
     * Import process start
     *
     * @return bool Result of operation
     */
    public function importData()
    {
        return $this->_importData();
    }

    /**
     * Is attribute contains particular data (not plain entity attribute)
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeParticular($attributeCode)
    {
        return in_array($attributeCode, $this->_specialAttributes);
    }

    /**
     * @return string the master attribute code to use in an import
     */
    public function getMasterAttributeCode()
    {
        return $this->masterAttributeCode;
    }

    /**
     * Check one attribute can be overridden in child
     *
     * @param string $attributeCode Attribute code
     * @param array $attributeParams Attribute params
     * @param array $rowData Row data
     * @param int $rowNumber
     * @return bool
     */
    public function isAttributeValid($attributeCode, array $attributeParams, array $rowData, $rowNumber)
    {
        switch ($attributeParams['type']) {
            case 'varchar':
                $value = $this->string->cleanString($rowData[$attributeCode]);
                $valid = $this->string->strlen($value) < self::DB_MAX_VARCHAR_LENGTH;
                break;
            case 'decimal':
                $value = trim($rowData[$attributeCode]);
                $valid = (double)$value == $value && is_numeric($value);
                break;
            case 'select':
            case 'multiselect':
                $valid = isset($attributeParams['options'][strtolower($rowData[$attributeCode])]);
                break;
            case 'int':
                $value = trim($rowData[$attributeCode]);
                $valid = (int)$value == $value && is_numeric($value);
                break;
            case 'datetime':
                $value = trim($rowData[$attributeCode]);
                $valid = strtotime($value) !== false;
                break;
            case 'text':
                $value = $this->string->cleanString($rowData[$attributeCode]);
                $valid = $this->string->strlen($value) < self::DB_MAX_TEXT_LENGTH;
                break;
            default:
                $valid = true;
                break;
        }

        if (!$valid) {
            $this->addRowError(__("Please correct the value for '%s'."), $rowNumber, $attributeCode);
        } elseif (!empty($attributeParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attributeCode][$rowData[$attributeCode]])) {
                $this->addRowError(__("Duplicate Unique Attribute for '%s'"), $rowNumber, $attributeCode);
                return false;
            }
            $this->_uniqueAttributes[$attributeCode][$rowData[$attributeCode]] = true;
        }
        return (bool)$valid;
    }

    /**
     * Check that is all of data valid
     *
     * @return bool
     */
    public function isDataValid()
    {
        $this->validateData();
        return 0 == $this->getErrorsCount();
    }

    /**
     * Import possibility getter
     *
     * @return bool
     */
    public function isImportAllowed()
    {
        return $this->_importAllowed;
    }

    /**
     * Returns TRUE if row is valid and not in skipped rows array
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function isRowAllowedToImport(array $rowData, $rowNumber)
    {
        return $this->validateRow($rowData, $rowNumber) && !isset($this->_skippedRows[$rowNumber]);
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    abstract public function validateRow(array $rowData, $rowNumber);

    /**
     * Set data from outside to change behavior
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;
        return $this;
    }

    /**
     * Source model setter
     *
     * @param AbstractSource $source
     * @return $this
     */
    public function setSource(AbstractSource $source)
    {
        $this->_source = $source;
        $this->_dataValidated = false;

        return $this;
    }

    /**
     * Validate data
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function validateData()
    {
        if (!$this->_dataValidated) {
            // do all permanent columns exist?
            $absentColumns = array_diff($this->_permanentAttributes, $this->getSource()->getColNames());
            if ($absentColumns) {
                throw new \Magento\Framework\Model\Exception(
                    __('Cannot find required columns: %1', implode(', ', $absentColumns))
                );
            }

            // check attribute columns names validity
            $columnNumber = 0;
            $emptyHeaderColumns = [];
            $invalidColumns = [];
            foreach ($this->getSource()->getColNames() as $columnName) {
                $columnNumber++;
                if (!$this->isAttributeParticular($columnName)) {
                    if (trim($columnName) == '') {
                        $emptyHeaderColumns[] = $columnNumber;
                    } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                        $invalidColumns[] = $columnName;
                    }
                }
            }

            if ($emptyHeaderColumns) {
                throw new \Magento\Framework\Model\Exception(
                    __('Columns number: "%1" have empty headers', implode('", "', $emptyHeaderColumns))
                );
            }
            if ($invalidColumns) {
                throw new \Magento\Framework\Model\Exception(
                    __('Column names: "%1" are invalid', implode('", "', $invalidColumns))
                );
            }

            // initialize validation related attributes
            $this->_errors = [];
            $this->_invalidRows = [];
            $this->_saveValidatedBunches();
            $this->_dataValidated = true;
        }
        return $this;
    }
}
