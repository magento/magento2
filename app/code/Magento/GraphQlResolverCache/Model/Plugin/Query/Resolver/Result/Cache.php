<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Plugin\Query\Resolver\Result;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQlResolverCache\Model\Cache\IdentifierPreparator;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\ResolverIdentityClassProvider;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\ValueProcessorInterface;
use Magento\GraphQlResolverCache\Model\Cache\ResolverExecutor;
use Magento\GraphQlResolverCache\Model\Cache\ResolverExecutorFactory;

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
     * @var ResolverIdentityClassProvider
     */
    private $resolverIdentityClassProvider;

    /**
     * @var ValueProcessorInterface
     */
    private ValueProcessorInterface $valueProcessor;

    /**
     * @var IdentifierPreparator
     */
    private IdentifierPreparator $identifierPreparator;

    /**
     * @var ResolverExecutorFactory
     */
    private ResolverExecutorFactory $resolverExecutorFactory;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param SerializerInterface $serializer
     * @param CacheState $cacheState
     * @param ResolverIdentityClassProvider $resolverIdentityClassProvider
     * @param IdentifierPreparator $identifierPreparator
     * @param ResolverExecutorFactory $resolverExecutorFactory
     * @param ValueProcessorInterface $valueProcessor
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        SerializerInterface $serializer,
        CacheState $cacheState,
        ResolverIdentityClassProvider $resolverIdentityClassProvider,
        IdentifierPreparator $identifierPreparator,
        ResolverExecutorFactory $resolverExecutorFactory,
        ValueProcessorInterface $valueProcessor
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
        $this->resolverIdentityClassProvider = $resolverIdentityClassProvider;
        $this->identifierPreparator = $identifierPreparator;
        $this->resolverExecutorFactory = $resolverExecutorFactory;
        $this->valueProcessor = $valueProcessor;
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
        if (!$this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER)
            || $info->operation->operation !== 'query'
        ) {
            return $proceed($field, $context, $info, $value, $args);
        }

        $resolverExecutor = $this->resolverExecutorFactory->create(['resolveMethod' => $proceed]);

        $identityProvider = $this->resolverIdentityClassProvider->getIdentityFromResolver($subject);

        if (!$identityProvider) { // not cacheable; proceed
            return $resolverExecutor->resolve($subject, $field, $context, $info, $value, $args);
        }

        // Cache key provider may base cache key on the parent resolver value fields.
        // The value provided must be either original return value or a hydrated value.
        $cacheKey = $this->identifierPreparator->prepareCacheIdentifier($subject, $args, $value);

        $cachedResult = $this->graphQlResolverCache->load($cacheKey);

        if ($cachedResult !== false) {
            $resolvedValue = $this->serializer->unserialize($cachedResult);
            $this->valueProcessor->processCachedValueAfterLoad($subject, $cacheKey, $resolvedValue);
            return $resolvedValue;
        }

        $resolvedValue = $resolverExecutor->resolve($subject, $field, $context, $info, $value, $args);

        $identities = $identityProvider->getIdentities($resolvedValue);

        if (count($identities)) {
            $cachedValue = $resolvedValue;
            $this->valueProcessor->preProcessValueBeforeCacheSave($subject, $cachedValue);
            $this->graphQlResolverCache->save(
                $this->serializer->serialize($cachedValue),
                $cacheKey,
                $identities,
                false, // use default lifetime directive
            );
        }

        return $resolvedValue;
    }
}
