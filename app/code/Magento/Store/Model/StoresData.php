<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreResolver\ReaderList;

/**
 * Class that computes and stores into cache the active store ids.
 */
class StoresData
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'store_relations';

    /**
     * @param FrontendInterface $cache
     * @param ReaderList $readerList
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly FrontendInterface $cache,
        private readonly ReaderList $readerList,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Get stores data
     *
     * @param string $runMode
     * @param string|null $scopeCode
     * @return array
     */
    public function getStoresData(string $runMode, string $scopeCode = null) : array
    {
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $cacheKey = 'resolved_stores_' . md5($runMode . $scopeCode);
        $cacheData = $this->cache->load($cacheKey);
        if ($cacheData) {
            $storesData = $this->serializer->unserialize($cacheData);
        } else {
            $reader = $this->readerList->getReader($runMode);
            $storesData = [$reader->getAllowedStoreIds($scopeCode), $reader->getDefaultStoreId($scopeCode)];
            $this->cache->save(
                $this->serializer->serialize($storesData),
                $cacheKey,
                [
                    self::CACHE_TAG,
                    Store::CACHE_TAG
                ]
            );
        }
        return $storesData;
    }
}
