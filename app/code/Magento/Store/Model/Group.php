<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store group model
 */
namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Store\Model\Validation\StoreValidator;

/**
 * Store Group model class used to retrieve and format group information
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Group extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Magento\Store\Api\Data\GroupInterface,
    \Magento\Framework\App\ScopeInterface
{
    public const ENTITY = 'store_group';

    public const CACHE_TAG = 'store_group';

    /**
     * @var bool
     */
    protected $_cacheTag = true;

    /**
     * @var string
     */
    protected $_eventPrefix = 'store_group';

    /**
     * @var string
     */
    protected $_eventObject = 'store_group';

    /**
     * Group Store collection array
     *
     * @var \Magento\Store\Model\ResourceModel\Store\Collection[]
     */
    protected $_stores;

    /**
     * Group store ids array
     *
     * @var int[]
     */
    protected $_storeIds = [];

    /**
     * Group store codes array
     *
     * @var string[]
     */
    protected $_storeCodes = [];

    /**
     * The number of stores in a group
     *
     * @var int
     */
    protected $_storesCount = 0;

    /**
     * Group default store
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_defaultStore;

    /**
     * @var bool
     */
    private $_isReadOnly = false;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data
     */
    protected $_configDataResource;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_storeListFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var PoisonPillPutInterface
     */
    private $pillPut;

    /**
     * @var StoreValidator
     */
    private $modelValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Config\Model\ResourceModel\Config\Data $configDataResource
     * @param ResourceModel\Store\CollectionFactory $storeListFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Event\ManagerInterface|null $eventManager
     * @param PoisonPillPutInterface|null $pillPut
     * @param StoreValidator|null $modelValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeListFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Event\ManagerInterface $eventManager = null,
        PoisonPillPutInterface $pillPut = null,
        StoreValidator $modelValidator = null
    ) {
        $this->_configDataResource = $configDataResource;
        $this->_storeListFactory = $storeListFactory;
        $this->_storeManager = $storeManager;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->pillPut = $pillPut ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PoisonPillPutInterface::class);
        $this->modelValidator = $modelValidator ?: ObjectManager::getInstance()
            ->get(StoreValidator::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Store\Model\ResourceModel\Group::class);
    }

    /**
     * @inheritdoc
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->modelValidator;
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
            if ($this->getDefaultStoreId() == $store->getId()) {
                $this->_defaultStore = $store;
            }
            $this->_storesCount++;
        }
    }

    /**
     * Set website stores
     *
     * @param \Magento\Store\Model\Store[] $stores
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
            if ($this->getDefaultStoreId() == $store->getId()) {
                $this->_defaultStore = $store;
            }
            $this->_storesCount++;
        }
    }

    /**
     * Retrieve new (not loaded) Store collection object with group filter
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection()
    {
        return $this->_storeListFactory->create()->addGroupFilter($this->getId());
    }

    /**
     * Retrieve website store objects
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection[]
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
     * @return int[]
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
     * Get stores count
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
     * Retrieve default store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getDefaultStore()
    {
        if (!$this->hasDefaultStoreId()) {
            return false;
        }
        if ($this->_stores === null) {
            $this->_loadStores();
        }
        return $this->_defaultStore;
    }

    /**
     * Get most suitable store by locale
     * If no store with given locale is found - default store is returned
     * If group has no stores - null is returned
     *
     * @param string $locale
     * @return \Magento\Store\Model\Store|null
     */
    public function getDefaultStoreByLocale($locale)
    {
        if ($this->getDefaultStore() && $this->getDefaultStore()->getLocaleCode() == $locale) {
            return $this->getDefaultStore();
        } else {
            $stores = $this->getStoresByLocale($locale);
            if (count($stores)) {
                return $stores[0];
            } else {
                return $this->getDefaultStore() ? $this->getDefaultStore() : null;
            }
        }
    }

    /**
     * Retrieve list of stores with given locale
     *
     * @param string $locale
     * @return \Magento\Store\Model\Store[]
     */
    public function getStoresByLocale($locale)
    {
        $stores = [];
        foreach ($this->getStores() as $store) {
            /* @var $store \Magento\Store\Model\Store */
            if ($store->getLocaleCode() == $locale) {
                $stores[] = $store;
            }
        }
        return $stores;
    }

    /**
     * Set relation to the website
     *
     * @param Website $website
     * @return void
     */
    public function setWebsite(Website $website)
    {
        $this->setWebsiteId($website->getId());
    }

    /**
     * Retrieve website model
     *
     * @return Website|bool
     */
    public function getWebsite()
    {
        if ($this->getWebsiteId() === null) {
            return false;
        }
        return $this->_storeManager->getWebsite($this->getWebsiteId());
    }

    /**
     * Is can delete group
     *
     * @return bool
     */
    public function isCanDelete()
    {
        if (!$this->getId()) {
            return false;
        }

        return $this->getWebsite()->getGroupsCount() > 1;
    }

    /**
     * Get default store id
     *
     * @return mixed
     */
    public function getDefaultStoreId()
    {
        return $this->_getData('default_store_id');
    }

    /**
     * @inheritdoc
     */
    public function setDefaultStoreId($defaultStoreId)
    {
        return $this->setData('default_store_id', $defaultStoreId);
    }

    /**
     * Get root category id
     *
     * @return mixed
     */
    public function getRootCategoryId()
    {
        return $this->_getData('root_category_id');
    }

    /**
     * @inheritdoc
     */
    public function setRootCategoryId($rootCategoryId)
    {
        return $this->setData('root_category_id', $rootCategoryId);
    }

    /**
     * Get website id
     *
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->_getData('website_id');
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData('website_id', $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->_configDataResource->clearScopeData(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $this->getStoreIds()
        );
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function afterDelete()
    {
        $group = $this;
        $this->getResource()->addCommitCallback(function () use ($group) {
            $this->_storeManager->reinitStores();
            $this->eventManager->dispatch($this->_eventPrefix . '_delete', ['group' => $group]);
        });
        $result = parent::afterDelete();

        if ($this->getId() === $this->getWebsite()->getDefaultGroupId()) {
            $ids = $this->getWebsite()->getGroupIds();
            if (!empty($ids) && count($ids) > 1) {
                unset($ids[$this->getId()]);
                $defaultId = current($ids);
            } else {
                $defaultId = null;
            }
            $this->getWebsite()->setDefaultGroupId($defaultId);
            $this->getWebsite()->save();
        }
        return $result;
    }

    /**
     * @inheritdoc
     * @since 100.2.5
     */
    public function afterSave()
    {
        $group = $this;
        $this->getResource()->addCommitCallback(function () use ($group) {
            $this->_storeManager->reinitStores();
            $this->eventManager->dispatch($this->_eventPrefix . '_save', ['group' => $group]);
        });
        $this->pillPut->put();
        return parent::afterSave();
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
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData('name');
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
     * @since 100.1.0
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
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
        \Magento\Store\Api\Data\GroupExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getScopeType()
    {
        return ScopeInterface::SCOPE_GROUP;
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getScopeTypeName()
    {
        return 'Store';
    }
}
