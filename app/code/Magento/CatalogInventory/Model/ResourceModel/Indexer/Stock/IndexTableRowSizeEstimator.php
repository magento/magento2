<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

class IndexTableRowSizeEstimator implements \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface
{
    /**
     * @inheritdoc
     */
    public function estimateRowSize()
    {
        return 100;
    }
}
