<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Class return price table name based on dimension
 * use only on the frontend area
 */
class PriceTableResolver implements IndexScopeResolverInterface
{
    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @var DimensionModeConfiguration
     */
    private $dimensionModeConfiguration;

    /**
     * @param IndexScopeResolver $indexScopeResolver
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     */
    public function __construct(
        IndexScopeResolver $indexScopeResolver,
        DimensionModeConfiguration $dimensionModeConfiguration
    ) {
        $this->indexScopeResolver = $indexScopeResolver;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
    }

    /**
     * Return price table name based on dimension
     * @param string $index
     * @param array $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions)
    {
        if ($index === 'catalog_product_index_price') {
            $dimensions = $this->filterDimensions($dimensions);
        }
        return $this->indexScopeResolver->resolve($index, $dimensions);
    }

    /**
     * @param Dimension[] $dimensions
     * @return array
     * @throws \Exception
     */
    private function filterDimensions($dimensions): array
    {
        $existDimensions = [];
        $currentDimensions = $this->dimensionModeConfiguration->getDimensionConfiguration();
        foreach ($dimensions as $dimension) {
            if ((string)$dimension->getValue() === '') {
                throw new \InvalidArgumentException(
                    sprintf('Dimension value of "%s" can not be empty', $dimension->getName())
                );
            }
            if (in_array($dimension->getName(), $currentDimensions, true)) {
                $existDimensions[] = $dimension;
            }
        }

        return $existDimensions;
    }
}
