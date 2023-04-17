<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\Framework\ObjectManagerInterface;

/**
 * Custom resolver cache id calculator factory.
 */
class KeyCalculatorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create cache ID calculator instance with given cache id providers.
     *
     * @param array $customFactorProviders
     * @return KeyCalculator
     */
    public function create(array $customFactorProviders = []): KeyCalculator
    {
        if (empty($customFactorProviders)) {
            return $this->objectManager->get(KeyCalculator::class);
        }
        /**
         * Returns cache id calculator with custom set of factor providers.
         */
        return $this->objectManager->create(
            KeyCalculator::class,
            ['idFactorProviders' => $customFactorProviders]
        );
    }
}
