<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;

/**
 * Provides cache key calculators for the resolvers chain.
 */
class Provider implements ProviderInterface
{
    /**
     * @var array
     */
    private array $factorProviders = [];

    /**
     * @var array
     */
    private array $keyCalculatorInstances = [];

    /**
     * Dev docs link
     */
    private const DEV_DOCS = "https://developer.adobe.com/commerce/webapi/graphql/develop";

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $factorProviders
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $factorProviders = []
    ) {
        $this->objectManager = $objectManager;
        $this->factorProviders = $factorProviders;
    }

    /**
     * Initialize cache key calculator for the given resolver.
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
        $factorProviders = $this->getFactorProvidersForResolver($resolver);
        if ($factorProviders === null) {
            $devDocs = self::DEV_DOCS;
            throw new \InvalidArgumentException(
                "GraphQL Resolver Cache key factors are not determined for {$resolverClass} or its parents. " .
                "See {$devDocs} for information about configuring cache key factors for a resolver."
            );
        } else {
            $runtimePoolKey = $this->generateKeyFromFactorProviders($factorProviders);
            if (!isset($this->keyCalculatorInstances[$runtimePoolKey])) {
                $this->keyCalculatorInstances[$runtimePoolKey] = $this->objectManager->create(
                    Calculator::class,
                    ['factorProviders' => $factorProviders]
                );
            }
            $this->keyCalculatorInstances[$resolverClass] = $this->keyCalculatorInstances[$runtimePoolKey];
        }
    }

    /**
     * Generate runtime pool key from the set of factor providers.
     *
     * @param array $factorProviders
     * @return string
     */
    private function generateKeyFromFactorProviders(array $factorProviders): string
    {
        if (empty($factorProviders)) {
            return '';
        }
        $keyArray = array_keys($factorProviders);
        sort($keyArray);
        return implode('_', $keyArray);
    }

    /**
     * @inheritDoc
     */
    public function getKeyCalculatorForResolver(ResolverInterface $resolver): Calculator
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
     * Get a list of cache key factor providers for the given resolver object.
     *
     * @param ResolverInterface $resolver
     * @return array|null
     */
    private function getFactorProvidersForResolver(ResolverInterface $resolver): ?array
    {
        $resultsToMerge = [];
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (isset($this->factorProviders[$resolverClass])
                && is_array($this->factorProviders[$resolverClass])
            ) {
                $resultsToMerge []= $this->factorProviders[$resolverClass];
            }
        }
        // avoid using array_merge in a loop
        return !empty($resultsToMerge) ? array_merge(...$resultsToMerge) : null;
    }
}
