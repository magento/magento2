<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Version\Plugin\App;

/**
 * Class CacheProductVersion
 *
 * Caches the product version result.
 */
class CacheProductVersion
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * CacheProductVersion constructor.
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * Cache version result.
     *
     * @param \Magento\Framework\App\ProductMetadata $subject
     * @param callable $proceed
     * @return array|bool|float|int|null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetVersion(\Magento\Framework\App\ProductMetadata $subject, callable $proceed)
    {
        $cache = false;
        if ($this->getCacheKey() && $this->getCacheLifetime()) {
            $cache = $this->cache->load($this->getCacheKey());
        }

        if ($cache) {
            return $this->serializer->unserialize($cache);
        }

        $version = $proceed();

        if ($this->getCacheKey() && $this->getCacheLifetime()) {
            $this->cache->save(
                $this->getCacheKey(),
                $version,
                ['version'],
                $this->getCacheLifetime()
            );
        }

        return $version;
    }

    /**
     * Get cache key.
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'magento_product_version';
    }

    /**
     * Get cache lifetime.
     *
     * @return int
     */
    public function getCacheLifetime(): int
    {
        return 3600;
    }
}
