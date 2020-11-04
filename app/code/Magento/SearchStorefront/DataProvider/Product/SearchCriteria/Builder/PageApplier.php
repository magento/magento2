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
 * Class PageApplier
 */
class PageApplier implements ApplierInterface
{
    const DEFAULT_PAGE_NUM = 0;
    const DEFAULT_PAGE_SIZE = 20;

    /**
     * Apply page size and current page to search criteria.
     *
     * @param ProductSearchRequestInterface $request
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function apply(
        ProductSearchRequestInterface $request,
        SearchCriteriaInterface $searchCriteria
    ) : SearchCriteriaInterface {
        $pageNum = self::DEFAULT_PAGE_NUM;
        $pageSize = self::DEFAULT_PAGE_SIZE;

        if ($request->getCurrentPage() && $request->getCurrentPage() > 0) {
            $pageNum = $request->getCurrentPage() - 1;
        }
        if ($request->getPageSize() && $request->getPageSize() > 0) {
            $pageSize = $request->getPageSize();
        }

        $searchCriteria->setCurrentPage($pageNum);
        $searchCriteria->setPageSize($pageSize);

        return $searchCriteria;
    }
}
