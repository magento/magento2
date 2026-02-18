<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class used to resolve store from url path or get parameters or cookie.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class StoreResolver implements StoreResolverInterface
{
    /**
     * Cache tag
     */
    public const CACHE_TAG = 'store_relations';

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @deprecated 101.0.0
     * @see No longer needed
     *
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @deprecated 101.0.0
     * @see No longer needed
     *
     * @var StoreResolver\ReaderList
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
     * @var Http
     */
    protected $request;

    /**
     * @var StoresData
     */
    private $storesData;

    /**
     * @var StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManagerInterface;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param Http $request
     * @param StoresData $storesData
     * @param StorePathInfoValidator $storePathInfoValidator
     * @param string $runMode
     * @param string|null $scopeCode
     * @param CookieManagerInterface|null $cookieManagerInterface
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        StoreCookieManagerInterface $storeCookieManager,
        Http $request,
        StoresData $storesData,
        StorePathInfoValidator $storePathInfoValidator,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null,
        ?CookieManagerInterface $cookieManagerInterface = null
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeCookieManager = $storeCookieManager;
        $this->request = $request;
        $this->storePathInfoValidator = $storePathInfoValidator;
        $this->storesData = $storesData;
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode;
        $this->cookieManagerInterface = $cookieManagerInterface ?:
            ObjectManager::getInstance()->get(CookieManagerInterface::class);
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
                throw new \InvalidArgumentException(__('Invalid store parameter.'));
            }
            $storeCode = $storeCode['_data']['code'];
        }

        if ($storeCode) {
            try {
                $store = $this->getRequestedStoreByCode($storeCode);
            } catch (NoSuchEntityException $e) {
                $this->request->setQueryValue(StoreManagerInterface::PARAM_NAME);
                $this->cookieManagerInterface->deleteCookie(StoreCookieManager::COOKIE_NAME);
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
    protected function getRequestedStoreByCode($storeCode) : StoreInterface
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
    protected function getDefaultStoreById($id) : StoreInterface
    {
        try {
            $store = $this->storeRepository->getActiveStoreById($id);
        } catch (StoreIsInactiveException $e) {
            throw new NoSuchEntityException(__('Default store is inactive'));
        }

        return $store;
    }
}
