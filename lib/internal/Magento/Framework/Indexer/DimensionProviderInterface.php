<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
<<<<<<< HEAD
=======
 * @api
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 * Provide a list of dimensions
 */
interface DimensionProviderInterface extends \IteratorAggregate
{
    /**
     * Get Dimension Iterator. Returns yielded value of \Magento\Framework\Indexer\Dimension
     * @return \Traversable|\Magento\Framework\Indexer\Dimension[]
     */
    public function getIterator(): \Traversable;
}
