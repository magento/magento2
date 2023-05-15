<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Cache;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\HydrationSkipConfig;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\ValueProcessorInterface;

/**
 * Prepares cache identifier for resolver data cache record.
 */
class IdentifierPreparator
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var ProviderInterface
     */
    private ProviderInterface $cacheKeyCalculatorProvider;

    /**
     * @var ValueProcessorInterface
     */
    private ValueProcessorInterface $valueProcessor;

    /**
     * @var HydrationSkipConfig
     */
    private $hydrationSkipConfig;

    /**
     * @param SerializerInterface $serializer
     * @param ProviderInterface $keyCalculatorProvider
     * @param ValueProcessorInterface $valueProcessor
     * @param HydrationSkipConfig $hydrationSkipConfig
     */
    public function __construct(
        SerializerInterface $serializer,
        ProviderInterface $keyCalculatorProvider,
        ValueProcessorInterface $valueProcessor,
        HydrationSkipConfig $hydrationSkipConfig
    ) {
        $this->serializer = $serializer;
        $this->cacheKeyCalculatorProvider = $keyCalculatorProvider;
        $this->valueProcessor = $valueProcessor;
        $this->hydrationSkipConfig = $hydrationSkipConfig;
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
    public function prepareCacheIdentifier(
        ResolverInterface $resolver,
        ?array $args,
        ?array $value
    ): string {
        $queryPayloadHash = sha1(get_class($resolver) . $this->serializer->serialize($args ?? []));

        if (!$this->hydrationSkipConfig->isSkipForKeyCalculation($resolver)) {
            $this->valueProcessor->preProcessParentResolverValue($value);
        }

        return GraphQlResolverCache::CACHE_TAG
            . '_'
            . $this->cacheKeyCalculatorProvider->getKeyCalculatorForResolver($resolver)->calculateCacheKey($value)
            . '_'
            . $queryPayloadHash;
    }
}
