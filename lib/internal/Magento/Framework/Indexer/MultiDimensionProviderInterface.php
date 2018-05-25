<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * Provide a list of multi dimensions.
 * Used for multiply several dimension and return array of dimensions during iteration
 */
interface MultiDimensionProviderInterface extends \IteratorAggregate
{
    /**
    * Returns [\Magento\Framework\Indexer\Dimension, ...] for each iteration
    * @return \Traversable|[\Magento\Framework\Indexer\Dimension,]
    */
    public function getIterator(): \Traversable;
}
