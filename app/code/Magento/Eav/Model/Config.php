<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * EAV config model.
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 100.0.2
 */
class Config implements ResetAfterRequestInterface
{
    /**#@+
     * EAV cache ids
     */
    public const ENTITIES_CACHE_ID = 'EAV_ENTITY_TYPES';
    public const ATTRIBUTES_CACHE_ID = 'EAV_ENTITY_ATTRIBUTES';
    public const ATTRIBUTES_CODES_CACHE_ID = 'EAV_ENTITY_ATTRIBUTES_CODES';
    /**#@-*/

    /**
     * Xml path to caching user defined eav attributes configuration.
     */
    private const XML_PATH_CACHE_USER_DEFINED_ATTRIBUTES = 'dev/caching/cache_user_defined_attributes';

    /**
     * @var array|null
     */
    protected $_entityTypeData;

    /**
     * Attributes data
     *
     * @var array
     */
    protected $_attributeData;

    /**
     * Attribute codes cache array
     *
     * @var array
     */
    protected $_attributeCodes;

    /**
     * Initialized objects
     *
     * array ($objectId => $object)
     *
     * @var \Magento\Eav\Model\Entity\Type[]
     */
    protected $_objects;

    /**
     * Initialized attributes
     *
     * [int $website][string $entityTypeCode][string $code] = AbstractAttribute $attribute
     * @var array<int, array<string, array<string, AbstractAttribute>>>
     */
    private $attributes;

    /**
     * References between codes and identifiers
     *
     * array (
     *      'attributes'=> array ($attributeId => $attributeCode),
     *      'entities'  => array ($entityId => $entityCode)
     * )
     *
     * @var array
     */
    protected $_references;

    /**
     * Cache flag
     *
     * @var bool|null
     */
    protected $_isCacheEnabled = null;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_entityTypeFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory
     */
    protected $entityTypeCollectionFactory;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var AbstractAttribute[]
     */
    private $attributeProto = [];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Cache of attributes per set
     *
     * @var array
     */
    private $attributesPerSet = [];

    /**
     * Is system attributes loaded flag.
     *
     * @var array
     */
    private $isSystemAttributesLoaded = [];

    /**
     * List of predefined system attributes for preload.
     *
     * @var array
     */
    private $attributesForPreload;

    /** @var bool[] */
    private array $isAttributeTypeWebsiteSpecificCache = [];

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param Entity\TypeFactory $entityTypeFactory
     * @param ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param SerializerInterface|null $serializer
     * @param ScopeConfigInterface|null $scopeConfig
     * @param array $attributesForPreload
     * @param StoreManagerInterface|null $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        SerializerInterface $serializer = null,
        ScopeConfigInterface $scopeConfig = null,
        $attributesForPreload = [],
        ?StoreManagerInterface $storeManager = null,
    ) {
        $this->_cache = $cache;
        $this->_entityTypeFactory = $entityTypeFactory;
        $this->entityTypeCollectionFactory = $entityTypeCollectionFactory;
        $this->_cacheState = $cacheState;
        $this->_universalFactory = $universalFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->attributesForPreload = $attributesForPreload;
        $this->_storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Get cache interface
     *
     * @return \Magento\Framework\App\CacheInterface
     * @codeCoverageIgnore
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    public function clear()
    {
        $this->_entityTypeData = null;
        $this->_attributeData = null;
        $this->_objects = null;
        $this->attributes = null;
        $this->_references = null;
        $this->_attributeCodes = null;
        $this->attributesPerSet = [];
        $this->_cache->clean(
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );
        return $this;
    }

    /**
     * Get object by identifier
     *
     * @param   mixed $id
     * @return  mixed
     */
    protected function _load($id)
    {
        return $this->_objects[$id] ?? null;
    }

    /**
     * Get attributes by entity type code
     *
     * @param   string $entityTypeCode
     * @return  AbstractAttribute[]
     */
    private function loadAttributes($entityTypeCode)
    {
        if ($this->isAttributeTypeWebsiteSpecific($entityTypeCode)) {
            $websiteId = $this->getWebsiteId();
        } else {
            $websiteId = 0;
        }
        return $this->attributes[$websiteId][$entityTypeCode] ?? [];
    }

    /**
     * Associate object with identifier
     *
     * @param mixed $obj
     * @param mixed $id
     * @return void
     * @codeCoverageIgnore
     */
    protected function _save($obj, $id)
    {
        $this->_objects[$id] = $obj;
    }

    /**
     * Associate object with identifier
     *
     * @param AbstractAttribute $attribute
     * @param string $entityTypeCode
     * @param string $attributeCode
     * @return void
     */
    private function saveAttribute(AbstractAttribute $attribute, $entityTypeCode, $attributeCode)
    {
        if ($this->isAttributeTypeWebsiteSpecific($entityTypeCode)) {
            $websiteId = $this->getWebsiteId();
        } else {
            $websiteId = 0;
        }
        $this->attributes[$websiteId][$entityTypeCode][$attributeCode] = $attribute;
    }

    /**
     * Specify reference for entity type id
     *
     * @param int $id
     * @param string $code
     * @return $this
     * @codeCoverageIgnore
     */
    protected function _addEntityTypeReference($id, $code)
    {
        $this->_references['entity'][$id] = $code;
        return $this;
    }

    /**
     * Get entity type code by id
     *
     * @param   int $id
     * @return  string
     */
    protected function _getEntityTypeReference($id)
    {
        return $this->_references['entity'][$id] ?? null;
    }

    /**
     * Specify reference between entity attribute id and attribute code
     *
     * @param int $id
     * @param string $code
     * @param string $entityTypeCode
     * @return $this
     */
    protected function _addAttributeReference($id, $code, $entityTypeCode)
    {
        $this->_references['attribute'][$entityTypeCode][$id] = $code;
        return $this;
    }

    /**
     * Get attribute code by attribute id
     *
     * @param   int $id
     * @param   string $entityTypeCode
     * @return  string|null
     */
    protected function _getAttributeReference($id, $entityTypeCode)
    {
        if (isset($this->_references['attribute'][$entityTypeCode][$id])) {
            return $this->_references['attribute'][$entityTypeCode][$id];
        }
        return null;
    }

    /**
     * Get internal cache key for entity type code
     *
     * @param   string $code
     * @return  string
     * @codeCoverageIgnore
     */
    protected function _getEntityKey($code)
    {
        return 'ENTITY/' . $code;
    }

    /**
     * Get internal cache key for attribute object cache
     *
     * @param   string $entityTypeCode
     * @param   string $attributeCode
     * @return  string
     * @codeCoverageIgnore
     */
    protected function _getAttributeKey($entityTypeCode, $attributeCode)
    {
        $codeSegments = explode('.', $attributeCode);

        return 'ATTRIBUTE/' . $entityTypeCode . '/' . array_pop($codeSegments);
    }

    /**
     * Check EAV cache availability
     *
     * @return bool
     */
    public function isCacheEnabled()
    {
        if ($this->_isCacheEnabled === null) {
            $this->_isCacheEnabled = $this->_cacheState->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
        }
        return $this->_isCacheEnabled;
    }

    /**
     * Initialize all entity types data
     *
     * @return $this
     */
    protected function _initEntityTypes()
    {
        if (is_array($this->_entityTypeData)) {
            return $this;
        }
        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, ['group' => 'EAV', 'method' => __METHOD__]);

        if ($this->isCacheEnabled() &&
            ($cache = $this->_cache->load(self::ENTITIES_CACHE_ID))
        ) {
            $this->_entityTypeData = $this->serializer->unserialize($cache);
            foreach ($this->_entityTypeData as $typeCode => $data) {
                $typeId = $data['entity_type_id'];
                $this->_addEntityTypeReference($typeId, $typeCode);
            }
            \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
            return $this;
        }

        $entityTypesData = $this->entityTypeCollectionFactory->create()->getData();
        foreach ($entityTypesData as $typeData) {
            if (!isset($typeData['attribute_model'])) {
                $typeData['attribute_model'] = \Magento\Eav\Model\Entity\Attribute::class;
            }

            $typeCode = $typeData['entity_type_code'];
            $typeId = $typeData['entity_type_id'];

            $this->_addEntityTypeReference($typeId, $typeCode);
            $this->_entityTypeData[$typeCode] = $typeData;
        }

        if ($this->isCacheEnabled() && !empty($this->_entityTypeData)) {
            $this->_cache->save(
                $this->serializer->serialize($this->_entityTypeData),
                self::ENTITIES_CACHE_ID,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                ]
            );
        }
        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $this;
    }

    /**
     * Get entity type object by entity type code/identifier
     *
     * @param int|string|Type $code
     * @return Type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityType($code)
    {
        if ($code instanceof Type) {
            return $code;
        }
        $this->_initEntityTypes();
        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, ['group' => 'EAV', 'method' => __METHOD__]);

        if (is_numeric($code)) {
            $entityCode = $this->_getEntityTypeReference($code);
            if ($entityCode !== null) {
                $code = $entityCode;
            }
        }

        $entityKey = $this->_getEntityKey($code);
        $entityType = $this->_load($entityKey);
        if ($entityType) {
            \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
            return $entityType;
        }

        $entityType = $this->_entityTypeFactory->create(
            ['data' => isset($this->_entityTypeData[$code]) ? $this->_entityTypeData[$code] : []]
        );
        if (!$entityType->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid entity_type specified: %1', $code));
        }
        $this->_addEntityTypeReference($entityType->getId(), $entityType->getEntityTypeCode());
        $this->_save($entityType, $entityKey);

        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $entityType;
    }

    /**
     * Initialize all attributes for entity type
     *
     * @param string $entityType
     * @return $this
     */
    protected function _initAttributes($entityType)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();

        if (is_array($this->_attributeData) && isset($this->_attributeData[$entityTypeCode])) {
            return $this;
        }

        $entityTypeCode = $entityType->getEntityTypeCode();
        $attributes = $this->_universalFactory->create($entityType->getEntityAttributeCollection());
        $websiteId = $attributes instanceof Collection ? $this->getWebsiteIdFromAttributeCollection($attributes) : 0;
        $cacheKey = self::ATTRIBUTES_CACHE_ID . '-' . $entityTypeCode . '-' . $websiteId ;

        if ($this->isCacheEnabled() && $this->initAttributesFromCache($entityType, $cacheKey)) {
            return $this;
        }

        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, ['group' => 'EAV', 'method' => __METHOD__]);

        $attributes = $attributes->setEntityTypeFilter($entityType)
            ->getData();

        $this->_attributeData[$entityTypeCode] = [];
        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_model'])) {
                $attribute['attribute_model'] = $entityType->getAttributeModel();
            }
            $attributeObject = $this->_createAttribute($entityType, $attribute);
            $this->saveAttribute($attributeObject, $entityTypeCode, $attributeObject->getAttributeCode());
            $this->_attributeData[$entityTypeCode][$attribute['attribute_code']] = $attributeObject->toArray();
        }
        if ($this->isCacheEnabled()) {
            $this->_cache->save(
                $this->serializer->serialize($this->_attributeData[$entityTypeCode]),
                $cacheKey,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                ]
            );
        }

        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $this;
    }

    /**
     * Get attributes by entity type
     *
     * @deprecated 101.0.0
     * @see \Magento\Eav\Model\Config::getEntityAttributes
     *
     * @param string $entityType
     * @return AbstractAttribute[]
     * @since 101.0.0
     */
    public function getAttributes($entityType)
    {
        return $this->getEntityAttributes($entityType);
    }

    /**
     * Get attribute by code for entity type
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param mixed $entityType
     * @param mixed $code
     * @return AbstractAttribute
     * @throws LocalizedException
     */
    public function getAttribute($entityType, $code)
    {
        if ($this->isAttributeTypeWebsiteSpecific($entityType)) {
            $websiteId = $this->getWebsiteId();
        } else {
            $websiteId = 0;
        }
        if ($code instanceof \Magento\Eav\Model\Entity\Attribute\AttributeInterface) {
            return $code;
        }

        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, ['group' => 'EAV', 'method' => __METHOD__]);
        $entityTypeCode = $this->getEntityType($entityType)->getEntityTypeCode();

        if (is_numeric($code)) { // if code is numeric, try to map attribute id to code
            $code = $this->_getAttributeReference($code, $entityTypeCode) ?: $code;
        }

        if (isset($this->attributes[$websiteId][$entityTypeCode][$code])) {
            \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
            return $this->attributes[$websiteId][$entityTypeCode][$code];
        }

        if (array_key_exists($entityTypeCode, $this->attributesForPreload)
            && array_key_exists($code, $this->attributesForPreload[$entityTypeCode])
        ) {
            $this->initSystemAttributes($entityType, $this->attributesForPreload[$entityTypeCode]);
        }
        if (isset($this->attributes[$websiteId][$entityTypeCode][$code])) {
            \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
            return $this->attributes[$websiteId][$entityTypeCode][$code];
        }

        if ($this->scopeConfig->getValue(self::XML_PATH_CACHE_USER_DEFINED_ATTRIBUTES)) {
            $attribute = $this->cacheUserDefinedAttribute($entityType, $entityTypeCode, $code);
        } else {
            $attribute = $this->initUserDefinedAttribute($entityType, $entityTypeCode, $code);
        }

        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        return $attribute;
    }

    /**
     * Initialize predefined system attributes for preload.
     *
     * @param string $entityType
     * @param array $systemAttributes
     * @return $this|bool|void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function initSystemAttributes($entityType, $systemAttributes)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();
        if (!empty($this->isSystemAttributesLoaded[$entityTypeCode])) {
            return;
        }
        $attributeCollection = $this->_universalFactory->create($entityType->getEntityAttributeCollection());
        $websiteId = $attributeCollection instanceof Collection
            ? $this->getWebsiteIdFromAttributeCollection($attributeCollection) : 0;
        $cacheKey = self::ATTRIBUTES_CACHE_ID . '-' . $entityTypeCode . '-' . $websiteId . '-preload';
        if ($this->isCacheEnabled() && ($attributes = $this->_cache->load($cacheKey))) {
            $attributes = $this->serializer->unserialize($attributes);
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    $attributeObject = $this->_createAttribute($entityType, $attribute);
                    $this->saveAttribute($attributeObject, $entityTypeCode, $attributeObject->getAttributeCode());
                }
                return true;
            }
        }

        \Magento\Framework\Profiler::start('EAV: ' . __METHOD__, ['group' => 'EAV', 'method' => __METHOD__]);

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributes */
        $attributes = $attributeCollection->setEntityTypeFilter(
            $entityType
        )->addFieldToFilter(
            'attribute_code',
            ['in' => array_keys($systemAttributes)]
        )->getData();

        $attributeData = [];
        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_model'])) {
                $attribute['attribute_model'] = $entityType->getAttributeModel();
            }
            $attributeObject = $this->_createAttribute($entityType, $attribute);
            $this->saveAttribute($attributeObject, $entityTypeCode, $attributeObject->getAttributeCode());
            $attributeData[$attribute['attribute_code']] = $attributeObject->toArray();
        }
        if ($this->isCacheEnabled()) {
            $this->_cache->save(
                $this->serializer->serialize($attributeData),
                $cacheKey,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
                ]
            );
        }

        \Magento\Framework\Profiler::stop('EAV: ' . __METHOD__);
        $this->isSystemAttributesLoaded[$entityTypeCode] = true;

        return $this;
    }

    /**
     * Initialize user defined attribute from cache or cache it.
     *
     * @param string $entityType
     * @param mixed $entityTypeCode
     * @param string $code
     * @return AbstractAttribute
     * @throws LocalizedException
     */
    private function cacheUserDefinedAttribute($entityType, $entityTypeCode, $code): AbstractAttribute
    {
        $cacheKey = self::ATTRIBUTES_CACHE_ID . '-attribute-' . $entityTypeCode . '-' . $code;
        $attributeData = $this->isCacheEnabled() && ($attribute = $this->_cache->load($cacheKey))
            ? $this->serializer->unserialize($attribute)
            : null;
        if ($attributeData) {
            if (isset($attributeData['attribute_id'])) {
                $attribute = $this->_createAttribute($entityType, $attributeData);
            } else {
                $entityType = $this->getEntityType($entityType);
                $attribute = $this->createAttribute($entityType->getAttributeModel());
                $attribute->setAttributeCode($code);
                $attribute = $this->setAttributeData($attribute, $entityType);
            }
        } else {
            $attribute = $this->createAttributeByAttributeCode($entityType, $code);
            $this->_addAttributeReference(
                $attribute->getAttributeId(),
                $attribute->getAttributeCode(),
                $entityTypeCode
            );
            $this->saveAttribute($attribute, $entityTypeCode, $attribute->getAttributeCode());
            if ($this->isCacheEnabled()) {
                $this->_cache->save(
                    $this->serializer->serialize($attribute->getData()),
                    $cacheKey,
                    [
                        \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                        \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                    ]
                );
            }
        }

        return $attribute;
    }

    /**
     * Initialize user defined attribute and save it to memory cache.
     *
     * @param mixed $entityType
     * @param string $entityTypeCode
     * @param string $code
     * @return AbstractAttribute|null
     * @throws LocalizedException
     */
    private function initUserDefinedAttribute($entityType, $entityTypeCode, $code): ?AbstractAttribute
    {
        $attributes = $this->loadAttributes($entityTypeCode);
        $attribute = $attributes[$code] ?? null;
        if (!$attribute) {
            $attribute = $this->createAttributeByAttributeCode($entityType, $code);
            $this->_addAttributeReference(
                $attribute->getAttributeId(),
                $attribute->getAttributeCode(),
                $entityTypeCode
            );
            $this->saveAttribute($attribute, $entityTypeCode, $attribute->getAttributeCode());
        }

        return $attribute;
    }

    /**
     * Create attribute
     *
     * @param string $model
     * @return Entity\Attribute\AbstractAttribute
     */
    private function createAttribute($model)
    {
        if (!isset($this->attributeProto[$model])) {
            /** @var AbstractAttribute $attribute */
            $this->attributeProto[$model] = $this->_universalFactory->create($model);
        }
        return clone $this->attributeProto[$model];
    }

    /**
     * Get codes of all entity type attributes
     *
     * @deprecated 101.0.0
     * @see \Magento\Eav\Model\Config::getEntityAttributes
     *
     * @param mixed $entityType
     * @param \Magento\Framework\DataObject $object
     * @return string[]
     */
    public function getEntityAttributeCodes($entityType, $object = null)
    {
        return array_keys($this->getEntityAttributes($entityType, $object));
    }

    /**
     * Get all entity type attributes
     *
     * @param int|string|Type $entityType
     * @param \Magento\Framework\DataObject|null $object
     * @return AbstractAttribute[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 101.0.0
     */
    public function getEntityAttributes($entityType, $object = null)
    {
        $entityType = $this->getEntityType($entityType);
        $attributeSetId = 0;
        $storeId = 0;
        if ($object instanceof \Magento\Framework\DataObject) {
            $attributeSetId = $object->getAttributeSetId() ?: $attributeSetId;
            $storeId = $object->getStoreId() ?: $storeId;
        }
        $cacheKey = self::ATTRIBUTES_CACHE_ID . '-' . $entityType->getId() . '-' . $storeId . '-' . $attributeSetId;

        if (isset($this->attributesPerSet[$cacheKey])) {
            return $this->attributesPerSet[$cacheKey];
        }

        $attributeCollection = $this->_universalFactory->create($entityType->getEntityAttributeCollection());
        // If entity contains website-dependent attributes, the result should not be cached here.
        // Website in collection is resolved by StoreManager which causes incorrect entity attributes caching when
        // the entity is loaded from the backend first time after the cache cleanup.
        $isEntityWebsiteDependent = $attributeCollection instanceof Collection;
        $attributesData = null;
        if ($this->isCacheEnabled() && !$isEntityWebsiteDependent && ($attributes = $this->_cache->load($cacheKey))) {
            $attributesData = $this->serializer->unserialize($attributes);
        }

        $attributes = [];
        if ($attributesData === null) {
            if ($attributeSetId) {
                $attributesData = $attributeCollection->setEntityTypeFilter(
                    $entityType
                )->setAttributeSetFilter(
                    $attributeSetId
                )->addStoreLabel(
                    $storeId
                )->getData();
            } else {
                $this->_initAttributes($entityType);
                $attributesData = $this->_attributeData[$entityType->getEntityTypeCode()];
            }

            if ($this->isCacheEnabled() && !$isEntityWebsiteDependent) {
                $this->_cache->save(
                    $this->serializer->serialize($attributesData),
                    $cacheKey,
                    [
                        \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                        \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                    ]
                );
            }
        }

        foreach ($attributesData as $attributeData) {
            $attributes[$attributeData['attribute_code']] = $this->_createAttribute($entityType, $attributeData);
        }

        $this->attributesPerSet[$cacheKey] = $attributes;

        return $attributes;
    }

    /**
     * Create attribute from attribute data array
     *
     * @param string $entityType
     * @param array $attributeData
     * @return AbstractAttribute
     */
    protected function _createAttribute($entityType, $attributeData)
    {
        $entityType = $this->getEntityType($entityType);
        $entityTypeCode = $entityType->getEntityTypeCode();

        $code = $attributeData['attribute_code'];
        $attributes = $this->loadAttributes($entityTypeCode);
        $attribute = isset($attributes[$code]) ? $attributes[$code] : null;
        if ($attribute) {
            $existsFullAttribute = $attribute->hasIsRequired();
            $fullAttributeData = array_key_exists('is_required', $attributeData);

            if ($existsFullAttribute || (!$existsFullAttribute && !$fullAttributeData)) {
                $scopeIsRequired = $attributeData['scope_is_required'] ?? null;
                if ($scopeIsRequired !== null) {
                    $attribute->setData('scope_is_required', $scopeIsRequired);
                }
                return $attribute;
            }
        }

        if (!empty($attributeData['attribute_model'])) {
            $model = $attributeData['attribute_model'];
        } else {
            $model = $entityType->getAttributeModel();
        }
        /** @var AbstractAttribute $attribute */
        $attribute = $this->createAttribute($model)->setData($attributeData);
        $attribute->setOrigData('entity_type_id', $attribute->getEntityTypeId());
        $this->_addAttributeReference(
            $attributeData['attribute_id'],
            $code,
            $entityTypeCode
        );
        $this->saveAttribute($attribute, $entityTypeCode, $code);

        return $attribute;
    }

    /**
     * Validate attribute data from import
     *
     * @param array $attributeData
     * @return bool
     */
    protected function _validateAttributeData($attributeData = null)
    {
        if (!is_array($attributeData)) {
            return false;
        }
        $requiredKeys = ['attribute_id', 'attribute_code', 'entity_type_id', 'attribute_model'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $attributeData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Import attributes data from external source
     *
     * @param string|Type $entityType
     * @param array $attributes
     * @return $this
     */
    public function importAttributesData($entityType, array $attributes)
    {
        $entityType = $this->getEntityType($entityType);
        foreach ($attributes as $attributeData) {
            if (!$this->_validateAttributeData($attributeData)) {
                continue;
            }
            $this->_createAttribute($entityType, $attributeData);
        }

        return $this;
    }

    /**
     * Create attribute by attribute code
     *
     * @param string|Type $entityType
     * @param string $attributeCode
     * @return AbstractAttribute
     * @throws LocalizedException
     */
    private function createAttributeByAttributeCode($entityType, $attributeCode)
    {
        $entityType = $this->getEntityType($entityType);
        $attribute = $this->createAttribute($entityType->getAttributeModel());
        if (is_numeric($attributeCode)) {
            $attribute->load($attributeCode);
            if ($attribute->getEntityTypeId() != $entityType->getId()) {
                $attribute = $this->createAttribute($entityType->getAttributeModel());
            }
        } else {
            $attribute->loadByCode($entityType->getEntityTypeId(), $attributeCode);
            $attribute->setAttributeCode($attributeCode);
        }

        $attribute = $this->setAttributeData($attribute, $entityType);

        return $attribute;
    }

    /**
     * Set entity type id, backend type, is global to attribute.
     *
     * @param AbstractAttribute $attribute
     * @param AbstractModel $entityType
     * @return AbstractAttribute
     */
    private function setAttributeData($attribute, $entityType): AbstractAttribute
    {
        $entity = $entityType->getEntity();
        if ($entity instanceof ProviderInterface
            && in_array($attribute->getAttributeCode(), $entity->getDefaultAttributes(), true)
        ) {
            $attribute->setBackendType(AbstractAttribute::TYPE_STATIC)->setIsGlobal(1);
        }
        $attribute->setEntityType($entityType)->setEntityTypeId($entityType->getId());

        return $attribute;
    }

    /**
     * Initialize attributes from cache for given entity type
     *
     * @param Type $entityType
     * @param string $cacheKey
     * @return bool
     */
    private function initAttributesFromCache(Type $entityType, string $cacheKey)
    {
        $entityTypeCode = $entityType->getEntityTypeCode();
        $attributes = $this->_cache->load($cacheKey);
        if ($attributes) {
            $attributes = $this->serializer->unserialize($attributes);
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    $this->_createAttribute($entityType, $attribute);
                    $this->_attributeData[$entityTypeCode][$attribute['attribute_code']] = $attribute;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Returns website id from attribute collection.
     *
     * @param Collection $attributeCollection
     * @return int
     */
    private function getWebsiteIdFromAttributeCollection(Collection $attributeCollection): int
    {
        return (int)$attributeCollection->getWebsite()?->getId();
    }

    /**
     * Return current website scope instance
     *
     * @return int website id
     */
    public function getWebsiteId() : int
    {
        $websiteId = $this->_storeManager->getStore()?->getWebsiteId();
        return (int)$websiteId;
    }

    /**
     * Returns true if $entityType has website-specific options.
     *
     * Most attributes are global, but some can have website-specific options.
     *
     * @param string|Type $entityType
     * @return bool
     */
    private function isAttributeTypeWebsiteSpecific(string|Type $entityType) : bool
    {
        if ($entityType instanceof Type) {
            $entityTypeCode = $entityType->getEntityTypeCode();
        } else {
            $entityTypeCode = $entityType;
        }
        if (key_exists($entityTypeCode, $this->isAttributeTypeWebsiteSpecificCache)) {
            return $this->isAttributeTypeWebsiteSpecificCache[$entityTypeCode];
        }
        $entityType = $this->getEntityType($entityType);
        $model = $entityType->getAttributeModel();
        $returnValue = is_a($model, \Magento\Eav\Model\Attribute::class, true);
        $this->isAttributeTypeWebsiteSpecificCache[$entityTypeCode] = $returnValue;
        return $returnValue;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->isAttributeTypeWebsiteSpecificCache = [];
        $this->attributesPerSet = [];
        $this->_attributeData = null;
        foreach ($this->attributes ?? [] as $attributesGroupedByWebsites) {
            foreach ($attributesGroupedByWebsites as $attributesGroupedByEntityTypeCode) {
                foreach ($attributesGroupedByEntityTypeCode as $attribute) {
                    if ($attribute instanceof ResetAfterRequestInterface) {
                        $attribute->_resetState();
                    }
                }
            }
        }
    }
}
