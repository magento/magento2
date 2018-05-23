<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\MultiDimensional;

class DimensionCollection implements \Iterator
{
    /**
     * @var array
     */
    private $dimensionsIterators = [];

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
                $this->dimensionsIterators[$i]->rewind();
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
        foreach ($this->dimensionsIterators as $dimensionIterator) {
            $dimensionIterator->rewind();
        }
    }

    private function addDimensionDataProvider(DimensionDataProviderInterface $dimensionDataProvider)
    {
        $this->dimensionsIterators[] = $dimensionDataProvider;
    }
}
