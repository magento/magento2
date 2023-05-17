<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
