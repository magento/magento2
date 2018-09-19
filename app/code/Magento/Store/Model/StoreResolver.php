<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

/**
 * Class used to resolve store from url path or get parameters or cookie.
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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var StoresData
     */
    private $storesData;

    /**
     * @var \Magento\Store\App\Request\StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoresData $storesData
     * @param \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator
     * @param string|null $runMode
     * @param string|null $scopeCode
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoresData $storesData,
        \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeCookieManager = $storeCookieManager;
        $this->request = $request;
        $this->storePathInfoValidator = $storePathInfoValidator;
        $this->storesData = $storesData;
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentStoreId()
    {
        list($stores, $defaultStoreId) = $this->getStoresData();

        $storeCode = $this->storePathInfoValidator->getValidStoreCode($this->request);

        if (!$storeCode) {
            $storeCode = $this->request->getParam(
                \Magento\Store\Model\StoreManagerInterface::PARAM_NAME,
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
     * @see \Magento\Store\Model\StoreResolver::getStoresData
     */
    protected function readStoresData() : array
    {
        return $this->getStoresData();
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
