<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

class StoreManager implements \Magento\Store\Model\StoreManagerInterface
{
    /**
     * Application run code
     */
    const PARAM_RUN_CODE = 'MAGE_RUN_CODE';

    /**
     * Application run type (store|website)
     */
    const PARAM_RUN_TYPE = 'MAGE_RUN_TYPE';

    /**
     * Wether single store mode enabled or not
     */
    const XML_PATH_SINGLE_STORE_MODE_ENABLED = 'general/single_store_mode/enabled';

    /**
     * Store storage factory model
     *
     * @var \Magento\Store\Model\StorageFactory
     */
    protected $_factory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Request model
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Default store code
     *
     * @var string
     */
    protected $_currentStore = null;

    /**
     * Flag is single store mode allowed
     *
     * @var bool
     */
    protected $_isSingleStoreAllowed = true;

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
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storage;

    /**
     * @param \Magento\Store\Model\StorageFactory $factory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $scopeCode
     * @param string $scopeType
     */
    public function __construct(
        \Magento\Store\Model\StorageFactory $factory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $scopeCode = '',
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        $this->_factory = $factory;
        $this->_request = $request;
        $this->_scopeCode = $scopeCode;
        $this->_scopeType = $scopeType;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Get storage instance
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function _getStorage()
    {
        if (!$this->_storage instanceof \Magento\Store\Model\StoreManagerInterface) {
            $arguments = [
                'isSingleStoreAllowed' => $this->_isSingleStoreAllowed,
                'currentStore' => $this->_currentStore,
                'scopeCode' => $this->_scopeCode,
                'scopeType' => $this->_scopeType
            ];
            $this->_storage = $this->_factory->get($arguments);
        }
        return $this->_storage;
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
        $this->_getStorage()->setCurrentStore($store);
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->_isSingleStoreAllowed = $value;
        $this->_getStorage()->setIsSingleStoreModeAllowed($value);
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore()
    {
        return $this->_getStorage()->hasSingleStore();
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
     * {@inheritdoc}
     */
    public function getStore($storeId = null)
    {
        return $this->_getStorage()->getStore($storeId);
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
        return $this->_getStorage()->getStores($withDefault, $codeKey);
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|Website $websiteId
     * @return Website
     * @throws \Magento\Framework\Model\Exception
     */
    public function getWebsite($websiteId = null)
    {
        return $this->_getStorage()->getWebsite($websiteId);
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
        return $this->_getStorage()->getWebsites($withDefault, $codeKey);
    }

    /**
     * Reinitialize store list
     *
     * @return void
     */
    public function reinitStores()
    {
        $this->_getStorage()->reinitStores();
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return Store|null
     */
    public function getDefaultStoreView()
    {
        return $this->_getStorage()->getDefaultStoreView();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup($groupId = null)
    {
        return $this->_getStorage()->getGroup($groupId);
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Store\Model\Group[]
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        return $this->_getStorage()->getGroups($withDefault, $codeKey);
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|Website $websiteId
     * @return void
     */
    public function clearWebsiteCache($websiteId = null)
    {
        $this->_getStorage()->clearWebsiteCache($websiteId);
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
            self::XML_PATH_SINGLE_STORE_MODE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
