<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

class DimensionCollection implements \Iterator, \Countable
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
    private $dimensionsCount;

    /**
     * @param array $dimensionDataProviders
     */
    public function __construct(array $dimensionDataProviders = []) {
        foreach ($dimensionDataProviders as $dimensionDataProvider) {
            $this->addDimensionDataProvider($dimensionDataProvider);
        }

        $this->dimensionsCount = count($this->dimensionsIterators);
    }

    public function current()
    {
        $dimensions = [];

        foreach ($this->dimensionsIterators as $dimensionIterator) {
            /** @var Dimension $dimension */
            $dimension = $dimensionIterator->current();
            $dimensions[$dimension->getName()] = $dimension;
        }

        return $dimensions;
    }

    public function next()
    {
        $this->dimensionsIterators[$this->dimensionsCount - 1]->next();

        for ($i = ($this->dimensionsCount - 1); $i > 0; $i--) {
            if (!$this->dimensionsIterators[$i]->valid()) {
                $this->dimensionsIterators[$i] = $this->dimensionsDataProviders[$i]->getIterator();
                $this->dimensionsIterators[$i-1]->next();
            }
        }
    }

    public function key()
    {
        $keys = [];

        foreach ($this->dimensionsIterators as $dimensionIterator) {
            $keys[] = $dimensionIterator->key();
        }

        return implode(':', $keys);
    }

    public function valid()
    {
        return $this->dimensionsCount > 0 && $this->dimensionsIterators[0]->valid();
    }

    public function rewind()
    {
        $this->dimensionsIterators = [];
        foreach ($this->dimensionsDataProviders as $dimensionsDataProvider) {
            $this->dimensionsIterators[] = $dimensionsDataProvider->getIterator();
        }
    }

    public function count()
    {
        $counts = [];

        foreach ($this->dimensionsDataProviders as $dimensionsDataProvider) {
            $counts[] = count($dimensionsDataProvider);
        }

        return array_product($counts);
    }

    private function addDimensionDataProvider(DimensionProviderInterface $dimensionDataProvider)
    {
        $this->dimensionsDataProviders[] = $dimensionDataProvider;
        $this->dimensionsIterators[] = $dimensionDataProvider->getIterator();
    }
}
