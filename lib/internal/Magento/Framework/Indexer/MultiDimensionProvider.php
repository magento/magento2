<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    }

    /**
     * Returns generator that will return multiplied dimensions on each iteration
     *
     * @return \Traversable|Dimension[][]
     * @throws \LogicException
     */
    public function getIterator(): \Traversable
    {
        // just return empty array if we have no dimension providers to iterate over
        if ($this->dimensionsProvidersCount === 0) {
            yield [];
            return;
        }

        // this recreates iterators for dimension so we can iterate over them
        $this->rewind();

        // if at leas one dimension provider has no dimensions to return we can't multiple dimension at all
        if (!$this->hasCurrentDimension()) {
            throw new \LogicException('Can`t multiple dimensions because some of them are empty.');
        }

        // return dimensions until all iterators become invalid
        while ($this->hasCurrentDimension()) {
            yield $this->getCurrentDimension();
            $this->setNextDimension();
        }
    }

    /**
     * Return all dimensions for current state of each dimension provider
     *
     * @return array
     */
    private function getCurrentDimension(): array
    {
        $dimensions = [];

        foreach ($this->dimensionsIterators as $dimensionIterator) {
            /** @var Dimension $dimension */
            $dimension = $dimensionIterator->current();
            $dimensions[$dimension->getName()] = $dimension;
        }

        return $dimensions;
    }

    /**
     * Iterates over dimension iterators one by one starting from right to left
     * This approach emulates iterations over X nested foreach loops e.g.:
     *
     * @return void
     */
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

    /**
     * Recreates iterators so all MultiDimensionProvider can be iterated again
     *
     * @return void
     */
    private function rewind()
    {
        $this->dimensionsIterators = [];

        foreach ($this->dimensionsDataProviders as $dimensionDataProvider) {
            $this->dimensionsIterators[] = $dimensionDataProvider->getIterator();
        }
    }

    /**
     * Check if all dimension iterators are in valid state
     *
     * If at least one of dimension iterators is invalid before very first iteration - we assume
     * that dimension provider has no dimensions at all, which means we can't multiple all dimensions
     *
     * If all dimension iterators became invalid - we assume that multiplication is already done
     *
     * @return bool
     */
    private function hasCurrentDimension(): bool
    {
        $valid = true;

        foreach ($this->dimensionsIterators as $dimensionsIterator) {
            // if at least one data provider is invalid at this stage - all generator is invalid
            if (!$dimensionsIterator->valid()) {
                return false;
            }
        }

        // generator is valid only when all data providers are valid
        return $valid;
    }

    /**
     * Collects dimension data providers
     * This was done via separate method to ensure that each provider has required interface
     *
     * @param DimensionProviderInterface $dimensionDataProvider
     * @return void
     */
    private function addDimensionDataProvider(DimensionProviderInterface $dimensionDataProvider)
    {
        $this->dimensionsDataProviders[] = $dimensionDataProvider;
        $this->dimensionsProvidersCount++;
    }
}
