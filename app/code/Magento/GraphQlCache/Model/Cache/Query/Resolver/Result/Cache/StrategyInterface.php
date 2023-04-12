<?php

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;

interface StrategyInterface
{
    public function initForResolver(ResolverInterface $resolver): void;

    public function getForResolver(ResolverInterface $resolver): CacheIdCalculator;

    public function restateFromResolverResult(array $result): void;

    public function restateFromContext(ContextInterface $context): void;

//    public function getCustomProvidersForResolverObject(ResolverInterface $resolver): array;
//
//    public function setCustomFactorProvidersForResolver(
//        string $resolverClass,
//        array $customFactorProviders = []
//    ): void;
}
