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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
