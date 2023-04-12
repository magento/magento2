<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCache\Model\Resolver\Cache;

use Magento\Framework\App\ObjectManager;

/**
 * Custom cache id calculator factory.
 */
class CacheIdCalculatorFactory
{
    /**
     * Create cache ID calculator instance with given cache id providers.
     *
     * @param array $resolverFactorIdProviders
     * @return CacheIdCalculator
     */
    public function create(array $customFactorProviders = []): CacheIdCalculator
    {
        if (empty($customFactorProviders)) {
            return ObjectManager::getInstance()->get(CacheIdCalculator::class);
        }
        /**
         * Returns cache id calculator with custom set of factor providers;
         */
        return ObjectManager::getInstance()->create(
            CacheIdCalculator::class,
            ['idFactorProviders' => $customFactorProviders]
        );
    }
}
