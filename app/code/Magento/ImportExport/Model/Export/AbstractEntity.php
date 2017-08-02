<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export;

/**
 * Export entity abstract model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 2.0.0
 */
abstract class AbstractEntity
{
    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Framework\Data\Collection::class;

    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = '';

    /**#@-*/

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Error codes with arrays of corresponding row numbers
     *
     * @var array
     * @since 2.0.0
     */
    protected $_errors = [];

    /**
     * Error counter
     *
     * @var int
     * @since 2.0.0
     */
    protected $_errorsCount = 0;

    /**
     * Limit of errors after which pre-processing will exit
     *
     * @var int
     * @since 2.0.0
     */
    protected $_errorsLimit = 100;

    /**
     * Validation information about processed rows
     *
     * @var array
     * @since 2.0.0
     */
    protected $_invalidRows = [];

    /**
     * Validation failure message template definitions
     *
     * @var array
     * @since 2.0.0
     */
    protected $_messageTemplates = [];

    /**
     * Parameters
     *
     * @var array
     * @since 2.0.0
     */
    protected $_parameters = [];

    /**
     * Number of entities processed by validation
     *
     * @var int
     * @since 2.0.0
     */
    protected $_processedEntitiesCount = 0;

    /**
     * Number of rows processed by validation
     *
     * @var int
     * @since 2.0.0
     */
    protected $_processedRowsCount = 0;

    /**
     * Source model
     *
     * @var AbstractAdapter
     * @since 2.0.0
     */
    protected $_writer;

    /**
     * Array of pairs store ID to its code
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeIdToCode = [];

    /**
     * Website ID-to-code
     *
     * @var array
     * @since 2.0.0
     */
    protected $_websiteIdToCode = [];

    /**
     * Disabled attributes
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_disabledAttributes = [];

    /**
     * Export file name
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_fileName = null;

    /**
     * Address attributes collection
     *
     * @var \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    protected $_attributeCollection;

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     * @since 2.0.0
     */
    protected $_pageSize;

    /**
     * Collection by pages iterator
     *
     * @var \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
     * @since 2.0.0
     */
    protected $_byPagesIterator;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * Attribute code to its values. Only attributes with options and only default store values used
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributeCodes = null;

    /**
     * Permanent entity columns
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_permanentAttributes = [];

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_attributeCollection = isset(
            $data['attribute_collection']
        ) ? $data['attribute_collection'] : $collectionFactory->create(
            static::ATTRIBUTE_COLLECTION_NAME
        );
        $this->_pageSize = isset(
            $data['page_size']
        ) ? $data['page_size'] : (static::XML_PATH_PAGE_SIZE ? (int)$this->_scopeConfig->getValue(
            static::XML_PATH_PAGE_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) : 0);
        $this->_byPagesIterator = isset(
            $data['collection_by_pages_iterator']
        ) ? $data['collection_by_pages_iterator'] : $resourceColFactory->create();
    }

    /**
     * Initialize stores hash
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initStores()
    {
        /** @var $store \Magento\Store\Model\Store */
        foreach ($this->_storeManager->getStores(true) as $store) {
            $this->_storeIdToCode[$store->getId()] = $store->getCode();
        }
        ksort($this->_storeIdToCode);
        // to ensure that 'admin' store (ID is zero) goes first

        return $this;
    }

    /**
     * Initialize website values
     *
     * @param bool $withDefault
     * @return $this
     * @since 2.0.0
     */
    protected function _initWebsites($withDefault = false)
    {
        /** @var $website \Magento\Store\Model\Website */
        foreach ($this->_storeManager->getWebsites($withDefault) as $website) {
            $this->_websiteIdToCode[$website->getId()] = $website->getCode();
        }
        return $this;
    }

    /**
     * Add error with corresponding current data source row number
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number
     * @return $this
     * @since 2.0.0
     */
    public function addRowError($errorCode, $errorRowNum)
    {
        $errorCode = (string)$errorCode;
        $this->_errors[$errorCode][] = $errorRowNum + 1;
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
     * @since 2.0.0
     */
    public function addMessageTemplate($errorCode, $message)
    {
        $this->_messageTemplates[$errorCode] = $message;

        return $this;
    }

    /**
     * Retrieve message template
     *
     * @param string $errorCode
     * @return null|string
     * @since 2.0.0
     */
    public function retrieveMessageTemplate($errorCode)
    {
        if (isset($this->_messageTemplates[$errorCode])) {
            return $this->_messageTemplates[$errorCode];
        }
        return null;
    }

    /**
     * Export process
     *
     * @return string
     * @since 2.0.0
     */
    abstract public function export();

    /**
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     * @since 2.0.0
     */
    abstract public function exportItem($item);

    /**
     * Iterate through given collection page by page and export items
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @return void
     * @since 2.0.0
     */
    protected function _exportCollectionByPages(\Magento\Framework\Data\Collection\AbstractDb $collection)
    {
        $this->_byPagesIterator->iterate($collection, $this->_pageSize, [[$this, 'exportItem']]);
    }

    /**
     * Get attributes codes which are appropriate for export
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getExportAttributeCodes()
    {
        if (null === $this->_attributeCodes) {
            if (!empty($this->_parameters[Export::FILTER_ELEMENT_SKIP])
                && is_array($this->_parameters[Export::FILTER_ELEMENT_SKIP])
            ) {
                $skippedAttributes = array_flip(
                    $this->_parameters[Export::FILTER_ELEMENT_SKIP]
                );
            } else {
                $skippedAttributes = [];
            }
            $attributeCodes = [];

            /** @var $attribute AbstractAttribute */
            foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
                if (!isset($skippedAttributes[$attribute->getAttributeId()])
                    || in_array($attribute->getAttributeCode(), $this->_permanentAttributes)
                ) {
                    $attributeCodes[] = $attribute->getAttributeCode();
                }
            }
            $this->_attributeCodes = $attributeCodes;
        }
        return $this->_attributeCodes;
    }

    /**
     * Entity type code getter
     *
     * @abstract
     * @return string
     * @since 2.0.0
     */
    abstract public function getEntityTypeCode();

    /**
     * Get header columns
     *
     * @return array
     * @since 2.0.0
     */
    abstract protected function _getHeaderColumns();

    /**
     * Get entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     * @since 2.0.0
     */
    abstract protected function _getEntityCollection();

    /**
     * Entity attributes collection getter
     *
     * @return \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    public function getAttributeCollection()
    {
        return $this->_attributeCollection;
    }

    /**
     * Clean up attribute collection
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    public function filterAttributeCollection(\Magento\Framework\Data\Collection $collection)
    {
        /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
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
     * @since 2.0.0
     */
    public function getErrorMessages()
    {
        $messages = [];
        foreach ($this->_errors as $errorCode => $errorRows) {
            $message = isset(
                $this->_messageTemplates[$errorCode]
            ) ? __(
                $this->_messageTemplates[$errorCode]
            ) : __(
                'Please correct the value for "%1" column.',
                $errorCode
            );
            $message = (string)$message;
            $messages[$message] = $errorRows;
        }

        return $messages;
    }

    /**
     * Returns error counter value
     *
     * @return int
     * @since 2.0.0
     */
    public function getErrorsCount()
    {
        return $this->_errorsCount;
    }

    /**
     * Returns invalid rows count
     *
     * @return int
     * @since 2.0.0
     */
    public function getInvalidRowsCount()
    {
        return count($this->_invalidRows);
    }

    /**
     * Returns number of checked entities
     *
     * @return int
     * @since 2.0.0
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_processedEntitiesCount;
    }

    /**
     * Returns number of checked rows
     *
     * @return int
     * @since 2.0.0
     */
    public function getProcessedRowsCount()
    {
        return $this->_processedRowsCount;
    }

    /**
     * Inner writer object getter
     *
     * @return AbstractAdapter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getWriter()
    {
        if (!$this->_writer) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please specify the writer.'));
        }

        return $this->_writer;
    }

    /**
     * Set parameters
     *
     * @param string[] $parameters
     * @return $this
     * @since 2.0.0
     */
    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;

        return $this;
    }

    /**
     * Writer model setter
     *
     * @param AbstractAdapter $writer
     * @return $this
     * @since 2.0.0
     */
    public function setWriter(AbstractAdapter $writer)
    {
        $this->_writer = $writer;

        return $this;
    }

    /**
     * Set export file name
     *
     * @param null|string $fileName
     * @return void
     * @since 2.0.0
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * Get export file name
     *
     * @return null|string
     * @since 2.0.0
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Retrieve list of disabled attributes codes
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getDisabledAttributes()
    {
        return $this->_disabledAttributes;
    }
}
