<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Correct batch size according to number of composite related items.
 * @api
 */
interface CompositeProductBatchSizeAdjusterInterface
{
    /**
     * Correct batch size according to number of composite related items.
     *
     * @param int $batchSize
     * @return int
     */
    public function adjust($batchSize);
}
