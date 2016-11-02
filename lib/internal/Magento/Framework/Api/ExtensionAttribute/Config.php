<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Api\ExtensionAttribute\Config\Reader;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Extension attributes config
 */
class Config extends \Magento\Framework\Config\Data
{
    const CACHE_ID = 'extension_attributes_config';

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
}
