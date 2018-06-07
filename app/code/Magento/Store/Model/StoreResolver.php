<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

/**
 * Class used to resolve store from url path or get parameters or cookie
 */
class StoreResolver implements \Magento\Store\Api\StoreResolverInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'store_relations';

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @deprecated
     */
    protected $cache;

    /**
     * @deprecated
     */
    protected $readerList;

    /**
     * @var string
     */
    protected $runMode;

    /**
     * @var string
     */
    protected $scopeCode;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var StoresData
     */
    private $storesData;

    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     */
    private $pathInfoProcessor;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Cache\FrontendInterface|null $cache
     * @param \Magento\Store\Model\StoreResolver\ReaderList|null $readerList
     * @param string|null $runMode
     * @param string|null $scopeCode
     * @param \Magento\Store\Model\StoresData|null $storesData
     * @param \Magento\Store\App\Request\PathInfoProcessor|null $pathInfoProcessor
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager,
        \Magento\Framework\App\RequestInterface $request,
        $cache = null,
        $readerList = null,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null,
        \Magento\Store\Model\StoresData $storesData = null,
        \Magento\Store\App\Request\PathInfoProcessor $pathInfoProcessor = null
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeCookieManager = $storeCookieManager;
        $this->request = $request;
        $this->cache = $cache ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Framework\App\Cache\Type\Config'
        );
        $this->readerList = $readerList ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Store\Model\StoreResolver\ReaderList'
        );
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode;
        $this->storesData = $storesData ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Store\Model\StoresData::class
        );
        $this->pathInfoProcessor = $pathInfoProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Store\App\Request\PathInfoProcessor::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentStoreId()
    {
        list($stores, $defaultStoreId) = $this->getStoresData();

        $storeCode = $this->pathInfoProcessor->resolveStoreFrontStoreFromPathInfo($this->request);
        if (!$storeCode) {
            $storeCode = $this->request->getParam(
                self::PARAM_NAME,
                $this->storeCookieManager->getStoreCodeFromCookie()
            );
        }

        if (is_array($storeCode)) {
            if (!isset($storeCode['_data']['code'])) {
                throw new \InvalidArgumentException(__('Invalid store parameter.'));
            }
            $storeCode = $storeCode['_data']['code'];
        }

        if ($storeCode) {
            try {
                $store = $this->getRequestedStoreByCode($storeCode);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $store = $this->getDefaultStoreById($defaultStoreId);
            }

            if (!in_array($store->getId(), $stores)) {
                $store = $this->getDefaultStoreById($defaultStoreId);
            }
        } else {
            $store = $this->getDefaultStoreById($defaultStoreId);
        }
        return $store->getId();
    }

    /**
     * Get stores data
     *
     * @return array
     */
    protected function getStoresData() : array
    {
        return $this->storesData->getStoresData($this->runMode, $this->scopeCode);
    }

    /**
     * Read stores data. First element is allowed store ids, second is default store id
     *
     * @return array
     * @deprecated
     */
    protected function readStoresData() : array
    {
        return $this->storesData->getStoresData($this->runMode, $this->scopeCode);
    }

    /**
     * Retrieve active store by code
     *
     * @param string $storeCode
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getRequestedStoreByCode($storeCode) : \Magento\Store\Api\Data\StoreInterface
    {
        try {
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
        } catch (StoreIsInactiveException $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested store is inactive'));
        }

        return $store;
    }

    /**
     * Retrieve active store by code
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getDefaultStoreById($id) : \Magento\Store\Api\Data\StoreInterface
    {
        try {
            $store = $this->storeRepository->getActiveStoreById($id);
        } catch (StoreIsInactiveException $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Default store is inactive'));
        }

        return $store;
    }
}
