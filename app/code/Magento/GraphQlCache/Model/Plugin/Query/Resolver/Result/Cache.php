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
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\StrategyInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\ResolverIdentityClassLocator;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheState
     */
    private $cacheState;

    /**
     * @var ResolverIdentityClassLocator
     */
    private $resolverIdentityClassLocator;

    /**
     * @var StrategyInterface
     */
    private StrategyInterface $cacheIdProviderStrategy;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param SerializerInterface $serializer
     * @param CacheState $cacheState
     * @param ResolverIdentityClassLocator $resolverIdentityClassLocator
     * @param StrategyInterface $cacheIdProviderStrategy
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        SerializerInterface $serializer,
        CacheState $cacheState,
        ResolverIdentityClassLocator $resolverIdentityClassLocator,
        StrategyInterface $cacheIdProviderStrategy
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
        $this->resolverIdentityClassLocator = $resolverIdentityClassLocator;
        $this->cacheIdProviderStrategy = $cacheIdProviderStrategy;
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

        $identityProvider = $this->resolverIdentityClassLocator->getIdentityFromResolver($subject);

        if (!$identityProvider) { // not cacheable; proceed
            return $proceed($field, $context, $info, $value, $args);
        }

        $cacheIdentityString = $this->prepareCacheIdentityString($subject, $info, $args, $value);

        $cachedResult = $this->graphQlResolverCache->load($cacheIdentityString);

        if ($cachedResult !== false) {
            return $this->serializer->unserialize($cachedResult);
            // todo add rehydration handler
        }

        $resolvedValue = $proceed($field, $context, $info, $value, $args);

        //todo check recursively for models and unset all objects
        $this->graphQlResolverCache->save(
            $this->serializer->serialize($resolvedValue),
            $cacheIdentityString,
            $identityProvider->getIdentities($resolvedValue)
        );

        return $resolvedValue;
    }

    /**
     * Prepare cache identity string incorporating factors from parameters.
     *
     * @param ResolverInterface $resolver
     * @param ResolveInfo $info
     * @param array|null $args
     * @param array|null $value
     * @return string
     */
    private function prepareCacheIdentityString(ResolverInterface $resolver, ResolveInfo $info, ?array $args, ?array $value): string
    {

        $this->cacheIdProviderStrategy->initForResolver($resolver);
        $this->cacheIdProviderStrategy->restateFromPreviousResolvedValues($resolver, $value);
        $cacheIdentityFullPageContextString = $this->cacheIdProviderStrategy
            ->getCacheIdCalculatorForResolver($resolver)
            ->getCacheId();

        $cacheIdentityQueryPayloadString = $info->returnType->name . $this->serializer->serialize($args ?? []);

        return GraphQlResolverCache::CACHE_TAG
            . '_'
            . $cacheIdentityFullPageContextString
            . '_'
            . sha1($cacheIdentityQueryPayloadString);
    }
}
