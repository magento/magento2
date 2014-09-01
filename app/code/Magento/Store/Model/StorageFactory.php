<?php
/**
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\Model;

use Magento\Framework\Profiler;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class StorageFactory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Default storage class name
     *
     * @var string
     */
    protected $_defaultStorageClassName;

    /**
     * Installed storage class name
     *
     * @var string
     */
    protected $_installedStorageClassName;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface[]
     */
    protected $_cache = array();

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_log;

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
     * @var \Magento\Framework\Stdlib\CookieManager
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Stdlib\CookieManager $cookieManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param string $defaultStorageClassName
     * @param string $installedStorageClassName
     * @param string $writerModel
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManager $cookieManager,
        $defaultStorageClassName = 'Magento\Store\Model\Storage\DefaultStorage',
        $installedStorageClassName = 'Magento\Store\Model\Storage\Db',
        $writerModel = ''
    ) {
        $this->_objectManager = $objectManager;
        $this->_defaultStorageClassName = $defaultStorageClassName;
        $this->_installedStorageClassName = $installedStorageClassName;
        $this->_eventManager = $eventManager;
        $this->_log = $logger;
        $this->_appState = $appState;
        $this->_sidResolver = $sidResolver;
        $this->_writerModel = $writerModel;
        $this->_httpContext = $httpContext;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager = $cookieManager;
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
    public function get(array $arguments = array())
    {
        $className =
            $this->_appState->isInstalled() ? $this->_installedStorageClassName : $this->_defaultStorageClassName;

        if (false == isset($this->_cache[$className])) {
            /** @var $storage \Magento\Store\Model\StoreManagerInterface */
            $storage = $this->_objectManager->create($className, $arguments);

            if (false === ($storage instanceof \Magento\Store\Model\StoreManagerInterface)) {
                throw new \InvalidArgumentException(
                    $className . ' doesn\'t implement \Magento\Store\Model\StoreManagerInterface'
                );
            }
            $this->_cache[$className] = $storage;
            if ($className === $this->_installedStorageClassName) {
                $this->_reinitStores($storage, $arguments);
                $useSid = $this->_scopeConfig->isSetFlag(
                    \Magento\Framework\Session\SidResolver::XML_PATH_USE_FRONTEND_SID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storage->getStore()
                );
                $this->_sidResolver->setUseSessionInUrl($useSid);

                $this->_eventManager->dispatch('core_app_init_current_store_after');

                $store = $storage->getStore(true);
                $logActive = $this->_scopeConfig->isSetFlag(
                    'dev/log/active',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                );
                if ($logActive || $this->_appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER) {
                    $logFile = $this->_scopeConfig->getValue(
                        'dev/log/file',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $logExceptionFile = $this->_scopeConfig->getValue(
                        'dev/log/exception_file',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $this->_log->unsetLoggers();
                    $this->_log->addStreamLog(
                        \Magento\Framework\Logger::LOGGER_SYSTEM,
                        $logFile,
                        $this->_writerModel
                    );
                    $this->_log->addStreamLog(
                        \Magento\Framework\Logger::LOGGER_EXCEPTION,
                        $logExceptionFile,
                        $this->_writerModel
                    );
                }
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
     * @throws \Magento\Store\Model\Exception
     */
    protected function _reinitStores(\Magento\Store\Model\StoreManagerInterface $storage, $arguments)
    {
        Profiler::start('init_stores');
        $storage->reinitStores();
        Profiler::stop('init_stores');

        $scopeCode = $arguments['scopeCode'];
        $scopeType = $arguments['scopeType'] ? : ScopeInterface::SCOPE_STORE;
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
                throw new \Magento\Store\Model\Exception(
                    'Store Manager has been initialized not properly'
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
        $storeCode = $this->_cookieManager->getCookie(Store::COOKIE_NAME);
        if (isset($storeCode)) {
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

        if ($storage->getStore()->getCode() == $storeCode) {
            $store = $storage->getStore($storeCode);
            if ($store->getWebsite()->getDefaultStore()->getId() == $store->getId()) {
                $this->_cookieManager->deleteCookie(Store::COOKIE_NAME);
            } else {
                $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setDurationOneYear();
                $this->_cookieManager->setPublicCookie(Store::COOKIE_NAME, $storage->getStore()->getCode(), $publicCookieMetadata);
                $this->_httpContext->setValue(
                    Store::ENTITY,
                    $storage->getStore()->getCode(),
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
     * @param StoreManagerInterface $storage
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
