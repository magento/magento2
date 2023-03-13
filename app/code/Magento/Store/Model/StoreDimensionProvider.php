<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Traversable;

/**
 * Provide a list of stores as Dimension
 */
class StoreDimensionProvider implements DimensionProviderInterface
{
    /**
     * Hold the name of Store dimension. Uses for retrieve dimension value.
     * Used "scope" name for support current indexer implementation
     */
    const DIMENSION_NAME = 'scope';

    /**
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly DimensionFactory $dimensionFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        foreach (array_keys($this->storeManager->getStores()) as $storeId) {
            yield [self::DIMENSION_NAME => $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$storeId)];
        }
    }
}
