<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Config\CacheInterface\Proxy as CacheInterface;
use Magento\Framework\Config\Data;
use Magento\Framework\Dto\Config\Reader\Proxy as Reader;
use Magento\Framework\Serialize\SerializerInterface\Proxy as SerializerInterface;

/**
 * DTO config
 */
class DtoConfig extends Data
{
    /**
     * Cache identifier
     */
    private const CACHE_ID = 'dto_config';

    /**
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId|null
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        $cacheId = self::CACHE_ID,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Return true if a class is defined in the dto.xml config
     *
     * @param string $className
     * @return bool
     */
    public function isDto(string $className): bool
    {
        return (bool) $this->get($className, false);
    }

    /**
     * Return true if a class is defined as immutable DTO
     *
     * @param string $className
     * @return bool
     */
    public function isImmutable(string $className): bool
    {
        $config = $this->get($className);
        if (!$config) {
            return false;
        }

        return !$config['mutable'];
    }
}
