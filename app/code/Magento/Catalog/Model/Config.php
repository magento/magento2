<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Config extends \Magento\Eav\Model\Config
{
    const XML_PATH_LIST_DEFAULT_SORT_BY = 'catalog/frontend/default_sort_by';

    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $_attributeSetsById;

    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $_attributeSetsByName;

    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $_attributeGroupsById;

    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $_attributeGroupsByName;

    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $_productTypesById;

    /**
     * Array of attributes codes needed for product load
     *
     * @var array
     * @since 2.0.0
     */
    protected $_productAttributes;

    /**
     * Product Attributes used in product listing
     *
     * @var array
     * @since 2.0.0
     */
    protected $_usedInProductListing;

    /**
     * Product Attributes For Sort By
     *
     * @var array
     * @since 2.0.0
     */
    protected $_usedForSortBy;

    /**
     * @var int|float|string|null
     * @since 2.0.0
     */
    protected $_storeId = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $_eavConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     * @since 2.0.0
     */
    protected $_setCollectionFactory;

    /**
     * Group collection factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     * @since 2.0.0
     */
    protected $_groupCollectionFactory;

    /**
     * Product type factory
     *
     * @var \Magento\Catalog\Model\Product\TypeFactory
     * @since 2.0.0
     */
    protected $_productTypeFactory;

    /**
     * Config factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\ConfigFactory
     * @since 2.0.0
     */
    protected $_configFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\ConfigFactory $configFactory
     * @param \Magento\Catalog\Model\Product\TypeFactory $productTypeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param SerializerInterface $serializer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $configFactory,
        \Magento\Catalog\Model\Product\TypeFactory $productTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        SerializerInterface $serializer = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_configFactory = $configFactory;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->_setCollectionFactory = $setCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;

        parent::__construct(
            $cache,
            $entityTypeFactory,
            $entityTypeCollectionFactory,
            $cacheState,
            $universalFactory,
            $serializer
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Config::class);
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return \Magento\Catalog\Model\Config
     * @since 2.0.0
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id, if is not set return current app store
     *
     * @return integer
     * @since 2.0.0
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadAttributeSets()
    {
        if ($this->_attributeSetsById) {
            return $this;
        }

        $attributeSetCollection = $this->_setCollectionFactory->create()->load();

        $this->_attributeSetsById = [];
        $this->_attributeSetsByName = [];
        foreach ($attributeSetCollection as $id => $attributeSet) {
            $entityTypeId = $attributeSet->getEntityTypeId();
            $name = $attributeSet->getAttributeSetName();
            $this->_attributeSetsById[$entityTypeId][$id] = $name;
            $this->_attributeSetsByName[$entityTypeId][strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param string|int|float $entityTypeId
     * @param float|int $id
     * @return false|string
     * @since 2.0.0
     */
    public function getAttributeSetName($entityTypeId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        return isset(
            $this->_attributeSetsById[$entityTypeId][$id]
        ) ? $this->_attributeSetsById[$entityTypeId][$id] : false;
    }

    /**
     * @param string|int|float $entityTypeId
     * @param string|null $name
     * @return false|string|int
     * @since 2.0.0
     */
    public function getAttributeSetId($entityTypeId, $name = null)
    {
        if (is_numeric($name)) {
            return $name;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        $name = strtolower($name);
        return isset(
            $this->_attributeSetsByName[$entityTypeId][$name]
        ) ? $this->_attributeSetsByName[$entityTypeId][$name] : false;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadAttributeGroups()
    {
        if ($this->_attributeGroupsById) {
            return $this;
        }

        $attributeSetCollection = $this->_groupCollectionFactory->create()->load();

        $this->_attributeGroupsById = [];
        $this->_attributeGroupsByName = [];
        foreach ($attributeSetCollection as $id => $attributeGroup) {
            $attributeSetId = $attributeGroup->getAttributeSetId();
            $name = $attributeGroup->getAttributeGroupName();
            $this->_attributeGroupsById[$attributeSetId][$id] = $name;
            $this->_attributeGroupsByName[$attributeSetId][strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param float|int|string $attributeSetId
     * @param float|int|string $id
     * @return bool|string
     * @since 2.0.0
     */
    public function getAttributeGroupName($attributeSetId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        return isset(
            $this->_attributeGroupsById[$attributeSetId][$id]
        ) ? $this->_attributeGroupsById[$attributeSetId][$id] : false;
    }

    /**
     * @param float|int|string $attributeSetId
     * @param string $name
     * @return bool|string|int|float
     * @since 2.0.0
     */
    public function getAttributeGroupId($attributeSetId, $name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        $name = strtolower($name);
        return isset(
            $this->_attributeGroupsByName[$attributeSetId][$name]
        ) ? $this->_attributeGroupsByName[$attributeSetId][$name] : false;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadProductTypes()
    {
        if ($this->_productTypesById) {
            return $this;
        }

        $productTypeCollection = $this->_productTypeFactory->create()->getOptionArray();

        $this->_productTypesById = [];
        $this->_productTypesByName = [];
        foreach ($productTypeCollection as $id => $type) {
            $name = $type;
            $this->_productTypesById[$id] = $name;
            $this->_productTypesByName[strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return false|string
     * @since 2.0.0
     */
    public function getProductTypeId($name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadProductTypes();

        $name = strtolower($name);
        return isset($this->_productTypesByName[$name]) ? $this->_productTypesByName[$name] : false;
    }

    /**
     * @param float|int|string $id
     * @return false|string
     * @since 2.0.0
     */
    public function getProductTypeName($id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadProductTypes();

        return isset($this->_productTypesById[$id]) ? $this->_productTypesById[$id] : false;
    }

    /**
     * @param \Magento\Framework\DataObject $source
     * @param string $value
     * @return null|mixed
     * @since 2.0.0
     */
    public function getSourceOptionId($source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value) == 0 || $option['value'] == $value) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * Load Product attributes
     *
     * @return array
     * @since 2.0.0
     */
    public function getProductAttributes()
    {
        if (is_null($this->_productAttributes)) {
            $this->_productAttributes = array_keys($this->getAttributesUsedInProductListing());
        }
        return $this->_productAttributes;
    }

    /**
     * Retrieve resource model
     *
     * @return \Magento\Catalog\Model\ResourceModel\Config
     * @since 2.0.0
     */
    protected function _getResource()
    {
        return $this->_configFactory->create();
    }

    /**
     * Retrieve Attributes used in product listing
     *
     * @return array
     * @since 2.0.0
     */
    public function getAttributesUsedInProductListing()
    {
        if (is_null($this->_usedInProductListing)) {
            $this->_usedInProductListing = [];
            $entityType = \Magento\Catalog\Model\Product::ENTITY;
            $attributesData = $this->_getResource()->setStoreId($this->getStoreId())->getAttributesUsedInListing();
            $this->_eavConfig->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedInProductListing[$attributeCode] = $this->_eavConfig->getAttribute(
                    $entityType,
                    $attributeCode
                );
            }
        }
        return $this->_usedInProductListing;
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @return array
     * @since 2.0.0
     */
    public function getAttributesUsedForSortBy()
    {
        if (is_null($this->_usedForSortBy)) {
            $this->_usedForSortBy = [];
            $entityType = \Magento\Catalog\Model\Product::ENTITY;
            $attributesData = $this->_getResource()->getAttributesUsedForSortBy();
            $this->_eavConfig->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedForSortBy[$attributeCode] = $this->_eavConfig->getAttribute($entityType, $attributeCode);
            }
        }
        return $this->_usedForSortBy;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     * @since 2.0.0
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = ['position' => __('Position')];
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }

    /**
     * Retrieve Product List Default Sort By
     *
     * @param mixed $store
     * @return string
     * @since 2.0.0
     */
    public function getProductListDefaultSortBy($store = null)
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LIST_DEFAULT_SORT_BY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
