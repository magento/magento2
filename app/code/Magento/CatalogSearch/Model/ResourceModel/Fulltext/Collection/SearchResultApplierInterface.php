<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve specific attributes for search criteria.
 */
interface SearchResultApplierInterface
{
    /**
     * Apply search results to collection.
     *
     * @return void
     */
    public function apply();
}
