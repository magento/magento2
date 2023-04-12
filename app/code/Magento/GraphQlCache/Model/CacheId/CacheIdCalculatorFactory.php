<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCache\Model\CacheId;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ConfigInterface;

/**
 * Custom cache id calculator factory.
 */
class CacheIdCalculatorFactory
{
    private ObjectManager $objectManager;

    public function __construct() {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * Create cache ID calculator instance with given cache id providers.
     *
     * @param array $resolverFactorIdProviders
     * @return CacheIdCalculator
     */
    public function create(array $customFactorProviders = []): CacheIdCalculator
    {
        /**
         * Returns cache id calculator with custom set of factor providers;
         */
        return $this->objectManager->create(
            CacheIdCalculator::class,
            ['idFactorProviders' => $customFactorProviders]
        );
    }
}
