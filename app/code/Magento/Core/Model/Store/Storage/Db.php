<?php
/**
 * Store loader
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Store\Storage;

class Db implements \Magento\Core\Model\Store\StorageInterface
{
    /**
     * Requested scope code
     *
     * @var string
     */
    protected $_scopeCode;

    /**
     * Requested scope type
     *
     * @var string
     */
    protected $_scopeType;

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
     * @var \Magento\Core\Model\Store
     */
    protected $_store;

    /**
     * Stores cache
     *
     * @var \Magento\Core\Model\Store[]
     */
    protected $_stores = array();

    /**
     * Application website object
     *
     * @var \Magento\Core\Model\Website
     */
    protected $_website;

    /**
     * Websites cache
     *
     * @var \Magento\Core\Model\Website[]
     */
    protected $_websites = array();

    /**
     * Groups cache
     *
     * @var \Magento\Core\Model\Store\Group[]
     */
    protected $_groups = array();

    /**
     * Config model
     *
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * Default store code
     *
     * @var string
     */
    protected $_currentStore = null;

    /**
     * Store factory
     *
     * @var \Magento\Core\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * Website factory
     *
     * @var \Magento\Core\Model\Website\Factory
     */
    protected $_websiteFactory;

    /**
     * Group factory
     *
     * @var \Magento\Core\Model\Store\Group\Factory
     */
    protected $_groupFactory;

    /**
     * Cookie model
     *
     * @var \Magento\Core\Model\Cookie
     */
    protected $_cookie;

    /**
     * Application state model
     *
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Backend\Model\Url\Proxy
     */
    protected $_proxy;

    /**
     * @param \Magento\Core\Model\StoreFactory $storeFactory
     * @param \Magento\Core\Model\Website\Factory $websiteFactory
     * @param \Magento\Core\Model\Store\Group\Factory $groupFactory
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Cookie $cookie
     * @param \Magento\App\State $appState
     * @param \Magento\Backend\Model\Url\Proxy $proxy
     * @param bool $isSingleStoreAllowed
     * @param string $scopeCode
     * @param string $scopeType
     * @param string $currentStore
     */
    public function __construct(
        \Magento\Core\Model\StoreFactory $storeFactory,
        \Magento\Core\Model\Website\Factory $websiteFactory,
        \Magento\Core\Model\Store\Group\Factory $groupFactory,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Cookie $cookie,
        \Magento\App\State $appState,
        \Magento\Backend\Model\Url\Proxy $proxy,
        $isSingleStoreAllowed,
        $scopeCode,
        $scopeType,
        $currentStore = null
    ) {
        $this->_storeFactory = $storeFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_groupFactory = $groupFactory;
        $this->_scopeCode = $scopeCode;
        $this->_scopeType = $scopeType ?: \Magento\Core\Model\StoreManagerInterface::SCOPE_TYPE_STORE;
        $this->_config = $config;
        $this->_isSingleStoreAllowed = $isSingleStoreAllowed;
        $this->_appState = $appState;
        $this->_cookie = $cookie;
        $this->_proxy = $proxy;
        if ($currentStore) {
            $this->_currentStore = $currentStore;
        }
    }

    /**
     * Get default store
     *
     * @return \Magento\Core\Model\Store
     */
    protected function _getDefaultStore()
    {
        if (empty($this->_store)) {
            $this->_store = $this->_storeFactory->create()
                ->setId(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
                ->setCode(\Magento\Core\Model\AppInterface::DISTRO_STORE_CODE);
        }
        return $this->_store;
    }

    /**
     * Initialize currently ran store
     *
     * @throws \Magento\Core\Model\Store\Exception
     */
    public function initCurrentStore()
    {
        \Magento\Profiler::start('init_stores');
        $this->_initStores();
        \Magento\Profiler::stop('init_stores');

        if (empty($this->_scopeCode) && false == is_null($this->_website)) {
            $this->_scopeCode = $this->_website->getCode();
            $this->_scopeType = \Magento\Core\Model\StoreManagerInterface::SCOPE_TYPE_WEBSITE;
        }
        switch ($this->_scopeType) {
            case \Magento\Core\Model\StoreManagerInterface::SCOPE_TYPE_STORE:
                $this->_currentStore = $this->_scopeCode;
                break;
            case \Magento\Core\Model\StoreManagerInterface::SCOPE_TYPE_GROUP:
                $this->_currentStore = $this->_getStoreByGroup($this->_scopeCode);
                break;
            case \Magento\Core\Model\StoreManagerInterface::SCOPE_TYPE_WEBSITE:
                $this->_currentStore = $this->_getStoreByWebsite($this->_scopeCode);
                break;
            default:
                $this->throwStoreException();
        }

        if (!empty($this->_currentStore)) {
            $this->_checkCookieStore($this->_scopeType);
            $this->_checkGetStore($this->_scopeType);
        }
    }

    /**
     * Check get store
     *
     * @param string $type
     */
    protected function _checkGetStore($type)
    {
        if (empty($_GET)) {
            return;
        }

        if (!isset($_GET['___store'])) {
            return;
        }

        $store = $_GET['___store'];
        if (!isset($this->_stores[$store])) {
            return;
        }

        $storeObj = $this->_stores[$store];
        if (!$storeObj->getId() || !$storeObj->getIsActive()) {
            return;
        }

        /**
         * prevent running a store from another website or store group,
         * if website or store group was specified explicitly
         */
        $curStoreObj = $this->_stores[$this->_currentStore];
        if ($type == 'website' && $storeObj->getWebsiteId() == $curStoreObj->getWebsiteId()) {
            $this->_currentStore = $store;
        } elseif ($type == 'group' && $storeObj->getGroupId() == $curStoreObj->getGroupId()) {
            $this->_currentStore = $store;
        } elseif ($type == 'store') {
            $this->_currentStore = $store;
        }

        if ($this->_currentStore == $store) {
            $store = $this->getStore($store);
            if ($store->getWebsite()->getDefaultStore()->getId() == $store->getId()) {
                $this->_cookie->delete(\Magento\Core\Model\Store::COOKIE_NAME);
            } else {
                $this->_cookie->set(\Magento\Core\Model\Store::COOKIE_NAME, $this->_currentStore, true);
            }
        }
        return;
    }

    /**
     * Check cookie store
     *
     * @param string $type
     */
    protected function _checkCookieStore($type)
    {
        if (!$this->_cookie->get()) {
            return;
        }

        $store = $this->_cookie->get(\Magento\Core\Model\Store::COOKIE_NAME);
        if ($store && isset($this->_stores[$store])
            && $this->_stores[$store]->getId()
            && $this->_stores[$store]->getIsActive()
        ) {
            if ($type == 'website'
                && $this->_stores[$store]->getWebsiteId() == $this->_stores[$this->_currentStore]->getWebsiteId()
            ) {
                $this->_currentStore = $store;
            }
            if ($type == 'group'
                && $this->_stores[$store]->getGroupId() == $this->_stores[$this->_currentStore]->getGroupId()
            ) {
                $this->_currentStore = $store;
            }
            if ($type == 'store') {
                $this->_currentStore = $store;
            }
        }
    }

    /**
     * Retrieve store code or null by store group
     *
     * @param int $group
     * @return string|null
     */
    protected function _getStoreByGroup($group)
    {
        if (!isset($this->_groups[$group])) {
            return null;
        }
        if (!$this->_groups[$group]->getDefaultStoreId()) {
            return null;
        }
        return $this->_stores[$this->_groups[$group]->getDefaultStoreId()]->getCode();
    }

    /**
     * Retrieve store code or null by website
     *
     * @param int|string $website
     * @return string|null
     */
    protected function _getStoreByWebsite($website)
    {
        if (!isset($this->_websites[$website])) {
            return null;
        }
        if (!$this->_websites[$website]->getDefaultGroupId()) {
            return null;
        }
        return $this->_getStoreByGroup($this->_websites[$website]->getDefaultGroupId());
    }

    /**
     * Init store, group and website collections
     */
    protected function _initStores()
    {
        $this->_store    = null;
        $this->_stores   = array();
        $this->_groups   = array();
        $this->_websites = array();

        $this->_website  = null;

        /** @var $websiteCollection \Magento\Core\Model\Resource\Website\Collection */
        $websiteCollection = $this->_websiteFactory->create()->getCollection();
        $websiteCollection->setLoadDefault(true);

        /** @var $groupCollection \Magento\Core\Model\Resource\Store\Group\Collection */
        $groupCollection = $this->_groupFactory->create()->getCollection();
        $groupCollection->setLoadDefault(true);

        /** @var $storeCollection \Magento\Core\Model\Resource\Store\Collection */
        $storeCollection = $this->_storeFactory->create()->getCollection();
        $storeCollection->setLoadDefault(true);

        $this->_hasSingleStore = false;
        if ($this->_isSingleStoreAllowed) {
            $this->_hasSingleStore = $storeCollection->count() < 3;
        }

        $websiteStores = array();
        $websiteGroups = array();
        $groupStores   = array();

        foreach ($storeCollection as $store) {
            /** @var $store \Magento\Core\Model\Store */
            $store->setWebsite($websiteCollection->getItemById($store->getWebsiteId()));
            $store->setGroup($groupCollection->getItemById($store->getGroupId()));

            $this->_stores[$store->getId()] = $store;
            $this->_stores[$store->getCode()] = $store;

            $websiteStores[$store->getWebsiteId()][$store->getId()] = $store;
            $groupStores[$store->getGroupId()][$store->getId()] = $store;

            if (is_null($this->_store) && $store->getId()) {
                $this->_store = $store;
            }

            if (0 == $store->getId()) {
                $store->setUrlModel($this->_proxy);
            }
        }

        foreach ($groupCollection as $group) {
            /* @var $group \Magento\Core\Model\Store\Group */
            if (!isset($groupStores[$group->getId()])) {
                $groupStores[$group->getId()] = array();
            }
            $group->setStores($groupStores[$group->getId()]);
            $group->setWebsite($websiteCollection->getItemById($group->getWebsiteId()));

            $websiteGroups[$group->getWebsiteId()][$group->getId()] = $group;

            $this->_groups[$group->getId()] = $group;
        }

        foreach ($websiteCollection as $website) {
            /* @var $website \Magento\Core\Model\Website */
            if (!isset($websiteGroups[$website->getId()])) {
                $websiteGroups[$website->getId()] = array();
            }
            if (!isset($websiteStores[$website->getId()])) {
                $websiteStores[$website->getId()] = array();
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
     * Retrieve application store object
     *
     * @param null|string|bool|int|\Magento\Core\Model\Store $storeId
     * @return \Magento\Core\Model\Store
     * @throws \Magento\Core\Model\Store\Exception
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
        if ($storeId instanceof \Magento\Core\Model\Store) {
            return $storeId;
        }
        if (!isset($storeId)) {
            $this->throwStoreException();
        }

        if (empty($this->_stores[$storeId])) {
            $store = $this->_storeFactory->create();
            if (is_numeric($storeId)) {
                $store->load($storeId);
            } elseif (is_string($storeId)) {
                $store->load($storeId, 'code');
            }

            if (!$store->getCode()) {
                $this->throwStoreException();
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
     * @return \Magento\Core\Model\Store[]
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        $stores = array();
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
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     * @return \Magento\Core\Model\Website
     * @throws \Magento\Core\Exception
     */
    public function getWebsite($websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->getStore()->getWebsiteId();
        } elseif ($websiteId instanceof \Magento\Core\Model\Website) {
            return $websiteId;
        } elseif ($websiteId === true) {
            return $this->_website;
        }

        if (empty($this->_websites[$websiteId])) {
            $website = $this->_websiteFactory->create();
            // load method will load website by code if given ID is not a numeric value
            $website->load($websiteId);
            if (!$website->hasWebsiteId()) {
                throw new \Magento\Core\Exception('Invalid website id/code requested.');
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
     * @return \Magento\Core\Model\Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = array();
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
     * @param null|\Magento\Core\Model\Store\Group|string $groupId
     * @return \Magento\Core\Model\Store\Group
     * @throws \Magento\Core\Exception
     */
    public function getGroup($groupId = null)
    {
        if (is_null($groupId)) {
            $groupId = $this->getStore()->getGroupId();
        } elseif ($groupId instanceof \Magento\Core\Model\Store\Group) {
            return $groupId;
        }
        if (empty($this->_groups[$groupId])) {
            $group = $this->_groupFactory->create();
            if (is_numeric($groupId)) {
                $group->load($groupId);
                if (!$group->hasGroupId()) {
                    throw new \Magento\Core\Exception('Invalid store group id requested.');
                }
            }
            $this->_groups[$group->getGroupId()] = $group;
        }
        return $this->_groups[$groupId];
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Core\Model\Store\Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        $groups = array();
        if (is_array($this->_groups)) {
            foreach ($this->_groups as $group) {
                /** @var $group \Magento\Core\Model\Store\Group */
                if (!$withDefault && $group->getId() == 0) {
                    continue;
                }
                if ($codeKey) {
                    $groups[$group->getCode()] = $group;
                } else {
                    $groups[$group->getId()] = $group;
                }
            }
        }
        return $groups;
    }

    /**
     * Reinitialize store list
     */
    public function reinitStores()
    {
        $this->_initStores();
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return \Magento\Core\Model\Store
     */
    public function getDefaultStoreView()
    {
        foreach ($this->getWebsites() as $_website) {
            /** @var $_website \Magento\Core\Model\Website */
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
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     */
    public function clearWebsiteCache($websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->getStore()->getWebsiteId();
        } elseif ($websiteId instanceof \Magento\Core\Model\Website) {
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
     * Get either default or any store view
     *
     * @return \Magento\Core\Model\Store|null
     */
    public function getAnyStoreView()
    {
        $store = $this->getDefaultStoreView();
        if ($store) {
            return $store;
        }
        foreach ($this->getStores() as $store) {
            return $store;
        }

        return null;
    }

    /**
     * Set current default store
     *
     * @param string $store
     */
    public function setCurrentStore($store)
    {
        $this->_currentStore = $store;
    }

    /**
     * @throws \Magento\Core\Model\Store\Exception
     */
    public function throwStoreException()
    {
        throw new \Magento\Core\Model\Store\Exception('');
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getCurrentStore()
    {
        return $this->_currentStore;
    }
}
