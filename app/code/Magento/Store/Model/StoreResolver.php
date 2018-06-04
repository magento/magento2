<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

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
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $cache;

    /**
     * @var \Magento\Store\Model\StoreResolver\ReaderList
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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Store Config
     *
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Store\Model\StoreResolver\ReaderList $readerList
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param string $runMode
     * @param null $scopeCode
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Store\Model\StoreResolver\ReaderList $readerList,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeCookieManager = $storeCookieManager;
        $this->request = $request;
        $this->cache = $cache;
        $this->readerList = $readerList;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentStoreId()
    {
        list($stores, $defaultStoreId) = $this->getStoresData();

        $storeCode = $this->getStoreCodeFromUrl();
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

        try {
            $store = $this->getRequestedStoreByCode($storeCode);
            if (!in_array($store->getId(), $stores)) {
                return $defaultStoreId;
            }
            return $store->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $defaultStoreId;
        }
    }

    /**
     * Get store code from url when 'use store code in url' is enabled
     *
     * @return null|string
     */
    private function getStoreCodeFromUrl() : ?string
    {
        $requestUri = $this->request->getRequestUri();
        if ($this->isUseStoreCodeInUrlEnabled() && '/' !== $requestUri) {
            try {
                //get path Info - without stripping store
                $requestUri = $this->removeRepeatedSlashes($requestUri);
                $parsedRequestUri = explode('?', $requestUri, 2);
                $baseUrl = $this->request->getBaseUrl();
                $pathInfo = (string)substr($parsedRequestUri[0], (int)strlen($baseUrl));

                $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
                /** @var \Magento\Store\Model\Store $store */
                $store = $this->getRequestedStoreByCode($pathParts[0]);

                if (!$this->request->isDirectAccessFrontendName($pathParts[0])
                    && $store->getCode() != Store::ADMIN_CODE) {
                    return $store->getCode();
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the use store code in the url setting enabled
     *
     * @return bool
     */
    private function isUseStoreCodeInUrlEnabled() : bool
    {
        return (bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL);
    }

    /**
     * Remove repeated slashes from the start of the path.
     *
     * @param string $pathInfo
     * @return string
     */
    private function removeRepeatedSlashes($pathInfo) : string
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        if ($firstChar == '/') {
            $pathInfo = '/' . ltrim($pathInfo, '/');
        }

        return $pathInfo;
    }

    /**
     * Get stores data
     *
     * @return array
     */
    protected function getStoresData() : array
    {
        $cacheKey = 'resolved_stores_' . md5($this->runMode . $this->scopeCode);
        $cacheData = $this->cache->load($cacheKey);
        if ($cacheData) {
            $storesData = $this->serializer->unserialize($cacheData);
        } else {
            $storesData = $this->readStoresData();
            $this->cache->save($this->serializer->serialize($storesData), $cacheKey, [self::CACHE_TAG]);
        }
        return $storesData;
    }

    /**
     * Read stores data. First element is allowed store ids, second is default store id
     *
     * @return array
     */
    protected function readStoresData() : array
    {
        $reader = $this->readerList->getReader($this->runMode);
        return [$reader->getAllowedStoreIds($this->scopeCode), $reader->getDefaultStoreId($this->scopeCode)];
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
