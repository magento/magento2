<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria\Builder;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

/**
 * Class SortApplier
 */
class SortApplier implements ApplierInterface
{
    const DEFAULT_SORT_FIELD = 'relevance';

    /** @var \Magento\Framework\Api\SortOrderBuilder */
    private $sortOrderBuilder;

    /**
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
    ) {
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Apply sorting to search criteria.
     *
     * @param ProductSearchRequestInterface $request
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function apply(
        ProductSearchRequestInterface $request,
        SearchCriteriaInterface $searchCriteria
    ) : SearchCriteriaInterface {
        $sortOrders = $searchCriteria->getSortOrders() ?? [];

        foreach ($request->getSort() as $sort) {
            $sortOrders[] = $this->sortOrderBuilder->setField($sort->getAttribute())
                ->setDirection(strtolower($sort->getDirection()) == 'desc' ? SortOrder::SORT_DESC : SortOrder::SORT_ASC)
                ->create();
        }

        $sortOrders = $this->addDefaultSortOrder($sortOrders);
        $searchCriteria->setSortOrders($sortOrders);

        return $searchCriteria;
    }

    /**
     * If request has no sort orders - add default one.
     *
     * @param array $sortOrders
     * @return mixed
     */
    private function addDefaultSortOrder(array $sortOrders = []) : array
    {
        if (empty($sortOrders)) {
            $sortOrders[] = $this->sortOrderBuilder
                ->setField(self::DEFAULT_SORT_FIELD)
                ->setDirection(SortOrder::SORT_DESC)
                ->create();
        }

        return $sortOrders;
    }
}
