<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\InitializableCacheIdFactorProviderInterface;
use Magento\GraphQlCache\Model\Resolver\Cache\ResolverCacheIdCalculator;
use Magento\GraphQlCache\Model\Resolver\Cache\ResolverCacheIdCalculatorFactory;

/**
 * Provides custom cache id providers for resolvers chain.
 */
class Strategy implements StrategyInterface
{
    /**
     * @var array
     */
    private array $customFactorProviders = [];

    /**
     * @var array
     */
    private array $resolverCacheIdCalculatorsInitialized = [];

    /**
     * @var ResolverCacheIdCalculator
     */
    private ResolverCacheIdCalculator $genericCacheIdCalculator;

    /**
     * @var ResolverCacheIdCalculatorFactory
     */
    private ResolverCacheIdCalculatorFactory $cacheIdCalculatorFactory;

    /**
     * @param ResolverCacheIdCalculatorFactory $cacheIdCalculatorFactory
     * @param array $customFactorProviders
     */
    public function __construct(
        ResolverCacheIdCalculatorFactory $cacheIdCalculatorFactory,
        array $customFactorProviders = []
    ) {
        $this->customFactorProviders = $customFactorProviders;
        $this->cacheIdCalculatorFactory = $cacheIdCalculatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function initForResolver(ResolverInterface $resolver): void
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (isset($this->resolverCacheIdCalculatorsInitialized[$resolverClass])) {
            return;
        }
        $customProviders = $this->getCustomProvidersForResolverObject($resolver);
        if (empty($customProviders)) {
            if (empty($this->genericCacheIdCalculator)) {
                $this->genericCacheIdCalculator = $this->cacheIdCalculatorFactory->create();
            }
            $this->resolverCacheIdCalculatorsInitialized[$resolverClass] = $this->genericCacheIdCalculator;
        }

        $this->resolverCacheIdCalculatorsInitialized[$resolverClass] =
            $this->cacheIdCalculatorFactory->create($customProviders);
    }

    /**
     * @inheritDoc
     */
    public function getCacheIdCalculatorForResolver(ResolverInterface $resolver): ResolverCacheIdCalculator
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (!isset($this->resolverCacheIdCalculatorsInitialized[$resolverClass])) {
            $this->initForResolver($resolver);
        }
        return $this->resolverCacheIdCalculatorsInitialized[$resolverClass];
    }

    /**
     * Get class inheritance chain for the given resolver object.
     *
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getResolverClassChain(ResolverInterface $resolver): array
    {
        $resolverClasses = [trim(get_class($resolver), '\\')];
        foreach (class_parents($resolver) as $classParent) {
            $resolverClasses[] = trim($classParent, '\\');
        }
        return $resolverClasses;
    }

    /**
     * Get custom factor providers for the given resolver object.
     *
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getCustomProvidersForResolverObject(ResolverInterface $resolver): array
    {
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (!empty($this->customFactorProviders[$resolverClass])) {
                return $this->customFactorProviders[$resolverClass];
            }
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function actualize(ResolverInterface $resolver, ?array $result, ContextInterface $context): void
    {
        if (!is_array($result)) {
            return;
        }
        $resolverClass = trim(get_class($resolver), '\\');
        if (!isset($this->resolverCacheIdCalculatorsInitialized[$resolverClass])) {
            return;
        }
        foreach ($this->resolverCacheIdCalculatorsInitialized[$resolverClass] as $cacheIdProvider) {
            if ($cacheIdProvider instanceof InitializableCacheIdFactorProviderInterface) {
                $cacheIdProvider->initialize($result, $context);
            }
        }
        $this->genericCacheIdCalculator->initialize($result, $context);
    }
}
