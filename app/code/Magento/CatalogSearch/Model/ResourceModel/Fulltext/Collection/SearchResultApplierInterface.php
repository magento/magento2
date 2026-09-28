<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve specific attributes for search criteria.
 *
 * @api
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
