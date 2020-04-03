<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Advanced;

use Magento\Framework\Search\EngineResolverInterface;

/**
 * Strategy provider for preparing product collection.
 */
class ProductCollectionPrepareStrategyProvider
{
    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @var array
     */
    private $strategies;

    /**
     * @param EngineResolverInterface $engineResolver
     * @param array $strategies
     */
    public function __construct(
        EngineResolverInterface $engineResolver,
        array $strategies
    ) {
        $this->engineResolver = $engineResolver;
        $this->strategies = $strategies;
    }

    /**
     * Get strategy provider for product collection prepare process.
     *
     * @return ProductCollectionPrepareStrategyInterface
     * @throws \Exception
     */
    public function getStrategy(): ProductCollectionPrepareStrategyInterface
    {
        if (!isset($this->strategies[$this->engineResolver->getCurrentSearchEngine()])) {
            if ($this->strategies['default']) {
                return $this->strategies['default'];
            } else {
                throw new \Exception('Default product collection strategy not found');
            }
        }
        return $this->strategies[$this->engineResolver->getCurrentSearchEngine()];
    }
}
