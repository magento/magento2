<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Product filter interface
 *
 * @api
 */
interface ProductFilterInterface
{
    /**
     * Filter provided product collection
     *
     * @param Collection $collection
     * @param array $filters
     * @return Collection
     */
    public function filter(Collection $collection, array $filters): Collection;
}
