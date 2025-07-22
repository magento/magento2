<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides search request configuration
 */
class Config extends \Magento\Framework\Config\Data
{
    /**
     * Cache identifier
     */
    public const CACHE_ID = 'request_declaration';

    /**
     * Constructor
     *
     * @param \Magento\Framework\Search\Request\Config\FilesystemReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @param array|null $cacheTags
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        \Magento\Framework\Search\Request\Config\FilesystemReader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = self::CACHE_ID,
        ?SerializerInterface $serializer = null,
        ?array $cacheTags = null,
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer, $cacheTags);
    }
}
