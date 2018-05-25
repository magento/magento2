<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

class DimensionProvider implements DimensionProviderInterface
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
    }

    public function getIterator(): \Traversable
    {
        $this->initDataIterators();
        $dimensionsCount = $this->count();

        for ($i = 0; $i < $dimensionsCount; $i++) {
            yield $this->getCurrentDimension();
            $this->setNextDimension();
        }

    }

    public function count(): int
    {
        if ($this->dimensionsCount === null) {
            $counts = [];

            foreach ($this->dimensionsDataProviders as $dimensionsDataProvider) {
                $counts[] = count($dimensionsDataProvider);
            }

            $this->dimensionsCount = count($counts) === 0 ? 0 : array_product($counts);
        }

        return $this->dimensionsCount;
    }

    private function getCurrentDimension(): array
    {
        $dimensions = [];

        foreach ($this->dimensionsIterators as $dimensionIterator) {
            /** @var Dimension $dimension */
            $dimension = $dimensionIterator->current();

            if (is_array($dimension)) {
                $dimension = $dimension[0];
            }

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

    private function initDataIterators()
    {
        foreach ($this->dimensionsDataProviders as $dimensionDataProvider) {
            $this->dimensionsIterators[] = $dimensionDataProvider->getIterator();
        }
    }
}
