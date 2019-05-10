<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

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
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var \Magento\Store\Model\StoreResolver\ReaderList
     */
    private $readerList;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Store\Model\StoreResolver\ReaderList $readerList
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Store\Model\StoreResolver\ReaderList $readerList,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->readerList = $readerList;
        $this->serializer = $serializer;
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
                    \Magento\Store\Model\Store::CACHE_TAG
                ]
            );
        }
        return $storesData;
    }
}
