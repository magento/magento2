<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * @api
 * Provide a list of dimensions
 * @since 101.0.6
 */
interface DimensionProviderInterface extends \IteratorAggregate
{
    /**
     * Get Dimension Iterator. Returns yielded value of \Magento\Framework\Indexer\Dimension
     * @return \Traversable|\Magento\Framework\Indexer\Dimension[]
     * @since 101.0.6
     */
    public function getIterator(): \Traversable;
}
