<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCache\Model\Resolver\Cache;

use Magento\Framework\App\ObjectManager;

/**
 * Custom resolver cache id calculator factory.
 */
class ResolverCacheIdCalculatorFactory
{
    /**
     * Create cache ID calculator instance with given cache id providers.
     *
     * @param array $resolverFactorIdProviders
     * @return ResolverCacheIdCalculator
     */
    public function create(array $customFactorProviders = []): ResolverCacheIdCalculator
    {
        if (empty($customFactorProviders)) {
            return ObjectManager::getInstance()->get(ResolverCacheIdCalculator::class);
        }
        /**
         * Returns cache id calculator with custom set of factor providers;
         */
        return ObjectManager::getInstance()->create(
            ResolverCacheIdCalculator::class,
            ['idFactorProviders' => $customFactorProviders]
        );
    }
}
