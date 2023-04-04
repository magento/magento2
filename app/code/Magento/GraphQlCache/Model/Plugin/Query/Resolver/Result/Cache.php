<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Plugin\Query\Resolver\Result;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\GraphQlCache\Model\Resolver\IdentityPool;

/**
 * Plugin to cache resolver result where applicable
 */
class Cache
{
    /**
     * GraphQL Resolver cache type
     *
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var CacheIdCalculator
     */
    private $cacheIdCalculator;

    /**
     * @var IdentityPool
     */
    private $identityPool;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheState
     */
    private $cacheState;

    /**
     * @var string[]
     */
    private array $cacheableResolverClassNameIdentityMap;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param CacheIdCalculator $cacheIdCalculator
     * @param IdentityPool $identityPool
     * @param SerializerInterface $serializer
     * @param CacheState $cacheState
     * @param string[] $cacheableResolverClassNameIdentityMap
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        CacheIdCalculator $cacheIdCalculator,
        IdentityPool $identityPool,
        SerializerInterface $serializer,
        CacheState $cacheState,
        array $cacheableResolverClassNameIdentityMap = []
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->cacheIdCalculator = $cacheIdCalculator;
        $this->identityPool = $identityPool;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
        $this->cacheableResolverClassNameIdentityMap = $cacheableResolverClassNameIdentityMap;
    }

    /**
     * Checks for cacheability of resolver's data, and, if cacheable, loads and persists cache entry for future use
     *
     * @param ResolverInterface $subject
     * @param \Closure $proceed
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     */
    public function aroundResolve(
        ResolverInterface $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        // even though a frontend access proxy is used to prevent saving/loading in $graphQlResolverCache when it is
        // disabled, it's best to return as early as possible to avoid unnecessary processing
        if (!$this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER)) {
            return $proceed($field, $context, $info, $value, $args);
        }

        $isQuery = $info->operation->operation === 'query';

        if (!$isQuery) {
            return $proceed($field, $context, $info, $value, $args);
        }

        $resolverClassHierarchy = array_merge(
            [get_class($subject) => get_class($subject)],
            class_parents($subject),
            class_implements($subject)
        );

        $cacheableResolverClassNames = array_keys($this->cacheableResolverClassNameIdentityMap);

        $matchingCacheableResolverClassNames = array_intersect($cacheableResolverClassNames, $resolverClassHierarchy);

        $isResolverCacheable = (bool) $matchingCacheableResolverClassNames;

        if (!$isResolverCacheable) {
            return $proceed($field, $context, $info, $value, $args);
        }

        $cacheIdentityFullPageContextString = $this->cacheIdCalculator->getCacheId();

        $cacheIdentityQueryPayloadString = $info->returnType->name . $this->serializer->serialize($args ?? []);

        $cacheIdentityString = GraphQlResolverCache::CACHE_TAG
            . '_'
            . $cacheIdentityFullPageContextString
            . '_'
            . sha1($cacheIdentityQueryPayloadString);

        $cachedResult = $this->graphQlResolverCache->load($cacheIdentityString);

        if ($cachedResult !== false) {
            return $this->serializer->unserialize($cachedResult);
        }

        $resolvedValue = $proceed($field, $context, $info, $value, $args);

        $matchingCacheableResolverClassName = reset($matchingCacheableResolverClassNames);
        $matchingCacheableResolverIdentityClassName = $this->cacheableResolverClassNameIdentityMap[
            $matchingCacheableResolverClassName
        ];

        $cacheableResolverIdentity = $this->identityPool->get($matchingCacheableResolverIdentityClassName);

        $this->graphQlResolverCache->save(
            $this->serializer->serialize($resolvedValue),
            $cacheIdentityString,
            $cacheableResolverIdentity->getIdentities($resolvedValue)
        );

        return $resolvedValue;
    }
}
