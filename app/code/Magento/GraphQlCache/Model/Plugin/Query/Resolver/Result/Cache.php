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
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator\ProviderInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\HydratorInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\HydratorProviderInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\ResolverIdentityClassLocator;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;

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
     * @var ProviderInterface
     */
    private ProviderInterface $cacheIdProviderStrategy;

    /**
     * @var HydratorProviderInterface
     */
    private HydratorProviderInterface $hydratorProvider;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param SerializerInterface $serializer
     * @param CacheState $cacheState
     * @param ResolverIdentityClassLocator $resolverIdentityClassLocator
     * @param ProviderInterface $cacheIdProviderStrategy
     * @param HydratorProviderInterface $hydratorProvider
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        SerializerInterface $serializer,
        CacheState $cacheState,
        ResolverIdentityClassLocator $resolverIdentityClassLocator,
        ProviderInterface $cacheIdProviderStrategy,
        HydratorProviderInterface $hydratorProvider
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
        $this->resolverIdentityClassLocator = $resolverIdentityClassLocator;
        $this->cacheIdProviderStrategy = $cacheIdProviderStrategy;
        $this->hydratorProvider = $hydratorProvider;
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

        // prehydrate the parent resolver data so that it contained all needed models
        // for the nested resolvers calls
        if ($value && isset($value['hydrator_instance']) && $value['hydrator_instance'] instanceof HydratorInterface) {
            $value['hydrator_instance']->hydrate($value);
            unset($value['hydrator_instance']);
        }

        $identityProvider = $this->resolverIdentityClassLocator->getIdentityFromResolver($subject);

        if (!$identityProvider) { // not cacheable; proceed
            return $proceed($field, $context, $info, $value, $args);
        }

        $cacheIdentityString = $this->prepareCacheIdentityString($subject, $info, $args, $value);

        $cachedResult = $this->graphQlResolverCache->load($cacheIdentityString);

        if ($cachedResult !== false) {
            $resolvedValue = $this->serializer->unserialize($cachedResult);
            $hydrator = $this->hydratorProvider->getForResolver($subject);
            if ($hydrator) {
                $resolvedValue['hydrator_instance'] = $hydrator;
            }
            return $resolvedValue;
        }

        $resolvedValue = $proceed($field, $context, $info, $value, $args);

        $identities = $identityProvider->getIdentities($resolvedValue);

        if (count($identities)) {
            $this->graphQlResolverCache->save(
                $this->serializer->serialize($resolvedValue),
                $cacheIdentityString,
                $identities,
                false, // use default lifetime directive
            );
        }

        return $resolvedValue;
    }

    /**
     * Prepare cache identity string incorporating factors from parameters.
     *
     * @param ResolverInterface $resolver
     * @param ResolveInfo $info
     * @param array|null $args
     * @param array|null $value
     *
     * @return string
     */
    private function prepareCacheIdentityString(
        ResolverInterface $resolver,
        ResolveInfo $info,
        ?array $args,
        ?array $value
    ): string {
        $cacheIdentityString = $this->cacheIdProviderStrategy->getForResolver($resolver)->calculateCacheKey($value);
        $cacheIdQueryPayloadString = $info->returnType->name . $this->serializer->serialize($args ?? []);
        return GraphQlResolverCache::CACHE_TAG . '_' . $cacheIdentityString . '_' . sha1($cacheIdQueryPayloadString);
    }
}
