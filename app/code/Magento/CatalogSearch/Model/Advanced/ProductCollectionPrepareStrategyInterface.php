<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Advanced;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Strategy interface for preparing product collection.
 *
 * @api
 */
interface ProductCollectionPrepareStrategyInterface
{
    /**
     * Prepare product collection.
     *
     * @param Collection $collection
     * @return void
     */
    public function prepare(Collection $collection);
}
