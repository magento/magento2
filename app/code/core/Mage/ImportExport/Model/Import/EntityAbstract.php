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
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import entity abstract model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_ImportExport_Model_Import_EntityAbstract
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
    const XML_PATH_PAGE_SIZE  = 'import/format_v2/page_size';
    /**#@-*/

    /**#@+
     * Database constants
     */
    const DB_MAX_VARCHAR_LENGTH = 256;
    const DB_MAX_TEXT_LENGTH    = 65536;
    /**#@-*/

    /**
     * DB connection
     *
     * @var Varien_Db_Adapter_Interface
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
     * @var Mage_ImportExport_Model_Resource_Import_Data
     */
    protected $_dataSourceModel;

    /**
     * Error codes with arrays of corresponding row numbers
     *
     * @var array
     */
    protected $_errors = array();

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
    protected $_invalidRows = array();

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array();

    /**
     * Notice messages
     *
     * @var array
     */
    protected $_notices = array();

    /**
     * Helper to encode/decode json
     *
     * @var Mage_Core_Helper_Data
     */
    protected $_jsonHelper;

    /**
     * Helper to manipulate with string
     *
     * @var Mage_Core_Helper_String
     */
    protected $_stringHelper;

    /**
     * Entity model parameters
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * Column names that holds values with particular meaning
     *
     * @var array
     */
    protected $_specialAttributes = array(self::COLUMN_ACTION);

    /**
     * Permanent entity columns
     *
     * @var array
     */
    protected $_permanentAttributes = array();

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
    protected $_skippedRows = array();

    /**
     * Array of numbers of validated rows as keys and boolean TRUE as values
     *
     * @var array
     */
    protected $_validatedRows = array();

    /**
     * Source model
     *
     * @var Mage_ImportExport_Model_Import_SourceAbstract
     */
    protected $_source;

    /**
     * Array of unique attributes
     *
     * @var array
     */
    protected $_uniqueAttributes = array();

    /**
     * List of available behaviors
     *
     * @var array
     */
    protected $_availableBehaviors = array(
        Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE,
        Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
        Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM,
    );

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
     * Array of data helpers
     *
     * @var array
     */
    protected $_helpers;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (isset($data['helpers'])) {
            $this->_helpers = $data['helpers'];
        }

        $this->_dataSourceModel     = isset($data['data_source_model']) ? $data['data_source_model']
            : Mage_ImportExport_Model_Import::getDataSourceModel();
        $this->_connection          = isset($data['connection']) ? $data['connection']
            : Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        $this->_jsonHelper          = isset($data['json_helper']) ? $data['json_helper']
            : Mage::helper('Mage_Core_Helper_Data');
        $this->_stringHelper        = isset($data['string_helper']) ? $data['string_helper']
            : Mage::helper('Mage_Core_Helper_String');
        $this->_pageSize            = isset($data['page_size']) ? $data['page_size']
            : (static::XML_PATH_PAGE_SIZE ? (int) Mage::getStoreConfig(static::XML_PATH_PAGE_SIZE) : 0);
        $this->_maxDataSize         = isset($data['max_data_size']) ? $data['max_data_size']
            : Mage::getResourceHelper('Mage_ImportExport')->getMaxDataSize();
        $this->_bunchSize           = isset($data['bunch_size']) ? $data['bunch_size']
            : (static::XML_PATH_BUNCH_SIZE ? (int) Mage::getStoreConfig(static::XML_PATH_BUNCH_SIZE) : 0);
    }

    /**
     * Helper getter
     *
     * @param string $helperName
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper($helperName)
    {
        return isset($this->_helpers[$helperName]) ? $this->_helpers[$helperName] : Mage::helper($helperName);
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
     * @return Mage_ImportExport_Model_Import_EntityAbstract
     */
    protected function _saveValidatedBunches()
    {
        $source            = $this->getSource();
        $processedDataSize = 0;
        $bunchRows         = array();
        $startNewBunch     = false;
        $nextRowBackup     = array();

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch(
                    $this->getEntityTypeCode(),
                    $this->getBehavior(),
                    $bunchRows
                );

                $bunchRows         = $nextRowBackup;
                $processedDataSize = strlen(serialize($bunchRows));
                $startNewBunch     = false;
                $nextRowBackup     = array();
            }
            if ($source->valid()) {
                // errors limit check
                if ($this->_errorsCount >= $this->_errorsLimit) {
                    return $this;
                }
                $rowData = $source->current();
                // add row to bunch for save
                if ($this->validateRow($rowData, $source->key())) {
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->_jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = ($this->_bunchSize > 0 && count($bunchRows) >= $this->_bunchSize);

                    if (($processedDataSize + $rowSize) >= $this->_maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = array($source->key() => $rowData);
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $processedDataSize += $rowSize;
                    }
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
     * @return Mage_ImportExport_Model_Import_EntityAbstract
     */
    public function addRowError($errorCode, $errorRowNum, $columnName = null)
    {
        $this->_errors[$errorCode][] = array($errorRowNum + 1, $columnName); // one added for human readability
        $this->_invalidRows[$errorRowNum] = true;
        $this->_errorsCount++;

        return $this;
    }

    /**
     * Add message template for specific error code from outside
     *
     * @param string $errorCode Error code
     * @param string $message Message template
     * @return Mage_ImportExport_Model_Import_EntityAbstract
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
        if (isset($this->_parameters['behavior'])
            && in_array($this->_parameters['behavior'], $this->_availableBehaviors)
        ) {
            $behavior = $this->_parameters['behavior'];
            if ($rowData !== null && $behavior == Mage_ImportExport_Model_Import::BEHAVIOR_CUSTOM) {
                // try analyze value in self::COLUMN_CUSTOM column and return behavior for given $rowData
                if (array_key_exists(self::COLUMN_ACTION, $rowData)) {
                    if (strtolower($rowData[self::COLUMN_ACTION]) == self::COLUMN_ACTION_VALUE_DELETE) {
                        $behavior = Mage_ImportExport_Model_Import::BEHAVIOR_DELETE;
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
        return Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE;
    }

    /**
     * Returns error information grouped by error types and translated (if possible)
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = array();

        foreach ($this->_errors as $errorCode => $errorRows) {
            if (isset($this->_messageTemplates[$errorCode])) {
                $errorCode = $this->_helper('Mage_ImportExport_Helper_Data')->__($this->_messageTemplates[$errorCode]);
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
     * @return array
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
     * @throws Exception
     * @return Mage_ImportExport_Model_Import_SourceAbstract
     */
    public function getSource()
    {
        if (!$this->_source) {
            Mage::throwException($this->_helper('Mage_ImportExport_Helper_Data')->__('Source is not set'));
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
     * Check one attribute can be overridden in child
     *
     * @param string $attributeCode Attribute code
     * @param array $attributeParams Attribute params
     * @param array $rowData Row data
     * @param int $rowNumber
     * @return boolean
     */
    public function isAttributeValid($attributeCode, array $attributeParams, array $rowData, $rowNumber)
    {
        switch ($attributeParams['type']) {
            case 'varchar':
                $value = $this->_stringHelper->cleanString($rowData[$attributeCode]);
                $valid = $this->_stringHelper->strlen($value) < self::DB_MAX_VARCHAR_LENGTH;
                break;
            case 'decimal':
                $value = trim($rowData[$attributeCode]);
                $valid = ((float)$value == $value) && is_numeric($value);
                break;
            case 'select':
            case 'multiselect':
                $valid = isset($attributeParams['options'][strtolower($rowData[$attributeCode])]);
                break;
            case 'int':
                $value = trim($rowData[$attributeCode]);
                $valid = ((int)$value == $value) && is_numeric($value);
                break;
            case 'datetime':
                $value = trim($rowData[$attributeCode]);
                $valid = strtotime($value) !== false
                    || preg_match('/^\d{2}.\d{2}.\d{2,4}(?:\s+\d{1,2}.\d{1,2}(?:.\d{1,2})?)?$/', $value);
                break;
            case 'text':
                $value = $this->_stringHelper->cleanString($rowData[$attributeCode]);
                $valid = $this->_stringHelper->strlen($value) < self::DB_MAX_TEXT_LENGTH;
                break;
            default:
                $valid = true;
                break;
        }

        if (!$valid) {
            $this->addRowError($this->_helper('Mage_ImportExport_Helper_Data')->__("Invalid value for '%s'"),
                $rowNumber, $attributeCode
            );
        } elseif (!empty($attributeParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attributeCode][$rowData[$attributeCode]])) {
                $this->addRowError(
                    $this->_helper('Mage_ImportExport_Helper_Data')->__("Duplicate Unique Attribute for '%s'"),
                    $rowNumber, $attributeCode
                );
                return false;
            }
            $this->_uniqueAttributes[$attributeCode][$rowData[$attributeCode]] = true;
        }
        return (bool) $valid;
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
     * @return boolean
     */
    abstract public function validateRow(array $rowData, $rowNumber);

    /**
     * Set data from outside to change behavior
     *
     * @param array $parameters
     * @return Mage_ImportExport_Model_Import_EntityAbstract
     */
    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;
        return $this;
    }

    /**
     * Source model setter
     *
     * @param Mage_ImportExport_Model_Import_SourceAbstract $source
     * @return Mage_ImportExport_Model_Import_EntityAbstract
     */
    public function setSource(Mage_ImportExport_Model_Import_SourceAbstract $source)
    {
        $this->_source = $source;
        $this->_dataValidated = false;

        return $this;
    }

    /**
     * Validate data
     *
     * @throws Exception
     * @return Mage_ImportExport_Model_Import_EntityAbstract
     */
    public function validateData()
    {
        if (!$this->_dataValidated) {
            // do all permanent columns exist?
            if ($absentColumns = array_diff($this->_permanentAttributes, $this->getSource()->getColNames())) {
                Mage::throwException(
                    $this->_helper('Mage_ImportExport_Helper_Data')->__('Can not find required columns: %s',
                        implode(', ', $absentColumns)
                    )
                );
            }

            // check attribute columns names validity
            $columnNumber       = 0;
            $emptyHeaderColumns = array();
            $invalidColumns     = array();
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
                Mage::throwException(
                    $this->_helper('Mage_ImportExport_Helper_Data')->__('Columns number: "%s" have empty headers',
                        implode('", "', $emptyHeaderColumns)
                    )
                );
            }
            if ($invalidColumns) {
                Mage::throwException(
                    $this->_helper('Mage_ImportExport_Helper_Data')->__('Column names: "%s" are invalid',
                        implode('", "', $invalidColumns)
                    )
                );
            }

            // initialize validation related attributes
            $this->_errors = array();
            $this->_invalidRows = array();
            $this->_saveValidatedBunches();
            $this->_dataValidated = true;
        }
        return $this;
    }
}
