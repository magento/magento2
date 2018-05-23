<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Price;

interface MultiDimensionalIndexerInterface
{
    public function reindexAllWithinDimensions(array $dimensions);
    public function reindexEntityWithinDimensions(array $entityIds, array $dimensions);
}
