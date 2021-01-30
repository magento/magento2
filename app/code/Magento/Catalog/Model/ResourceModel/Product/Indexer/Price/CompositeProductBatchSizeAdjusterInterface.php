<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Correct batch size according to number of composite related items.
 * @api
 * @since 102.0.0
 */
interface CompositeProductBatchSizeAdjusterInterface
{
    /**
     * Correct batch size according to number of composite related items.
     *
     * @param int $batchSize
     * @return int
     * @since 102.0.0
     */
    public function adjust($batchSize);
}
