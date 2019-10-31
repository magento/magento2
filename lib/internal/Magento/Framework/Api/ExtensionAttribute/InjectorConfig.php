<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Api\ExtensionAttribute\InjectorConfig\Reader;
use Magento\Framework\Config\Data;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Extension attributes config
 */
class InjectorConfig extends Data
{
    /**
     * Cache identifier
     */
    private const CACHE_ID = 'extension_attributes_injectors_config';

    /**
     * Constructor
     *
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
}
