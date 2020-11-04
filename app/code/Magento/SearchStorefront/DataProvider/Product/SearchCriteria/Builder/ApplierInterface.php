<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria\Builder;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

/**
 * Interface ApplierInterface
 */
interface ApplierInterface
{
    /**
     * @param ProductSearchRequestInterface $request
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function apply(
        ProductSearchRequestInterface $request,
        SearchCriteriaInterface $searchCriteria
    ) : SearchCriteriaInterface;
}
