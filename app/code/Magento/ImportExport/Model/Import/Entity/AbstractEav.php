<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Entity;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Import EAV entity abstract model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class AbstractEav extends \Magento\ImportExport\Model\Import\AbstractEntity
{
    /**
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Framework\Data\Collection::class;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Entity type id
     *
     * @var int
     * @since 2.0.0
     */
    protected $_entityTypeId;

    /**
     * Attributes with index (not label) value
     *
     * @var array
     * @since 2.0.0
     */
    protected $_indexValueAttributes = [];

    /**
     * Website code-to-ID
     *
     * @var array
     * @since 2.0.0
     */
    protected $_websiteCodeToId = [];

    /**
     * All stores code-ID pairs.
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeCodeToId = [];

    /**
     * Entity attributes parameters
     *
     *  [attr_code_1] => array(
     *      'options' => array(),
     *      'type' => 'text', 'price', 'textarea', 'select', etc.
     *      'id' => ..
     *  ),
     *  ...
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributes = [];

    /**
     * Attributes collection
     *
     * @var \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    protected $_attributeCollection;

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
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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
        array $data = []
    ) {
        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);

        $this->_storeManager = $storeManager;
        $this->_attributeCollection = isset(
            $data['attribute_collection']
        ) ? $data['attribute_collection'] : $collectionFactory->create(
            static::ATTRIBUTE_COLLECTION_NAME
        );

        if (isset($data['entity_type_id'])) {
            $this->_entityTypeId = $data['entity_type_id'];
        } else {
            $this->_entityTypeId = $eavConfig->getEntityType($this->getEntityTypeCode())->getEntityTypeId();
        }
    }

    /**
     * Retrieve website id by code or false when website code not exists
     *
     * @param string $websiteCode
     * @return int|false
     * @since 2.0.0
     */
    public function getWebsiteId($websiteCode)
    {
        if (isset($this->_websiteCodeToId[$websiteCode])) {
            return $this->_websiteCodeToId[$websiteCode];
        }

        return false;
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
            $this->_websiteCodeToId[$website->getCode()] = $website->getId();
        }
        return $this;
    }

    /**
     * Initialize stores data
     *
     * @param bool $withDefault
     * @return $this
     * @since 2.0.0
     */
    protected function _initStores($withDefault = false)
    {
        /** @var $store \Magento\Store\Model\Store */
        foreach ($this->_storeManager->getStores($withDefault) as $store) {
            $this->_storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }

    /**
     * Initialize entity attributes
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initAttributes()
    {
        /** @var $attribute \Magento\Eav\Model\Attribute */
        foreach ($this->_attributeCollection as $attribute) {
            $this->_attributes[$attribute->getAttributeCode()] = [
                'id' => $attribute->getId(),
                'code' => $attribute->getAttributeCode(),
                'table' => $attribute->getBackend()->getTable(),
                'is_required' => $attribute->getIsRequired(),
                'is_static' => $attribute->isStatic(),
                'rules' => $attribute->getValidateRules() ? $attribute->getValidateRules() : null,
                'type' => \Magento\ImportExport\Model\Import::getAttributeType($attribute),
                'options' => $this->getAttributeOptions($attribute),
            ];
            $this->validColumnNames[] = $attribute->getAttributeCode();
        }
        return $this;
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
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param array $indexAttributes OPTIONAL Additional attribute codes with index values.
     * @return array
     * @since 2.0.0
     */
    public function getAttributeOptions(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        array $indexAttributes = []
    ) {
        $options = [];

        if ($attribute->usesSource()) {
            // merge global entity index value attributes
            $indexAttributes = array_merge($indexAttributes, $this->_indexValueAttributes);

            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $indexAttributes) ? 'value' : 'label';

            // only default (admin) store values used
            $attribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    $value = is_array($option['value']) ? $option['value'] : [$option];
                    foreach ($value as $innerOption) {
                        // skip ' -- Please Select -- ' option
                        if (strlen($innerOption['value'])) {
                            if ($attribute->isStatic()) {
                                $options[strtolower($innerOption[$index])] = $innerOption['value'];
                            } else {
                                // Non-static attributes flip keys an values
                                $options[$innerOption['value']] = $innerOption[$index];
                            }
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
     * Get attribute collection
     *
     * @return \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    public function getAttributeCollection()
    {
        return $this->_attributeCollection;
    }
}
