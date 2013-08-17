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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export entity abstract model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_ImportExport_Model_Export_EntityAbstract
{
    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Varien_Data_Collection';
    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = '';
    /**#@-*/

    /**
     * Website manager (currently Mage_Core_Model_App works as website manager)
     *
     * @var Mage_Core_Model_App
     */
    protected $_websiteManager;

    /**
     * Store manager (currently Mage_Core_Model_App works as store manager)
     *
     * @var Mage_Core_Model_App
     */
    protected $_storeManager;

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
     * Validation information about processed rows
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
     * Helper to translate error messages
     *
     * @var Mage_ImportExport_Helper_Data
     */
    protected $_translator;

    /**
     * Parameters
     *
     * @var array
     */
    protected $_parameters = array();

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
     * Source model
     *
     * @var Mage_ImportExport_Model_Export_Adapter_Abstract
     */
    protected $_writer;

    /**
     * Array of pairs store ID to its code
     *
     * @var array
     */
    protected $_storeIdToCode = array();

    /**
     * Website ID-to-code
     *
     * @var array
     */
    protected $_websiteIdToCode = array();

    /**
     * Disabled attributes
     *
     * @var array
     */
    protected $_disabledAttributes = array();

    /**
     * Export file name
     *
     * @var string|null
     */
    protected $_fileName = null;

    /**
     * Address attributes collection
     *
     * @var Varien_Data_Collection
     */
    protected $_attributeCollection;

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     */
    protected $_pageSize;

    /**
     * Collection by pages iterator
     *
     * @var Mage_ImportExport_Model_Resource_CollectionByPagesIterator
     */
    protected $_byPagesIterator;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_websiteManager = isset($data['website_manager']) ? $data['website_manager'] : Mage::app();
        $this->_storeManager   = isset($data['store_manager']) ? $data['store_manager'] : Mage::app();
        $this->_translator     = isset($data['translator']) ? $data['translator']
            : Mage::helper('Mage_ImportExport_Helper_Data');
        $this->_attributeCollection = isset($data['attribute_collection']) ? $data['attribute_collection']
            : Mage::getResourceModel(static::ATTRIBUTE_COLLECTION_NAME);
        $this->_pageSize = isset($data['page_size']) ? $data['page_size']
            : (static::XML_PATH_PAGE_SIZE ? (int) Mage::getStoreConfig(static::XML_PATH_PAGE_SIZE) : 0);
        $this->_byPagesIterator = isset($data['collection_by_pages_iterator']) ? $data['collection_by_pages_iterator']
            : Mage::getResourceModel('Mage_ImportExport_Model_Resource_CollectionByPagesIterator');
    }

    /**
     * Initialize stores hash
     *
     * @return Mage_ImportExport_Model_Export_EntityAbstract
     */
    protected function _initStores()
    {
        /** @var $store Mage_Core_Model_Store */
        foreach ($this->_storeManager->getStores(true) as $store) {
            $this->_storeIdToCode[$store->getId()] = $store->getCode();
        }
        ksort($this->_storeIdToCode); // to ensure that 'admin' store (ID is zero) goes first

        return $this;
    }

    /**
     * Initialize website values
     *
     * @param bool $withDefault
     * @return Mage_ImportExport_Model_Export_EntityAbstract
     */
    protected function _initWebsites($withDefault = false)
    {
        /** @var $website Mage_Core_Model_Website */
        foreach ($this->_websiteManager->getWebsites($withDefault) as $website) {
            $this->_websiteIdToCode[$website->getId()] = $website->getCode();
        }
        return $this;
    }

    /**
     * Add error with corresponding current data source row number
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number
     * @return Mage_ImportExport_Model_Export_EntityAbstract
     */
    public function addRowError($errorCode, $errorRowNum)
    {
        $this->_errors[$errorCode][] = $errorRowNum + 1; // one added for human readability
        $this->_invalidRows[$errorRowNum] = true;
        $this->_errorsCount++;

        return $this;
    }

    /**
     * Add message template for specific error code from outside
     *
     * @param string $errorCode Error code
     * @param string $message Message template
     * @return Mage_ImportExport_Model_Export_EntityAbstract
     */
    public function addMessageTemplate($errorCode, $message)
    {
        $this->_messageTemplates[$errorCode] = $message;

        return $this;
    }

    /**
     * Export process
     *
     * @return string
     */
    abstract public function export();

    /**
     * Export one item
     *
     * @param Mage_Core_Model_Abstract $item
     */
    abstract public function exportItem($item);

    /**
     * Iterate through given collection page by page and export items
     *
     * @param Varien_Data_Collection_Db $collection
     */
    protected function _exportCollectionByPages(Varien_Data_Collection_Db $collection)
    {
        $this->_byPagesIterator->iterate($collection, $this->_pageSize, array(array($this, 'exportItem')));
    }

    /**
     * Entity type code getter
     *
     * @abstract
     * @return string
     */
    abstract public function getEntityTypeCode();

    /**
     * Get header columns
     *
     * @return array
     */
    abstract protected function _getHeaderColumns();

    /**
     * Get entity collection
     *
     * @return Varien_Data_Collection_Db
     */
    abstract protected function _getEntityCollection();

    /**
     * Entity attributes collection getter
     *
     * @return Varien_Data_Collection
     */
    public function getAttributeCollection()
    {
        return $this->_attributeCollection;
    }

    /**
     * Clean up attribute collection
     *
     * @param Varien_Data_Collection $collection
     * @return Varien_Data_Collection
     */
    public function filterAttributeCollection(Varien_Data_Collection $collection)
    {
        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        foreach ($collection as $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->_disabledAttributes)) {
                $collection->removeItemByKey($attribute->getId());
            }
        }

        return $collection;
    }

    /**
     * Returns error information
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = array();
        foreach ($this->_errors as $errorCode => $errorRows) {
            $message = isset($this->_messageTemplates[$errorCode])
                ? $this->_translator->__($this->_messageTemplates[$errorCode])
                : $this->_translator->__("Please correct the value for '%s' column", $errorCode);
            $messages[$message] = $errorRows;
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
     * Returns invalid rows count
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return count($this->_invalidRows);
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
     * Inner writer object getter
     *
     * @throws Exception
     * @return Mage_ImportExport_Model_Export_Adapter_Abstract
     */
    public function getWriter()
    {
        if (!$this->_writer) {
            Mage::throwException(Mage::helper('Mage_ImportExport_Helper_Data')->__('Please specify writer.'));
        }

        return $this->_writer;
    }

    /**
     * Set parameters
     *
     * @param array $parameters
     * @return Mage_ImportExport_Model_Export_EntityAbstract
     */
    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;

        return $this;
    }

    /**
     * Writer model setter
     *
     * @param Mage_ImportExport_Model_Export_Adapter_Abstract $writer
     * @return Mage_ImportExport_Model_Export_EntityAbstract
     */
    public function setWriter(Mage_ImportExport_Model_Export_Adapter_Abstract $writer)
    {
        $this->_writer = $writer;

        return $this;
    }

    /**
     * Set export file name
     *
     * @param null|string $fileName
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * Get export file name
     *
     * @return null|string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Retrieve list of disabled attributes codes
     *
     * @return array
     */
    public function getDisabledAttributes()
    {
        return $this->_disabledAttributes;
    }
}
