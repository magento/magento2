<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class StoreResolver implements \Magento\Store\Model\StoreResolverInterface
{
    /**
     * Cookie name
     */
    const COOKIE_NAME = 'store';

    /**
     * Param name
     */
    const PARAM_NAME = '___store';

    /**
     * Cache tag
     */
    const CACHE_TAG = 'store_relations';

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $cache;

    /**
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
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param StoreResolver\ReaderList $readerList
     * @param string $runMode
     * @param null $scopeCode
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Framework\Cache\FrontendInterface $cache,
        StoreResolver\ReaderList $readerList,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $this->storeRepository = $storeRepository;
        $this->cache = $cache;
        $this->readerList = $readerList;
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentStoreId(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie
    ) {
        $requestedStoreCode = $request->getParam(self::PARAM_NAME, $cookie->getCookie(self::COOKIE_NAME));

        $cacheKey = 'resolved_stores_' . md5($this->runMode . $this->scopeCode);
        $data = $this->cache->load($cacheKey);
        if ($data) {
            list($stores, $defaultStoreId) = unserialize($data);
        } else {
            $reader = $this->readerList->getReader($this->runMode);
            $stores = $reader->getAllowedStoreIds($this->scopeCode);
            $defaultStoreId = $reader->getDefaultStoreId($this->scopeCode);
            $data = serialize([$stores, $defaultStoreId]);
            $this->cache->save($data, $cacheKey, [self::CACHE_TAG]);
        }

        if ($requestedStoreCode) {
            $requestedStore = $this->storeRepository->get($requestedStoreCode);
            if (!in_array($requestedStore->getId(), $stores)) {
                throw new NoSuchEntityException(__('Requested scope cannot be loaded'));
            }
        } else {
            $requestedStore = $this->storeRepository->getById($defaultStoreId);
        }
        if (!$requestedStore->getIsActive()) {
            throw new NoSuchEntityException(__('Requested store is inactive'));
        }
        return $requestedStore->getId();
    }
}
