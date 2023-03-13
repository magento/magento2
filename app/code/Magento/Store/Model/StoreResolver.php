<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use InvalidArgumentException;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\App\Request\StorePathInfoValidator;

/**
 * Class used to resolve store from url path or get parameters or cookie.
 */
class StoreResolver implements StoreResolverInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'store_relations';

    /**
     * @deprecated 101.0.0
     */
    protected $cache;

    /**
     * @deprecated 101.0.0
     */
    protected $readerList;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param Http $request
     * @param StoresData $storesData
     * @param StorePathInfoValidator $storePathInfoValidator
     * @param string|null $runMode
     * @param string|null $scopeCode
     */
    public function __construct(
        protected readonly StoreRepositoryInterface $storeRepository,
        protected readonly StoreCookieManagerInterface $storeCookieManager,
        protected readonly Http $request,
        private readonly StoresData $storesData,
        private readonly StorePathInfoValidator $storePathInfoValidator,
        protected $runMode = ScopeInterface::SCOPE_STORE,
        protected $scopeCode = null
    ) {
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
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
                StoreManagerInterface::PARAM_NAME,
                $this->storeCookieManager->getStoreCodeFromCookie()
            );
        }

        if (is_array($storeCode)) {
            if (!isset($storeCode['_data']['code'])) {
                throw new InvalidArgumentException(__('Invalid store parameter.'));
            }
            $storeCode = $storeCode['_data']['code'];
        }

        if ($storeCode) {
            try {
                $store = $this->getRequestedStoreByCode($storeCode);
            } catch (NoSuchEntityException $e) {
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
     * @deprecated 101.0.0
     * @see StoreResolver::getStoresData
     */
    protected function readStoresData() : array
    {
        return $this->getStoresData();
    }

    /**
     * Retrieve active store by code
     *
     * @param string $storeCode
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    protected function getRequestedStoreByCode($storeCode): StoreInterface
    {
        try {
            $store = $this->storeRepository->getActiveStoreByCode($storeCode);
        } catch (StoreIsInactiveException $e) {
            throw new NoSuchEntityException(__('Requested store is inactive'));
        }

        return $store;
    }

    /**
     * Retrieve active store by code
     *
     * @param int $id
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    protected function getDefaultStoreById($id): StoreInterface
    {
        try {
            $store = $this->storeRepository->getActiveStoreById($id);
        } catch (StoreIsInactiveException $e) {
            throw new NoSuchEntityException(__('Default store is inactive'));
        }

        return $store;
    }
}
