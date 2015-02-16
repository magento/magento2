<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store group model
 *
 * @method \Magento\Store\Model\Resource\Group _getResource()
 * @method \Magento\Store\Model\Resource\Group getResource()
 * @method \Magento\Store\Model\Store setWebsiteId(int $value)
 * @method string getName()
 * @method \Magento\Store\Model\Store setName(string $value)
 * @method \Magento\Store\Model\Store setRootCategoryId(int $value)
 * @method \Magento\Store\Model\Store setDefaultStoreId(int $value)
 */
namespace Magento\Store\Model;


class Group extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Object\IdentityInterface
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
     * @var \Magento\Store\Model\Resource\Store\Collection[]
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
     * @var \Magento\Core\Model\Resource\Config\Data
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
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Core\Model\Resource\Config\Data $configDataResource
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Core\Model\Resource\Config\Data $configDataResource,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeListFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_configDataResource = $configDataResource;
        $this->_storeListFactory = $storeListFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Store\Model\Resource\Group');
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
     * @return \Magento\Store\Model\Resource\Store\Collection
     */
    public function getStoreCollection()
    {
        return $this->_storeListFactory->create()->addGroupFilter($this->getId());
    }

    /**
     * Retrieve website store objects
     *
     * @return \Magento\Store\Model\Resource\Store\Collection[]
     */
    public function getStores()
    {
        if (is_null($this->_stores)) {
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
        if (is_null($this->_stores)) {
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
        if (is_null($this->_stores)) {
            $this->_loadStores();
        }
        return $this->_storeCodes;
    }

    /**
     * @return int
     */
    public function getStoresCount()
    {
        if (is_null($this->_stores)) {
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
        if (is_null($this->_stores)) {
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
        if (is_null($this->getWebsiteId())) {
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

        return $this->getWebsite()->getDefaultGroupId() != $this->getId();
    }

    /**
     * @return mixed
     */
    public function getDefaultStoreId()
    {
        return $this->_getData('default_store_id');
    }

    /**
     * @return mixed
     */
    public function getRootCategoryId()
    {
        return $this->_getData('root_category_id');
    }

    /**
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->_getData('website_id');
    }

    /**
     * @return $this
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
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
