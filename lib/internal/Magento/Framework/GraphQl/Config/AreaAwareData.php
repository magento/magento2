<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\App\Area;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * GraphQL configuration data must only be loaded with GraphQL area dependencies.
 */
class AreaAwareData extends ConfigData
{
    /**
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param ScopeInterface $configScope
     * @param SerializerInterface|null $serializer
     * @param array|null $cacheTags
     * @throws LocalizedException
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        ScopeInterface $configScope,
        ?SerializerInterface $serializer = null,
        ?array $cacheTags = null
    ) {
        if ($configScope->getCurrentScope() !== Area::AREA_GRAPHQL) {
            throw new LocalizedException(__('GraphQL configuration can only be loaded in the graphql area.'));
        }

        parent::__construct($reader, $cache, $cacheId, $serializer, $cacheTags);
    }
}
