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
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\CalculationException;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ResolverIdentityClassProvider;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to cache resolver result where applicable.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param SerializerInterface $serializer
     * @param CacheState $cacheState
     * @param ResolverIdentityClassProvider $resolverIdentityClassProvider
     * @param ValueProcessorInterface $valueProcessor
     * @param ProviderInterface $keyCalculatorProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        SerializerInterface $serializer,
        CacheState $cacheState,
        ResolverIdentityClassProvider $resolverIdentityClassProvider,
        ValueProcessorInterface $valueProcessor,
        ProviderInterface $keyCalculatorProvider,
        LoggerInterface $logger
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->serializer = $serializer;
        $this->cacheState = $cacheState;
        $this->resolverIdentityClassProvider = $resolverIdentityClassProvider;
        $this->valueProcessor = $valueProcessor;
        $this->keyCalculatorProvider = $keyCalculatorProvider;
        $this->logger = $logger;
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

        // Cache key provider may base cache key on the parent resolver value
        // $value is processed on key calculation if needed
        try {
            $cacheKey = $this->prepareCacheIdentifier($subject, $args, $value);
        } catch (CalculationException $e) {
            $this->logger->warning(
                sprintf(
                    "Unable to obtain cache key for %s resolver results, proceeding to invoke resolver."
                    . "Original exception message: %s ",
                    get_class($subject),
                    $e->getMessage()
                )
            );
            return $this->executeResolver($proceed, $field, $context, $info, $value, $args);
        }

        $cachedResult = $this->graphQlResolverCache->load($cacheKey);

        if ($cachedResult !== false) {
            $returnValue = $this->serializer->unserialize($cachedResult);
            $this->valueProcessor->processCachedValueAfterLoad($info, $subject, $cacheKey, $returnValue);
            return $returnValue;
        }

        $returnValue = $this->executeResolver($proceed, $field, $context, $info, $value, $args);

        // $value (parent value) is preprocessed (hydrated) on the previous step
        $identities = $identityProvider->getIdentities($returnValue, $value);

        if (count($identities)) {
            $cachedValue = $returnValue;
            $this->valueProcessor->preProcessValueBeforeCacheSave($subject, $cachedValue);
            $this->graphQlResolverCache->save(
                $this->serializer->serialize($cachedValue),
                $cacheKey,
                $identities,
                false // use default lifetime directive
            );
            unset($cachedValue);
        }

        return $returnValue;
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
        if (is_array($value)) {
            $this->valueProcessor->preProcessParentValue($value);
        }
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
     * @throws CalculationException
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
