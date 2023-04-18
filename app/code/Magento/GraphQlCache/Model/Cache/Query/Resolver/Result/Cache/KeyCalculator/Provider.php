<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculatorFactory;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;

/**
 * Provides custom cache id providers for resolvers chain.
 */
class Provider implements ProviderInterface
{
    /**
     * @var array
     */
    private array $customFactorProviders = [];

    /**
     * @var array
     */
    private array $keyCalculatorInstances = [];

    /**
     * @var KeyCalculator
     */
    private KeyCalculator $genericKeyCalculator;

    /**
     * @var KeyCalculatorFactory
     */
    private KeyCalculatorFactory $keyCalculatorFactory;

    /**
     * @param KeyCalculatorFactory $keyCalculatorFactory
     * @param array $customFactorProviders
     */
    public function __construct(
        KeyCalculatorFactory $keyCalculatorFactory,
        array $customFactorProviders = []
    ) {
        $this->customFactorProviders = $customFactorProviders;
        $this->keyCalculatorFactory = $keyCalculatorFactory;
    }

    /**
     * Initialize custom cache key calculator for the given resolver.
     *
     * @param ResolverInterface $resolver
     *
     * @return void
     */
    private function initForResolver(ResolverInterface $resolver): void
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (isset($this->keyCalculatorInstances[$resolverClass])) {
            return;
        }
        $customProviders = $this->getCustomProvidersForResolverObject($resolver);
        if (empty($customProviders)) {
            if (empty($this->genericKeyCalculator)) {
                $this->genericKeyCalculator = $this->keyCalculatorFactory->create();
            }
            $this->keyCalculatorInstances[$resolverClass] = $this->genericKeyCalculator;
        } else {
            $runtimePoolKey = $this->generateCustomProvidersKey($customProviders);
            if (!isset($this->keyCalculatorInstances[$runtimePoolKey])) {
                $this->keyCalculatorInstances[$runtimePoolKey] = $this->keyCalculatorFactory->create($customProviders);
            }
            $this->keyCalculatorInstances[$resolverClass] = $this->keyCalculatorInstances[$runtimePoolKey];
        }
    }

    /**
     * Generate runtime pool key from the set of custom providers.
     *
     * @param array $customProviders
     * @return string
     */
    private function generateCustomProvidersKey(array $customProviders): string
    {
        $keyArray = [];
        /** @var CacheIdFactorProviderInterface $customProvider */
        foreach ($customProviders as $customProvider) {
            $keyArray[] = $customProvider->getFactorName();
        }
        sort($keyArray);
        return implode('_', $keyArray);
    }

    /**
     * @inheritDoc
     */
    public function getKeyCalculatorForResolver(ResolverInterface $resolver): KeyCalculator
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (!isset($this->keyCalculatorInstances[$resolverClass])) {
            $this->initForResolver($resolver);
        }
        return $this->keyCalculatorInstances[$resolverClass];
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
}
