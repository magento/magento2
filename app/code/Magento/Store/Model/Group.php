<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store group model
 */
namespace Magento\Store\Model;

use Magento\Config\Model\ResourceModel\Config\Data as ConfigData;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\GroupExtensionInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Model\ResourceModel\Store\Collection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\ResourceModel\Group as ResourceModelGroup;
use Magento\Store\Model\Validation\StoreValidator;

/**
 * Class Group
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Group extends AbstractExtensibleModel implements
    IdentityInterface,
    GroupInterface,
    AppScopeInterface
{
    const ENTITY = 'store_group';

    const CACHE_TAG = 'store_group';

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
     * @var Collection[]
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
     * @var Store
     */
    protected $_defaultStore;

    /**
     * @var bool
     */
    private $_isReadOnly = false;

    /**
     * @var ConfigData
     */
    protected $_configDataResource;

    /**
     * @var Store
     */
    protected $_storeListFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ManagerInterface
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
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ConfigData $configDataResource
     * @param CollectionFactory $storeListFactory
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ManagerInterface|null $eventManager
     * @param PoisonPillPutInterface|null $pillPut
     * @param StoreValidator|null $modelValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ConfigData $configDataResource,
        CollectionFactory $storeListFactory,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ManagerInterface $eventManager = null,
        PoisonPillPutInterface $pillPut = null,
        StoreValidator $modelValidator = null
    ) {
        $this->_configDataResource = $configDataResource;
        $this->_storeListFactory = $storeListFactory;
        $this->_storeManager = $storeManager;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->pillPut = $pillPut ?: ObjectManager::getInstance()
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
        $this->_init(ResourceModelGroup::class);
    }

    /**
     * Validation rules for store
     *
     * @return \Zend_Validate_Interface|null
     * @throws \Zend_Validate_Exception
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
    protected function _loadStores(): void
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
     * @param Store[] $stores
     * @return void
     */
    public function setStores($stores): void
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
     * @return Collection
     */
    public function getStoreCollection(): Collection
    {
        return $this->_storeListFactory->create()->addGroupFilter($this->getId());
    }

    /**
     * Retrieve website store objects
     *
     * @return Collection[]
     */
    public function getStores(): array
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
    public function getStoreIds(): array
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
    public function getStoreCodes(): array
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
    public function getStoresCount(): int
    {
        if ($this->_stores === null) {
            $this->_loadStores();
        }
        return $this->_storesCount;
    }

    /**
     * Retrieve default store model
     *
     * @return Store|bool
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
     * @return Store|null
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
     * @return Store[]
     */
    public function getStoresByLocale($locale): array
    {
        $stores = [];
        foreach ($this->getStores() as $store) {
            /* @var Store $store */
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
    public function setWebsite(Website $website): void
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
    public function isCanDelete(): bool
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
            ScopeInterface::SCOPE_STORES,
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
    public function isReadOnly($value = null): bool
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
    public function getIdentities(): array
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
        GroupExtensionInterface $extensionAttributes
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
