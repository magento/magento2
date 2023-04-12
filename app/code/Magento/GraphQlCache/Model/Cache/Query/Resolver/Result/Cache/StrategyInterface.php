<?php

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\Resolver\Cache\CacheIdCalculator;

interface StrategyInterface
{
    public function initForResolver(ResolverInterface $resolver): void;

    public function getCacheIdCalculatorForResolver(ResolverInterface $resolver): CacheIdCalculator;

    public function restateFromPreviousResolvedValues(ResolverInterface $resolverObject, ?array $result): void;

    public function restateFromContext(ContextInterface $context): void;

//    public function getCustomProvidersForResolverObject(ResolverInterface $resolver): array;
//
//    public function setCustomFactorProvidersForResolver(
//        string $resolverClass,
//        array $customFactorProviders = []
//    ): void;
}
