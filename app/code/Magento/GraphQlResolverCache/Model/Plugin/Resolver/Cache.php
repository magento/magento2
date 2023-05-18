<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Plugin\Resolver;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ResolverIdentityClassProvider;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;

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
     * @var ProviderInterface
     */
    private ProviderInterface $keyCalculatorProvider;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param SerializerInterface $serializer
     * @param CacheState $cacheState
     * @param ResolverIdentityClassProvider $resolverIdentityClassProvider
     * @param ValueProcessorInterface $valueProcessor
     * @param ProviderInterface $keyCalculatorProvider
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        SerializerInterface $serializer,
        CacheState $cacheState,
        ResolverIdentityClassProvider $resolverIdentityClassProvider,
        ValueProcessorInterface $valueProcessor,
        ProviderInterface $keyCalculatorProvider
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
        $this->resolverIdentityClassProvider = $resolverIdentityClassProvider;
        $this->valueProcessor = $valueProcessor;
        $this->keyCalculatorProvider = $keyCalculatorProvider;
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

        $identityProvider = $this->resolverIdentityClassProvider->getIdentityFromResolver($subject);

        if (!$identityProvider) { // not cacheable; proceed
            return $this->executeResolver($proceed, $field, $context, $info, $value, $args);
        }

        // Cache key provider may base cache key on the parent resolver value fields.
        // The value provided must be either original return value or a hydrated value.
        $cacheKey = $this->prepareCacheIdentifier($subject, $args, $value);

        $cachedResult = $this->graphQlResolverCache->load($cacheKey);

        if ($cachedResult !== false) {
            $resolvedValue = $this->serializer->unserialize($cachedResult);
            $this->valueProcessor->processCachedValueAfterLoad($subject, $cacheKey, $resolvedValue);
            return $resolvedValue;
        }

        $resolvedValue = $this->executeResolver($proceed, $field, $context, $info, $value, $args);

        // parent value is preprocessed (hydrated) on the previous step
        $identities = $identityProvider->getIdentities($resolvedValue, $value);

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

    /**
     * Call proceed method with context.
     *
     * @param \Closure $closure
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    private function executeResolver(
        \Closure $closure,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        array &$value = null,
        array $args = null
    ) {
        $this->valueProcessor->preProcessParentValue($value);
        return $closure($field, $context, $info, $value, $args);
    }

    /**
     * Generate cache key incorporating factors from parameters.
     *
     * @param ResolverInterface $resolver
     * @param array|null $args
     * @param array|null $value
     *
     * @return string
     */
    private function prepareCacheIdentifier(
        ResolverInterface $resolver,
        ?array $args,
        ?array $value
    ): string {
        $queryPayloadHash = sha1(get_class($resolver) . $this->serializer->serialize($args ?? []));

        return GraphQlResolverCache::CACHE_TAG
            . '_'
            . $this->keyCalculatorProvider->getKeyCalculatorForResolver($resolver)->calculateCacheKey($value)
            . '_'
            . $queryPayloadHash;
    }
}
