<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
