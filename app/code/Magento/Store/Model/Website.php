<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;

/**
 * Core Website model
 *
 * @api
 * @method string getGroupTitle()
 * @method string getStoreTitle()
 * @method int getStoreId()
 * @method int getGroupId()
 * @method int getWebsiteId()
 * @method bool hasWebsiteId()
 * @method int getSortOrder()
 * @method \Magento\Store\Model\Website setSortOrder($value)
 * @method int getIsDefault()
 * @method \Magento\Store\Model\Website setIsDefault($value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Website extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Magento\Framework\App\ScopeInterface,
    \Magento\Store\Api\Data\WebsiteInterface
{
    public const ENTITY = 'store_website';

    public const CACHE_TAG = 'website';

    /**
     * @var bool
     */
    protected $_cacheTag = true;

    /**
     * @var string
     */
    protected $_eventPrefix = 'website';

    /**
     * @var string
     */
    protected $_eventObject = 'website';

    /**
     * Cache configuration array
     *
     * @var array
     */
    protected $_configCache = [];

    /**
     * Website Group Collection array
     *
     * @var \Magento\Store\Model\Store[]
     */
    protected $_groups;

    /**
     * Website group ids array
     *
     * @var array
     */
    protected $_groupIds = [];

    /**
     * The number of groups in a website
     *
     * @var int
     */
    protected $_groupsCount;

    /**
     * Website Store collection array
     *
     * @var array
     */
    protected $_stores;

    /**
     * Website store ids array
     *
     * @var array
     */
    protected $_storeIds = [];

    /**
     * Website store codes array
     *
     * @var array
     */
    protected $_storeCodes = [];

    /**
     * The number of stores in a website
     *
     * @var int
     */
    protected $_storesCount = 0;

    /**
     * Website default group
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_defaultGroup;

    /**
     * Website default store
     *
     * @var Store
     */
    protected $_defaultStore;

    /**
     * is can delete website
     *
     * @var bool
     */
    protected $_isCanDelete;

    /**
     * @var bool
     */
    private $_isReadOnly = false;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data
     */
    protected $_configDataResource;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeListFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_storeGroupFactory;

    /**
     * @var WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var PoisonPillPutInterface
     */
    private $pillPut;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_coreConfig;

    /**
     * @var TypeListInterface
     */
    private TypeListInterface $typeList;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $configDataResource
     * @param ScopeConfigInterface $coreConfig
     * @param CollectionFactory $storeListFactory
     * @param GroupFactory $storeGroupFactory
     * @param WebsiteFactory $websiteFactory
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param PoisonPillPutInterface|null $pillPut
     * @param TypeListInterface|null $typeList
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeListFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?PoisonPillPutInterface $pillPut = null,
        ?TypeListInterface $typeList = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_configDataResource = $configDataResource;
        $this->_coreConfig = $coreConfig;
        $this->storeListFactory = $storeListFactory;
        $this->_storeGroupFactory = $storeGroupFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->pillPut = $pillPut ?: ObjectManager::getInstance()->get(PoisonPillPutInterface::class);
        $this->typeList = $typeList ?: ObjectManager::getInstance()->get(TypeListInterface::class);
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Store\Model\ResourceModel\Website::class);
    }

    /**
     * Custom load
     *
     * @param int|string $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if (!is_numeric($id) && $field === null) {
            $this->_getResource()->load($this, $id, 'code');
            return $this;
        }
        return parent::load($id, $field);
    }

    /**
     * Get website config data
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        if (!isset($this->_configCache[$path])) {
            $config = $this->_coreConfig->getValue(
                $path,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $this->getCode()
            );
            if (!$config) {
                return false;
            }
            $this->_configCache[$path] = $config;
        }
        return $this->_configCache[$path];
    }

    /**
     * Load group collection and set internal data
     *
     * @return void
     */
    protected function _loadGroups()
    {
        $this->_groups = [];
        $this->_groupsCount = 0;
        foreach ($this->getGroupCollection() as $group) {
            $this->_groups[$group->getId()] = $group;
            $this->_groupIds[$group->getId()] = $group->getId();
            if ($this->getDefaultGroupId() == $group->getId()) {
                $this->_defaultGroup = $group;
            }
            $this->_groupsCount++;
        }
    }

    /**
     * Set website groups
     *
     * @param array $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->_groups = [];
        $this->_groupsCount = 0;
        foreach ($groups as $group) {
            $this->_groups[$group->getId()] = $group;
            $this->_groupIds[$group->getId()] = $group->getId();
            if ($this->getDefaultGroupId() == $group->getId()) {
                $this->_defaultGroup = $group;
            }
            $this->_groupsCount++;
        }
        return $this;
    }

    /**
     * Retrieve new (not loaded) Group collection object with website filter
     *
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroupCollection()
    {
        return $this->_storeGroupFactory->create()->getCollection()->addWebsiteFilter($this->getId())
            ->setLoadDefault(true);
    }

    /**
     * Retrieve website groups
     *
     * @return \Magento\Store\Model\Store[]
     */
    public function getGroups()
    {
        if ($this->_groups === null) {
            $this->_loadGroups();
        }
        return $this->_groups;
    }

    /**
     * Retrieve website group ids
     *
     * @return array
     */
    public function getGroupIds()
    {
        if ($this->_groups === null) {
            $this->_loadGroups();
        }
        return $this->_groupIds;
    }

    /**
     * Retrieve number groups in a website
     *
     * @return int
     */
    public function getGroupsCount()
    {
        if ($this->_groups === null) {
            $this->_loadGroups();
        }
        return $this->_groupsCount;
    }

    /**
     * Retrieve default group model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getDefaultGroup()
    {
        if (!$this->hasDefaultGroupId()) {
            return false;
        }
        if ($this->_groups === null) {
            $this->_loadGroups();
        }
        return $this->_defaultGroup;
    }

    /**
     * Load store collection and set internal data
     *
     * @return void
     */
    protected function _loadStores()
    {
        $this->_stores = [];
        $this->_storesCount = 0;
        foreach ($this->getStoreCollection() as $store) {
            $this->_stores[$store->getId()] = $store;
            $this->_storeIds[$store->getId()] = $store->getId();
            $this->_storeCodes[$store->getId()] = $store->getCode();
            if ($this->getDefaultGroup() && $this->getDefaultGroup()->getDefaultStoreId() == $store->getId()) {
                $this->_defaultStore = $store;
            }
            $this->_storesCount++;
        }
    }

    /**
     * Set website stores
     *
     * @param array $stores
     * @return void
     */
    public function setStores($stores)
    {
        $this->_stores = [];
        $this->_storesCount = 0;
        foreach ($stores as $store) {
            $this->_stores[$store->getId()] = $store;
            $this->_storeIds[$store->getId()] = $store->getId();
            $this->_storeCodes[$store->getId()] = $store->getCode();
            if ($this->getDefaultGroup() && $this->getDefaultGroup()->getDefaultStoreId() == $store->getId()) {
                $this->_defaultStore = $store;
            }
            $this->_storesCount++;
        }
    }

    /**
     * Retrieve new (not loaded) Store collection object with website filter
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection()
    {
        return $this->storeListFactory->create()->addWebsiteFilter($this->getId())->setLoadDefault(true);
    }

    /**
     * Retrieve website store objects
     *
     * @return array
     */
    public function getStores()
    {
        if ($this->_stores === null) {
            $this->_loadStores();
        }
        return $this->_stores;
    }

    /**
     * Retrieve website store ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        if ($this->_stores === null) {
            $this->_loadStores();
        }
        return $this->_storeIds;
    }

    /**
     * Retrieve website store codes
     *
     * @return array
     */
    public function getStoreCodes()
    {
        if ($this->_stores === null) {
            $this->_loadStores();
        }
        return $this->_storeCodes;
    }

    /**
     * Retrieve number stores in a website
     *
     * @return int
     */
    public function getStoresCount()
    {
        if ($this->_stores === null) {
            $this->_loadStores();
        }
        return $this->_storesCount;
    }

    /**
     * Can delete website
     *
     * @return bool
     */
    public function isCanDelete()
    {
        if ($this->_isReadOnly || !$this->getId()) {
            return false;
        }
        if ($this->_isCanDelete === null) {
            $this->_isCanDelete = $this->_websiteFactory->create()->getCollection()->getSize() > 1 &&
                !$this->getIsDefault();
        }
        return $this->_isCanDelete;
    }

    /**
     * Retrieve unique website-group-store key for collection with groups and stores
     *
     * @return string
     */
    public function getWebsiteGroupStore()
    {
        return implode('-', [$this->getWebsiteId(), $this->getGroupId(), $this->getStoreId()]);
    }

    /**
     * Get default group id
     *
     * @return mixed
     */
    public function getDefaultGroupId()
    {
        return $this->_getData('default_group_id');
    }

    /**
     * @inheritdoc
     */
    public function setDefaultGroupId($defaultGroupId)
    {
        return $this->setData('default_group_id', $defaultGroupId);
    }

    /**
     * Get code
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->_getData('code');
    }

    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->_configDataResource->clearScopeData(
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $this->getId()
        );
        $this->_configDataResource->clearScopeData(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $this->getStoreIds()
        );
        return parent::beforeDelete();
    }

    /**
     * Rewrite in order to clear configuration cache
     *
     * @return $this
     */
    public function afterDelete()
    {
        $this->_storeManager->reinitStores();
        $types = [
            'full_page',
            Config::TYPE_IDENTIFIER
        ];
        foreach ($types as $type) {
            $this->typeList->cleanType($type);
        }
        parent::afterDelete();
        return $this;
    }

    /**
     * Clear configuration cache after creation website
     *
     * @return $this
     * @since 100.2.0
     */
    public function afterSave()
    {
        if ($this->isObjectNew()) {
            $this->_storeManager->reinitStores();
        } else {
            $this->typeList->invalidate(['full_page', Config::TYPE_IDENTIFIER]);
        }
        $this->pillPut->put();
        return parent::afterSave();
    }

    /**
     * Retrieve website base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        if ($this->getConfig(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE
        ) == \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL
        ) {
            $currencyCode = $this->_coreConfig->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );
        } else {
            $currencyCode = $this->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE);
        }

        return $currencyCode;
    }

    /**
     * Retrieve website base currency
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if ($currency === null) {
            $currency = $this->_currencyFactory->create()->load($this->getBaseCurrencyCode());
            $this->setData('base_currency', $currency);
        }
        return $currency;
    }

    /**
     * Retrieve Default Website Store or null
     *
     * @return Store
     */
    public function getDefaultStore()
    {
        // init stores if not loaded
        $this->getStores();
        return $this->_defaultStore;
    }

    /**
     * Retrieve default stores select object, select fields website_id, store_id
     *
     * @param bool $withDefault include/exclude default admin website
     * @return \Magento\Framework\DB\Select
     */
    public function getDefaultStoresSelect($withDefault = false)
    {
        return $this->getResource()->getDefaultStoresSelect($withDefault);
    }

    /**
     * Get/Set isReadOnly flag
     *
     * @param bool $value
     * @return bool
     */
    public function isReadOnly($value = null)
    {
        if (null !== $value) {
            $this->_isReadOnly = (bool)$value;
        }
        return $this->_isReadOnly;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * @inheritDoc
     */
    public function getCacheTags()
    {
        $identities = $this->getIdentities();
        $parentTags = parent::getCacheTags();

        return array_unique(array_merge($identities, $parentTags));
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getScopeType()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getScopeTypeName()
    {
        return 'Website';
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\Store\Api\Data\WebsiteExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
