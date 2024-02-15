<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Asset;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Stores computed HASH of file in a key value pair
 */
class Cache
{
    /**
     * @var CacheInterface
     */
    private CacheInterface $cacheManager;

    /**
     * Constant for Cache Prefix
     */
    public const CACHE_PREFIX = 'INTEGRITY_HASH';

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * constructor
     *
     * @param CacheInterface $cacheManager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cacheManager,
        SerializerInterface $serializer
    ) {
        $this->cacheManager = $cacheManager;
        $this->serializer = $serializer;
    }

    /**
     * Get Integrity Hash from Cache
     *
     * @param string $path
     * @return array|false
     */
    public function get(string $path): array|false
    {
        $identifier = self::CACHE_PREFIX . $path;
        $cacheData = $this->cacheManager->load($identifier);
        if (!$cacheData) {
            return false;
        } else {
            return $this->serializer->unserialize($cacheData);
        }
    }

    /**
     * Save integrity data to cache
     *
     * @param array $data
     * @param string $path
     * @return void
     */
    public function save(array $data, string $path): void
    {
        $cacheIdentifier = self::CACHE_PREFIX . $path;
        $this->cacheManager->save(
            $this->serializer->serialize($data),
            $cacheIdentifier,
            [self::CACHE_PREFIX]
        );
    }

    /**
     * Clear contents of cache
     *
     * @param string $path
     * @return void
     */
    public function delete(string $path): void
    {
        $this->cacheManager->clean([self::CACHE_PREFIX.$path]);
    }
}
