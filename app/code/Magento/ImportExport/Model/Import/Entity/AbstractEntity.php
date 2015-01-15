<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Entity;

use Magento\ImportExport\Model\Import\AbstractSource;

/**
 * Import entity abstract model
 */
abstract class AbstractEntity
{
    /**
     * Database constants
     */
    const DB_MAX_PACKET_COEFFICIENT = 900000;

    const DB_MAX_PACKET_DATA = 1048576;

    const DB_MAX_VARCHAR_LENGTH = 256;

    const DB_MAX_TEXT_LENGTH = 65536;

    /**
     * DB connection.
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
     * DB data source model.
     *
     * @var \Magento\ImportExport\Model\Resource\Import\Data
     */
    protected $_dataSourceModel;

    /**
     * Entity type id.
     *
     * @var int
     */
    protected $_entityTypeId;

    /**
     * Error codes with arrays of corresponding row numbers.
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Error counter.
     *
     * @var int
     */
    protected $_errorsCount = 0;

    /**
     * Limit of errors after which pre-processing will exit.
     *
     * @var int
     */
    protected $_errorsLimit = 100;

    /**
     * Flag to disable import.
     *
     * @var bool
     */
    protected $_importAllowed = true;

    /**
     * Attributes with index (not label) value.
     *
     * @var array
     */
    protected $_indexValueAttributes = [];

    /**
     * Array of invalid rows numbers.
     *
     * @var array
     */
    protected $_invalidRows = [];

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_messageTemplates = [];

    /**
     * Notice messages.
     *
     * @var string[]
     */
    protected $_notices = [];

    /**
     * Entity model parameters.
     *
     * @var array
     */
    protected $_parameters = [];

    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_specialAttributes = [];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [];

    /**
     * Number of entities processed by validation.
     *
     * @var int
     */
    protected $_processedEntitiesCount = 0;

    /**
     * Number of rows processed by validation.
     *
     * @var int
     */
    protected $_processedRowsCount = 0;

    /**
     * Rows to skip. Valid rows but we have some reasons to skip them.
     *
     * [Row number 1] => true,
     * ...
     * [Row number N] => true
     *
     * @var array
     */
    protected $_rowsToSkip = [];

    /**
     * Array of numbers of validated rows as keys and boolean TRUE as values.
     *
     * @var array
     */
    protected $_validatedRows = [];

    /**
     * Source model.
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
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $_importExportData;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var \Magento\ImportExport\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\Resource\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\Resource\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Resource $resource,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Stdlib\String $string
    ) {
        $this->_coreData = $coreData;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->string = $string;

        $entityType = $config->getEntityType($this->getEntityTypeCode());

        $this->_entityTypeId = $entityType->getEntityTypeId();
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection('write');
    }

    /**
     * Inner source object getter.
     *
     * @return AbstractSource
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getSource()
    {
        if (!$this->_source) {
            throw new \Magento\Framework\Model\Exception(__('Please specify a source.'));
        }
        return $this->_source;
    }

    /**
     * Import data rows.
     *
     * @abstract
     * @return boolean
     */
    abstract protected function _importData();

    /**
     * Returns boolean TRUE if row scope is default (fundamental) scope.
     *
     * @param array $rowData
     * @return bool
     */
    protected function _isRowScopeDefault(array $rowData)
    {
        return true;
    }

    /**
     * Change row data before saving in DB table.
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
     * Validate data rows and save bunches to DB.
     *
     * @return $this|void
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen(serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                if ($this->_errorsCount >= $this->_errorsLimit) {
                    // errors limit check
                    return;
                }
                $rowData = $source->current();

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->_coreData->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        return $this;
    }

    /**
     * Add error with corresponding current data source row number.
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number.
     * @param string $colName OPTIONAL Column name.
     * @return $this
     */
    public function addRowError($errorCode, $errorRowNum, $colName = null)
    {
        $errorCode = (string)$errorCode;
        $this->_errors[$errorCode][] = [$errorRowNum + 1, $colName];
        // one added for human readability
        $this->_invalidRows[$errorRowNum] = true;
        $this->_errorsCount++;

        return $this;
    }

    /**
     * Add message template for specific error code from outside.
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
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased.
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param array $indexValAttrs OPTIONAL Additional attributes' codes with index values.
     * @return array
     */
    public function getAttributeOptions(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        $indexValAttrs = []
    ) {
        $options = [];

        if ($attribute->usesSource()) {
            // merge global entity index value attributes
            $indexValAttrs = array_merge($indexValAttrs, $this->_indexValueAttributes);

            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $indexValAttrs) ? 'value' : 'label';

            // only default (admin) store values used
            $attribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    $value = is_array($option['value']) ? $option['value'] : [$option];
                    foreach ($value as $innerOption) {
                        if (strlen($innerOption['value'])) {
                            // skip ' -- Please Select -- ' option
                            $options[strtolower($innerOption[$index])] = $innerOption['value'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // ignore exceptions connected with source models
            }
        }
        return $options;
    }

    /**
     * Import behavior getter.
     *
     * @return string
     */
    public function getBehavior()
    {
        if (!isset(
            $this->_parameters['behavior']
        ) ||
            $this->_parameters['behavior'] != \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND &&
            $this->_parameters['behavior'] != \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE &&
            $this->_parameters['behavior'] != \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
        ) {
            return \Magento\ImportExport\Model\Import::getDefaultBehavior();
        }
        return $this->_parameters['behavior'];
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    abstract public function getEntityTypeCode();

    /**
     * Entity type ID getter.
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        return $this->_entityTypeId;
    }

    /**
     * Returns error information grouped by error types and translated (if possible).
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = [];
        foreach ($this->_errors as $errorCode => $errorRows) {
            if (isset($this->_messageTemplates[$errorCode])) {
                $errorCode = __($this->_messageTemplates[$errorCode]);
            }
            foreach ($errorRows as $errorRowData) {
                $key = $errorRowData[1] ? sprintf($errorCode, $errorRowData[1]) : $errorCode;
                $messages[$key][] = $errorRowData[0];
            }
        }
        return $messages;
    }

    /**
     * Returns error counter value.
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return $this->_errorsCount;
    }

    /**
     * Returns error limit value.
     *
     * @return int
     */
    public function getErrorsLimit()
    {
        return $this->_errorsLimit;
    }

    /**
     * Returns invalid rows count.
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return count($this->_invalidRows);
    }

    /**
     * Returns model notices.
     *
     * @return string[]
     */
    public function getNotices()
    {
        return $this->_notices;
    }

    /**
     * Returns number of checked entities.
     *
     * @return int
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_processedEntitiesCount;
    }

    /**
     * Returns number of checked rows.
     *
     * @return int
     */
    public function getProcessedRowsCount()
    {
        return $this->_processedRowsCount;
    }

    /**
     * Source object getter.
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
     * Import process start.
     *
     * @return bool Result of operation.
     */
    public function importData()
    {
        return $this->_importData();
    }

    /**
     * Is attribute contains particular data (not plain entity attribute).
     *
     * @param string $attrCode
     * @return bool
     */
    public function isAttributeParticular($attrCode)
    {
        return in_array($attrCode, $this->_specialAttributes);
    }

    /**
     * Check one attribute. Can be overridden in child.
     *
     * @param string $attrCode Attribute code
     * @param array $attrParams Attribute params
     * @param array $rowData Row data
     * @param int $rowNum
     * @return boolean
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        switch ($attrParams['type']) {
            case 'varchar':
                $val = $this->string->cleanString($rowData[$attrCode]);
                $valid = $this->string->strlen($val) < self::DB_MAX_VARCHAR_LENGTH;
                break;
            case 'decimal':
                $val = trim($rowData[$attrCode]);
                $valid = (double)$val == $val;
                break;
            case 'select':
            case 'multiselect':
                $valid = isset($attrParams['options'][strtolower($rowData[$attrCode])]);
                break;
            case 'int':
                $val = trim($rowData[$attrCode]);
                $valid = (int)$val == $val;
                break;
            case 'datetime':
                $val = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false;
                break;
            case 'text':
                $val = $this->string->cleanString($rowData[$attrCode]);
                $valid = $this->string->strlen($val) < self::DB_MAX_TEXT_LENGTH;
                break;
            default:
                $valid = true;
                break;
        }

        if (!$valid) {
            $this->addRowError(__("Please correct the value for '%s'."), $rowNum, $attrCode);
        } elseif (!empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]])) {
                $this->addRowError(__("Duplicate Unique Attribute for '%s'"), $rowNum, $attrCode);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = true;
        }
        return (bool)$valid;
    }

    /**
     * Is all of data valid?
     *
     * @return bool
     */
    public function isDataValid()
    {
        $this->validateData();
        return 0 == $this->_errorsCount;
    }

    /**
     * Import possibility getter.
     *
     * @return bool
     */
    public function isImportAllowed()
    {
        return $this->_importAllowed;
    }

    /**
     * Returns TRUE if row is valid and not in skipped rows array.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function isRowAllowedToImport(array $rowData, $rowNum)
    {
        return $this->validateRow($rowData, $rowNum) && !isset($this->_rowsToSkip[$rowNum]);
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    abstract public function validateRow(array $rowData, $rowNum);

    /**
     * Set data from outside to change behavior. I.e. for setting some default parameters etc.
     *
     * @param array $params
     * @return $this
     */
    public function setParameters(array $params)
    {
        $this->_parameters = $params;
        return $this;
    }

    /**
     * Source model setter.
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
     * Validate data.
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function validateData()
    {
        if (!$this->_dataValidated) {
            // do all permanent columns exist?
            if ($absentColumns = array_diff($this->_permanentAttributes, $this->getSource()->getColNames())) {
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
