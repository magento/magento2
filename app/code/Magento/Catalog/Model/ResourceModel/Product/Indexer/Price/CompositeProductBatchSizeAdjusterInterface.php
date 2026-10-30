<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
