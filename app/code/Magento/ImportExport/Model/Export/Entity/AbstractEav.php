<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\ImportExport\Model\Export;
use Magento\Store\Model\Store;

/**
 * Export EAV entity abstract model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class AbstractEav extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    /**
     * Attribute code to its values. Only attributes with options and only default store values used
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributeValues = [];

    /**
     * Entity type id.
     *
     * @var int
     * @since 2.0.0
     */
    protected $_entityTypeId;

    /**
     * Attributes with index (not label) value
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_indexValueAttributes = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @since 2.0.0
     */
    protected $_localeDate;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->_localeDate = $localeDate;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);

        if (isset($data['entity_type_id'])) {
            $this->_entityTypeId = $data['entity_type_id'];
        } else {
            $this->_entityTypeId = $eavConfig->getEntityType($this->getEntityTypeCode())->getEntityTypeId();
        }
    }

    /**
     * Initialize attribute option values
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initAttributeValues()
    {
        /** @var $attribute AbstractAttribute */
        foreach ($this->getAttributeCollection() as $attribute) {
            $this->_attributeValues[$attribute->getAttributeCode()] = $this->getAttributeOptions($attribute);
        }
        return $this;
    }

    /**
     * Apply filter to collection and add not skipped attributes to select
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     * @since 2.0.0
     */
    protected function _prepareEntityCollection(AbstractCollection $collection)
    {
        $this->filterEntityCollection($collection);
        $this->_addAttributesToCollection($collection);
        return $collection;
    }

    /**
     * Apply filter to collection
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function filterEntityCollection(AbstractCollection $collection)
    {
        if (!isset(
            $this->_parameters[Export::FILTER_ELEMENT_GROUP]
        ) || !is_array(
            $this->_parameters[Export::FILTER_ELEMENT_GROUP]
        )
        ) {
            $exportFilter = [];
        } else {
            $exportFilter = $this->_parameters[Export::FILTER_ELEMENT_GROUP];
        }

        /** @var $attribute AbstractAttribute */
        foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            // filter applying
            if (isset($exportFilter[$attributeCode])) {
                $attributeFilterType = Export::getAttributeFilterType($attribute);

                if (Export::FILTER_TYPE_SELECT == $attributeFilterType) {
                    if (is_scalar($exportFilter[$attributeCode]) && trim($exportFilter[$attributeCode])) {
                        $collection->addAttributeToFilter(
                            $attributeCode,
                            ['eq' => $exportFilter[$attributeCode]]
                        );
                    }
                } elseif (Export::FILTER_TYPE_INPUT == $attributeFilterType) {
                    if (is_scalar($exportFilter[$attributeCode]) && trim($exportFilter[$attributeCode])) {
                        $collection->addAttributeToFilter(
                            $attributeCode,
                            ['like' => "%{$exportFilter[$attributeCode]}%"]
                        );
                    }
                } elseif (Export::FILTER_TYPE_DATE == $attributeFilterType) {
                    if (is_array($exportFilter[$attributeCode]) && count($exportFilter[$attributeCode]) == 2) {
                        $from = array_shift($exportFilter[$attributeCode]);
                        $to = array_shift($exportFilter[$attributeCode]);

                        if (is_scalar($from) && !empty($from)) {
                            $date = (new \DateTime($from))->format('m/d/Y');
                            $collection->addAttributeToFilter($attributeCode, ['from' => $date, 'date' => true]);
                        }
                        if (is_scalar($to) && !empty($to)) {
                            $date = (new \DateTime($to))->format('m/d/Y');
                            $collection->addAttributeToFilter($attributeCode, ['to' => $date, 'date' => true]);
                        }
                    }
                } elseif (Export::FILTER_TYPE_NUMBER == $attributeFilterType) {
                    if (is_array($exportFilter[$attributeCode]) && count($exportFilter[$attributeCode]) == 2) {
                        $from = array_shift($exportFilter[$attributeCode]);
                        $to = array_shift($exportFilter[$attributeCode]);

                        if (is_numeric($from)) {
                            $collection->addAttributeToFilter($attributeCode, ['from' => $from]);
                        }
                        if (is_numeric($to)) {
                            $collection->addAttributeToFilter($attributeCode, ['to' => $to]);
                        }
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * Add not skipped attributes to select
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     * @since 2.0.0
     */
    protected function _addAttributesToCollection(AbstractCollection $collection)
    {
        $attributeCodes = $this->_getExportAttributeCodes();
        $collection->addAttributeToSelect($attributeCodes);
        return $collection;
    }

    /**
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased
     *
     * @param AbstractAttribute $attribute
     * @return array
     * @since 2.0.0
     */
    public function getAttributeOptions(AbstractAttribute $attribute)
    {
        $options = [];

        if ($attribute->usesSource()) {
            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $this->_indexValueAttributes) ? 'value' : 'label';

            // only default (admin) store values used
            $attribute->setStoreId(Store::DEFAULT_STORE_ID);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    $optionValues = is_array($option['value']) ? $option['value'] : [$option];
                    foreach ($optionValues as $innerOption) {
                        if (strlen($innerOption['value'])) {
                            // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = $innerOption[$index];
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
     * Entity type ID getter
     *
     * @return int
     * @since 2.0.0
     */
    public function getEntityTypeId()
    {
        return $this->_entityTypeId;
    }

    /**
     * Fill row with attributes values
     *
     * @param \Magento\Framework\Model\AbstractModel $item export entity
     * @param array $row data row
     * @return array
     * @since 2.0.0
     */
    protected function _addAttributeValuesToRow(\Magento\Framework\Model\AbstractModel $item, array $row = [])
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        // go through all valid attribute codes
        foreach ($validAttributeCodes as $attributeCode) {
            $attributeValue = $item->getData($attributeCode);

            if (isset(
                $this->_attributeValues[$attributeCode]
            ) && isset(
                $this->_attributeValues[$attributeCode][$attributeValue]
            )
            ) {
                $attributeValue = $this->_attributeValues[$attributeCode][$attributeValue];
            }
            if (null !== $attributeValue) {
                $row[$attributeCode] = $attributeValue;
            }
        }

        return $row;
    }
}
