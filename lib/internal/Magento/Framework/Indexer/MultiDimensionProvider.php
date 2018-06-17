<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Multiply dimensions from provided DimensionProviderInterface
 */
class MultiDimensionProvider implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $dimensionsIterators = [];

    /**
     * @var array
     */
    private $dimensionsDataProviders = [];

    /**
     * @var int
     */
    private $dimensionsProvidersCount = 0;

    /**
     * @param DimensionProviderInterface[] $dimensionProviders
     */
    public function __construct(array $dimensionProviders = [])
    {
        foreach ($dimensionProviders as $dimensionDataProvider) {
            $this->addDimensionDataProvider($dimensionDataProvider);
        }
        foreach ($this->dimensionsDataProviders as $dimensionDataProvider) {
            $this->dimensionsIterators[] = $dimensionDataProvider->getIterator();
        }
    }

    /**
     * @return \Traversable|Dimension[][]
     */
    public function getIterator(): \Traversable
    {
        if (!$this->dimensionsIterators) {
            yield [];
        } else {
            while (true) {
                $dimensions = $this->getCurrentDimension();
                if (!$dimensions) {
                    break;
                }
                yield $dimensions;
                $this->setNextDimension();
            }
        }
    }

    private function getCurrentDimension(): array
    {
        $dimensions = [];
        foreach ($this->dimensionsIterators as $dimensionIterator) {
            if (!$dimensionIterator->valid()) {
                return [];
            }
            /** @var Dimension $dimension */
            $dimension = $dimensionIterator->current();
            $dimensions[$dimension->getName()] = $dimension;
        }

        return $dimensions;
    }

    private function setNextDimension()
    {
        $this->dimensionsIterators[$this->dimensionsProvidersCount - 1]->next();

        for ($i = ($this->dimensionsProvidersCount - 1); $i > 0; $i--) {
            if (!$this->dimensionsIterators[$i]->valid()) {
                $this->dimensionsIterators[$i] = $this->dimensionsDataProviders[$i]->getIterator();
                $this->dimensionsIterators[$i-1]->next();
            }
        }
    }

    private function addDimensionDataProvider(DimensionProviderInterface $dimensionDataProvider)
    {
        $this->dimensionsDataProviders[] = $dimensionDataProvider;
        $this->dimensionsProvidersCount++;
    }
}
