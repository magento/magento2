<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export\Entity;

use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

/**
 * Export entity abstract model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractEntity
{
    /**
     * Attribute code to its values. Only attributes with options and only default store values used.
     *
     * @var array
     */
    protected $_attributeValues = [];

    /**
     * Attribute code to its values. Only attributes with options and only default store values used.
     *
     * @var array
     */
    protected static $attrCodes = null;

    /**
     * DB connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Array of attributes codes which are disabled for export.
     *
     * @var string[]
     */
    protected $_disabledAttrs = [];

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
     * Export filter data.
     *
     * @var array
     */
    protected $_filter = [];

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = [];

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_messageTemplates = [];

    /**
     * Parameters.
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
     * Source model.
     *
     * @var AbstractAdapter
     */
    protected $_writer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $config
     * @param ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $config,
        ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_localeDate = $localeDate;
        $this->_storeManager = $storeManager;
        $entityCode = $this->getEntityTypeCode();
        $this->_entityTypeId = $config->getEntityType($entityCode)->getEntityTypeId();
        $this->_connection = $resource->getConnection();
    }

    /**
     * Initialize stores hash.
     *
     * @return $this
     */
    protected function _initStores()
    {
        foreach ($this->_storeManager->getStores(true) as $store) {
            $this->_storeIdToCode[$store->getId()] = $store->getCode();
        }
        ksort($this->_storeIdToCode);
        // to ensure that 'admin' store (ID is zero) goes first

        return $this;
    }

    /**
     * Get header columns
     *
     * @return string[]
     */
    abstract protected function _getHeaderColumns();

    /**
     * Get entity collection
     *
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    abstract protected function _getEntityCollection($resetCollection = false);

    /**
     * Get attributes codes which are appropriate for export.
     *
     * @return array
     */
    protected function _getExportAttrCodes()
    {
        if (null === self::$attrCodes) {
            if (!empty($this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP]) && is_array(
                $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP]
            )
            ) {
                $skipAttr = array_flip($this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP]);
            } else {
                $skipAttr = [];
            }
            $attrCodes = [];

            foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
                if (!isset(
                    $skipAttr[$attribute->getAttributeId()]
                ) || in_array(
                    $attribute->getAttributeCode(),
                    $this->_permanentAttributes
                )
                ) {
                    $attrCodes[] = $attribute->getAttributeCode();
                }
            }
            self::$attrCodes = $attrCodes;
        }
        return self::$attrCodes;
    }

    /**
     * Initialize attribute option values.
     *
     * @return $this
     */
    protected function _initAttrValues()
    {
        foreach ($this->getAttributeCollection() as $attribute) {
            $this->_attributeValues[$attribute->getAttributeCode()] = $this->getAttributeOptions($attribute);
        }
        return $this;
    }

    /**
     * Apply filter to collection and add not skipped attributes to select.
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareEntityCollection(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection)
    {
        if (!isset(
            $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP]
        ) || !is_array(
            $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP]
        )
        ) {
            $exportFilter = [];
        } else {
            $exportFilter = $this->_parameters[\Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP];
        }
        $exportAttrCodes = $this->_getExportAttrCodes();

        foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
            $attrCode = $attribute->getAttributeCode();

            // filter applying
            if (isset($exportFilter[$attrCode])) {
                $attrFilterType = \Magento\ImportExport\Model\Export::getAttributeFilterType($attribute);

                if (\Magento\ImportExport\Model\Export::FILTER_TYPE_SELECT == $attrFilterType) {
                    if (is_scalar($exportFilter[$attrCode]) && trim($exportFilter[$attrCode])) {
                        $collection->addAttributeToFilter($attrCode, ['eq' => $exportFilter[$attrCode]]);
                    }
                } elseif (\Magento\ImportExport\Model\Export::FILTER_TYPE_INPUT == $attrFilterType) {
                    if (is_scalar($exportFilter[$attrCode]) && trim($exportFilter[$attrCode])) {
                        $collection->addAttributeToFilter($attrCode, ['like' => "%{$exportFilter[$attrCode]}%"]);
                    }
                } elseif (\Magento\ImportExport\Model\Export::FILTER_TYPE_DATE == $attrFilterType) {
                    if (is_array($exportFilter[$attrCode]) && count($exportFilter[$attrCode]) == 2) {
                        $from = array_shift($exportFilter[$attrCode]);
                        $to = array_shift($exportFilter[$attrCode]);

                        if (is_scalar($from) && !empty($from)) {
                            $date = (new \DateTime($from))->format('m/d/Y');
                            $collection->addAttributeToFilter($attrCode, ['from' => $date, 'date' => true]);
                        }
                        if (is_scalar($to) && !empty($to)) {
                            $date = (new \DateTime($to))->format('m/d/Y');
                            $collection->addAttributeToFilter($attrCode, ['to' => $date, 'date' => true]);
                        }
                    }
                } elseif (\Magento\ImportExport\Model\Export::FILTER_TYPE_NUMBER == $attrFilterType) {
                    if (is_array($exportFilter[$attrCode]) && count($exportFilter[$attrCode]) == 2) {
                        $from = array_shift($exportFilter[$attrCode]);
                        $to = array_shift($exportFilter[$attrCode]);

                        if (is_numeric($from)) {
                            $collection->addAttributeToFilter($attrCode, ['from' => $from]);
                        }
                        if (is_numeric($to)) {
                            $collection->addAttributeToFilter($attrCode, ['to' => $to]);
                        }
                    }
                }
            }
            if (in_array($attrCode, $exportAttrCodes)) {
                $collection->addAttributeToSelect($attrCode);
            }
        }
        return $collection;
    }

    /**
     * Add error with corresponding current data source row number.
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number.
     * @return \Magento\ImportExport\Model\Import\AbstractSource
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
     * Add message template for specific error code from outside.
     *
     * @param string $errorCode Error code
     * @param string $message Message template
     * @return \Magento\ImportExport\Model\Import\Entity\AbstractEntity
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
     */
    public function retrieveMessageTemplate($errorCode)
    {
        if (isset($this->_messageTemplates[$errorCode])) {
            return $this->_messageTemplates[$errorCode];
        }
        return null;
    }

    /**
     * Export process.
     *
     * @return string
     */
    abstract public function export();

    /**
     * Clean up attribute collection.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function filterAttributeCollection(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection)
    {
        $collection->load();

        foreach ($collection as $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->_disabledAttrs)) {
                $collection->removeItemByKey($attribute->getId());
            }
        }
        return $collection;
    }

    /**
     * Entity attributes collection getter.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    abstract public function getAttributeCollection();

    /**
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased.
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     */
    public function getAttributeOptions(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute)
    {
        $options = [];

        if ($attribute->usesSource()) {
            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $this->_indexValueAttributes) ? 'value' : 'label';

            // only default (admin) store values used
            $attribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : [$option] as $innerOption) {
                        if (strlen($innerOption['value'])) {
                            // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = (string)$innerOption[$index];
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
     * Returns error information.
     *
     * @return array
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
            $messages[$message] = $errorRows;
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
     * Returns invalid rows count.
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return count($this->_invalidRows);
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
     * Inner writer object getter.
     *
     * @return AbstractAdapter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWriter()
    {
        if (!$this->_writer) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please specify the writer.'));
        }
        return $this->_writer;
    }

    /**
     * Set parameters.
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
     * Writer model setter.
     *
     * @param AbstractAdapter $writer
     * @return $this
     */
    public function setWriter(AbstractAdapter $writer)
    {
        $this->_writer = $writer;

        return $this;
    }

    /**
     * Clean cached values
     */
    public function __destruct()
    {
        self::$attrCodes = null;
    }
}
