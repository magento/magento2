<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

/**
 * Build search criteria from search request
 */
interface SearchCriteriaBuilderInterface
{
    /**
     * Build search criteria from search service request.
     *
     * @param ProductSearchRequestInterface $request
     * @return SearchCriteriaInterface
     */
    public function build(ProductSearchRequestInterface $request): SearchCriteriaInterface;
}
