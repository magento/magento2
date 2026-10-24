<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

use Magento\Framework\Api\Search\SearchCriteria;

/**
 * Resolve specific attributes for search criteria.
 *
 * @api
 */
interface SearchCriteriaResolverInterface
{
    /**
     * Resolve specific attribute.
     *
     * @return SearchCriteria
     */
    public function resolve(): SearchCriteria;
}
