<?php
/**
 * Store loader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Storage;

use Magento\Framework\App\State;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory as WebsiteFactory;

class Db implements \Magento\Store\Model\StoreManagerInterface
{
    /**
     * Flag that shows that system has only one store view
     *
     * @var bool
     */
    protected $_hasSingleStore;

    /**
     * Flag is single store mode allowed
     *
     * @var bool
     */
    protected $_isSingleStoreAllowed = true;

    /**
     * Application store object
     *
     * @var Store
     */
    protected $_store;

    /**
     * Stores cache
     *
     * @var Store[]
     */
    protected $_stores = [];

    /**
     * Application website object
     *
     * @var Website
     */
    protected $_website;

    /**
     * Websites cache
     *
     * @var Website[]
     */
    protected $_websites = [];

    /**
     * Groups cache
     *
     * @var Group[]
     */
    protected $_groups = [];

    /**
     * Default store code
     *
     * @var string
     */
    protected $_currentStore;

    /**
     * Store factory
     *
     * @var StoreFactory
     */
    protected $_storeFactory;

    /**
     * Website factory
     *
     * @var WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Group factory
     *
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * Application state model
     *
     * @var State
     */
    protected $_appState;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\Resource\Website\CollectionFactory
     */
    protected $_websiteCollectionFactory;

    /**
     * @var \Magento\Store\Model\Resource\Group\CollectionFactory
     */
    protected $_groupCollectionFactory;

    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @param StoreFactory $storeFactory
     * @param WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\Resource\Website\CollectionFactory $websiteCollectionFactory
     * @param \Magento\Store\Model\Resource\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
     * @param State $appState
     * @param bool $isSingleStoreAllowed
     * @param null $currentStore
     */
    public function __construct(
        StoreFactory $storeFactory,
        WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\Resource\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Store\Model\Resource\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory,
        State $appState,
        $isSingleStoreAllowed,
        $currentStore = null
    ) {
        $this->_storeFactory = $storeFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_groupFactory = $groupFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_isSingleStoreAllowed = $isSingleStoreAllowed;
        $this->_appState = $appState;
        $this->_currentStore = $currentStore;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * Get default store
     *
     * @return Store
     */
    protected function _getDefaultStore()
    {
        if (empty($this->_store)) {
            $this->_store = $this->_storeFactory->create()->setId(
                \Magento\Store\Model\Store::DISTRO_STORE_ID
            )->setCode(
                \Magento\Store\Model\Store::DEFAULT_CODE
            );
        }
        return $this->_store;
    }

    /**
     * Init store, group and website collections
     *
     * @return void
     */
    protected function _initStores()
    {
        $this->_store = null;
        $this->_stores = [];
        $this->_groups = [];
        $this->_websites = [];

        $this->_website = null;
        /** @var $websiteCollection \Magento\Store\Model\Resource\Website\Collection */
        $websiteCollection = $this->_websiteCollectionFactory->create();
        $websiteCollection->setLoadDefault(true);

        /** @var $groupCollection \Magento\Store\Model\Resource\Group\Collection */
        $groupCollection = $this->_groupCollectionFactory->create();
        $groupCollection->setLoadDefault(true);

        /** @var $storeCollection \Magento\Store\Model\Resource\Store\Collection */
        $storeCollection = $this->_storeCollectionFactory->create();
        $storeCollection->setLoadDefault(true);

        $this->_hasSingleStore = false;
        if ($this->_isSingleStoreAllowed && $storeCollection->count() < 3) {
            $this->_hasSingleStore = true;
            $this->_store = $storeCollection->getLastItem();
        }

        $websiteStores = [];
        $websiteGroups = [];
        $groupStores = [];
        foreach ($storeCollection as $store) {
            /** @var $store Store */
            $this->_stores[$store->getId()] = $store;
            $this->_stores[$store->getCode()] = $store;
            $websiteStores[$store->getWebsiteId()][$store->getId()] = $store;
            $groupStores[$store->getGroupId()][$store->getId()] = $store;
        }

        foreach ($groupCollection as $group) {
            /* @var $group Group */
            if (!isset($groupStores[$group->getId()])) {
                $groupStores[$group->getId()] = [];
            }
            $group->setStores($groupStores[$group->getId()]);
            $websiteGroups[$group->getWebsiteId()][$group->getId()] = $group;
            $this->_groups[$group->getId()] = $group;
        }

        foreach ($websiteCollection as $website) {
            /* @var $website Website */
            if (!isset($websiteGroups[$website->getId()])) {
                $websiteGroups[$website->getId()] = [];
            }
            if (!isset($websiteStores[$website->getId()])) {
                $websiteStores[$website->getId()] = [];
            }
            if ($website->getIsDefault()) {
                $this->_website = $website;
            }
            $website->setGroups($websiteGroups[$website->getId()]);
            $website->setStores($websiteStores[$website->getId()]);

            $this->_websites[$website->getId()] = $website;
            $this->_websites[$website->getCode()] = $website;
        }
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->_isSingleStoreAllowed = (bool)$value;
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore()
    {
        return $this->_hasSingleStore;
    }

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->hasSingleStore() && $this->isSingleStoreModeEnabled();
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|Store $storeId
     * @return Store
     * @throws \Magento\Framework\App\InitException
     */
    public function getStore($storeId = null)
    {
        if ($this->_appState->getUpdateMode()) {
            return $this->_getDefaultStore();
        }

        if ($storeId === true && $this->hasSingleStore()) {
            return $this->_store;
        }

        if (!isset($storeId) || '' === $storeId || $storeId === true) {
            $storeId = $this->_currentStore;
        }
        if ($storeId instanceof Store) {
            return $storeId;
        }

        if (empty($this->_stores[$storeId])) {
            $store = $this->_storeFactory->create();
            if (is_numeric($storeId)) {
                $store->load($storeId);
            } elseif (is_string($storeId)) {
                $store->load($storeId, 'code');
            }

            if (!$store->getCode()) {
                throw new \Magento\Framework\App\InitException(
                    'Store Manager has been initialized not properly'
                );
            }
            $this->_stores[$store->getStoreId()] = $store;
            $this->_stores[$store->getCode()] = $store;
        }
        return $this->_stores[$storeId];
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Store[]
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        $stores = [];
        foreach ($this->_stores as $store) {
            if (!$withDefault && $store->getId() == 0) {
                continue;
            }
            if ($codeKey) {
                $stores[$store->getCode()] = $store;
            } else {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|Website $websiteId
     * @return Website
     * @throws \Magento\Framework\App\InitException
     */
    public function getWebsite($websiteId = null)
    {
        if ($websiteId === null || $websiteId === '') {
            $websiteId = $this->getStore()->getWebsiteId();
        } elseif ($websiteId instanceof Website) {
            return $websiteId;
        } elseif ($websiteId === true) {
            return $this->_website;
        }

        if (empty($this->_websites[$websiteId])) {
            $website = $this->_websiteFactory->create();
            // load method will load website by code if given ID is not a numeric value
            $website->load($websiteId);
            if (!$website->hasWebsiteId()) {
                throw new \Magento\Framework\App\InitException('Invalid website id/code requested.');
            }
            $this->_websites[$website->getWebsiteId()] = $website;
            $this->_websites[$website->getCode()] = $website;
        }
        return $this->_websites[$websiteId];
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = [];
        if (is_array($this->_websites)) {
            foreach ($this->_websites as $website) {
                if (!$withDefault && $website->getId() == 0) {
                    continue;
                }
                if ($codeKey) {
                    $websites[$website->getCode()] = $website;
                } else {
                    $websites[$website->getId()] = $website;
                }
            }
        }
        return $websites;
    }

    /**
     * Retrieve application store group object
     *
     * @param null|Group|string $groupId
     * @return Group
     * @throws \Magento\Framework\App\InitException
     */
    public function getGroup($groupId = null)
    {
        if (is_null($groupId)) {
            $groupId = $this->getStore()->getGroupId();
        } elseif ($groupId instanceof Group) {
            return $groupId;
        }
        if (empty($this->_groups[$groupId])) {
            $group = $this->_groupFactory->create();
            if (is_numeric($groupId)) {
                $group->load($groupId);
                if (!$group->hasGroupId()) {
                    throw new \Magento\Framework\App\InitException('Invalid store group id requested.');
                }
            }
            $this->_groups[$group->getGroupId()] = $group;
        }
        return $this->_groups[$groupId];
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        $groups = [];
        if (is_array($this->_groups)) {
            foreach ($this->_groups as $group) {
                /** @var $group Group */
                if (!$withDefault && $group->getId() == 0) {
                    continue;
                }
                $groups[$group->getId()] = $group;
            }
        }
        return $groups;
    }

    /**
     * Reinitialize store list
     *
     * @return void
     */
    public function reinitStores()
    {
        $this->_initStores();
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return Store|null
     */
    public function getDefaultStoreView()
    {
        foreach ($this->getWebsites(true) as $_website) {
            /** @var $_website Website */
            if ($_website->getIsDefault()) {
                $_defaultStore = $this->getGroup($_website->getDefaultGroupId())->getDefaultStore();
                if ($_defaultStore) {
                    return $_defaultStore;
                }
            }
        }
        return null;
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|Website $websiteId
     * @return void
     */
    public function clearWebsiteCache($websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->getStore()->getWebsiteId();
        } elseif ($websiteId instanceof Website) {
            $websiteId = $websiteId->getId();
        } elseif ($websiteId === true) {
            $websiteId = $this->_website->getId();
        }

        if (!empty($this->_websites[$websiteId])) {
            $website = $this->_websites[$websiteId];

            unset($this->_websites[$website->getWebsiteId()]);
            unset($this->_websites[$website->getCode()]);
        }
    }

    /**
     * Set current default store
     *
     * @param string $store
     * @return void
     */
    public function setCurrentStore($store)
    {
        $this->_currentStore = $store;
    }

    /**
     * Check if Single-Store mode is enabled in configuration
     *
     * This flag only shows that admin does not want to show certain UI components at backend (like store switchers etc)
     * if Magento has only one store view but it does not check the store view collection
     *
     * @return bool
     */
    protected function isSingleStoreModeEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
