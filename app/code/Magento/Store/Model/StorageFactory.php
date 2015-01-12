<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\Profiler;

class StorageFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Storage class name
     *
     * @var string
     */
    protected $_storageClassName;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface[]
     */
    protected $_cache = [];

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var string
     */
    protected $_writerModel;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $storageClassName
     * @param string $writerModel
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        $storageClassName = 'Magento\Store\Model\Storage\Db',
        $writerModel = ''
    ) {
        $this->_objectManager = $objectManager;
        $this->_storageClassName = $storageClassName;
        $this->_eventManager = $eventManager;
        $this->_appState = $appState;
        $this->_sidResolver = $sidResolver;
        $this->_writerModel = $writerModel;
        $this->_httpContext = $httpContext;
        $this->_scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Get storage instance
     *
     * @param array $arguments
     * @return \Magento\Store\Model\StoreManagerInterface
     * @throws \InvalidArgumentException
     */
    public function get(array $arguments = [])
    {
        $className = $this->_storageClassName;

        if (false == isset($this->_cache[$className])) {
            /** @var $storage \Magento\Store\Model\StoreManagerInterface */
            $storage = $this->_objectManager->create($className, $arguments);

            if (false === ($storage instanceof \Magento\Store\Model\StoreManagerInterface)) {
                throw new \InvalidArgumentException(
                    $className . ' doesn\'t implement \Magento\Store\Model\StoreManagerInterface'
                );
            }
            $this->_cache[$className] = $storage;
            if ($className === $this->_storageClassName) {
                $this->_reinitStores($storage, $arguments);
                $useSid = $this->_scopeConfig->isSetFlag(
                    \Magento\Framework\Session\SidResolver::XML_PATH_USE_FRONTEND_SID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storage->getStore()
                );
                $this->_sidResolver->setUseSessionInUrl($useSid);

                $this->_eventManager->dispatch('core_app_init_current_store_after');
            }
        }
        return $this->_cache[$className];
    }

    /**
     * Initialize currently ran store
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storage
     * @param array $arguments
     * @return void
     * @throws \Magento\Framework\App\InitException
     */
    protected function _reinitStores(\Magento\Store\Model\StoreManagerInterface $storage, $arguments)
    {
        Profiler::start('init_stores');
        $storage->reinitStores();
        Profiler::stop('init_stores');

        $scopeCode = $arguments['scopeCode'];
        $scopeType = $arguments['scopeType'] ?: ScopeInterface::SCOPE_STORE;
        if (empty($scopeCode) && false == is_null($storage->getWebsite(true))) {
            $scopeCode = $storage->getWebsite(true)->getCode();
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
        }
        switch ($scopeType) {
            case ScopeInterface::SCOPE_STORE:
                $storage->setCurrentStore($scopeCode);
                break;
            case ScopeInterface::SCOPE_GROUP:
                $storage->setCurrentStore($this->_getStoreByGroup($storage, $scopeCode));
                break;
            case ScopeInterface::SCOPE_WEBSITE:
                $storage->setCurrentStore($this->_getStoreByWebsite($storage, $scopeCode));
                break;
            default:
                throw new \Magento\Framework\App\InitException(
                    'Store Manager has not been initialized properly'
                );
        }

        $currentStore = $storage->getStore()->getCode();
        if (!empty($currentStore)) {
            $this->_checkCookieStore($storage, $scopeType);
            $this->_checkRequestStore($storage, $scopeType);
        }
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storage
     * @param string $scopeCode
     * @return null|string
     */
    protected function _getStoreByGroup(\Magento\Store\Model\StoreManagerInterface $storage, $scopeCode)
    {
        $groups = $storage->getGroups(true);
        $stores = $storage->getStores(true);
        if (!isset($groups[$scopeCode])) {
            return null;
        }
        if (!$groups[$scopeCode]->getDefaultStoreId() || !isset($stores[$groups[$scopeCode]->getDefaultStoreId()])) {
            return null;
        }
        return $stores[$groups[$scopeCode]->getDefaultStoreId()]->getCode();
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storage
     * @param string $scopeCode
     * @return null|string
     */
    protected function _getStoreByWebsite(\Magento\Store\Model\StoreManagerInterface $storage, $scopeCode)
    {
        $websites = $storage->getWebsites(true, true);
        if (!isset($websites[$scopeCode])) {
            return null;
        }
        if (!$websites[$scopeCode]->getDefaultGroupId()) {
            return null;
        }
        return $this->_getStoreByGroup($storage, $websites[$scopeCode]->getDefaultGroupId());
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storage
     * @param string $scopeType
     * @return void
     */
    protected function _checkCookieStore(\Magento\Store\Model\StoreManagerInterface $storage, $scopeType)
    {
        $storeCode = $storage->getStore()->getStoreCodeFromCookie();
        if (null != $storeCode) {
            $this->setCurrentStore($storage, $storeCode, $scopeType);
        }
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storage
     * @param string $scopeType
     * @return void
     */
    protected function _checkRequestStore(\Magento\Store\Model\StoreManagerInterface $storage, $scopeType)
    {
        $storeCode = $this->request->getParam('___store');
        if (empty($storeCode)) {
            return;
        }

        if (!$this->setCurrentStore($storage, $storeCode, $scopeType)) {
            return;
        }

        $storageStore = $storage->getStore();
        if ($storageStore->getCode() == $storeCode) {
            $store = $storage->getStore($storeCode);
            if ($store->getWebsite()->getDefaultStore()->getId() == $store->getId()) {
                $store->deleteCookie();
            } else {
                $storageStore->setCookie();
                $this->_httpContext->setValue(
                    Store::ENTITY,
                    $storageStore->getCode(),
                    \Magento\Store\Model\Store::DEFAULT_CODE
                );
            }
        }
        return;
    }

    /**
     * Get active store by code
     *
     * @param StoreManagerInterface $storage
     * @param string $scopeCode
     * @return bool|Store
     */
    protected function getActiveStoreByCode(\Magento\Store\Model\StoreManagerInterface $storage, $scopeCode)
    {
        $stores = $storage->getStores(true, true);
        if ($scopeCode && isset($stores[$scopeCode])
            && $stores[$scopeCode]->getId()
            && $stores[$scopeCode]->getIsActive()
        ) {
            return $stores[$scopeCode];
        }
        return false;
    }

    /**
     * Set current store
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storage
     * @param string $scopeCode
     * @param string $scopeType
     * @return bool
     */
    protected function setCurrentStore(\Magento\Store\Model\StoreManagerInterface $storage, $scopeCode, $scopeType)
    {
        $store = $this->getActiveStoreByCode($storage, $scopeCode);
        if (!$store) {
            return false;
        }
        $stores = $storage->getStores(true, true);
        $curStoreObj = $stores[$storage->getStore()->getCode()];
        /**
         * Prevent running a store from another website or store group,
         * if website or store group was specified explicitly
         */
        $setStore = false;
        switch ($scopeType) {
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE:
                $setStore = $store->getWebsiteId() == $curStoreObj->getWebsiteId();
                break;
            case \Magento\Store\Model\ScopeInterface::SCOPE_GROUP:
                $setStore = $store->getGroupId() == $curStoreObj->getGroupId();
                break;
            case \Magento\Store\Model\ScopeInterface::SCOPE_STORE:
                $setStore = true;
                break;
        }
        if ($setStore) {
            $storage->setCurrentStore($scopeCode);
        }
        return true;
    }
}
