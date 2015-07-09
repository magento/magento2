<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class Reporting
 */
class Reporting implements ReportingInterface
{
    /**
     * @var CollectionPool
     */
    protected $collectionPool;
    /**
     * @var array
     */
    protected $filterPool;

    /**
     * @param array $appliers
     */
    public function __construct(
        CollectionPool $collectionPool,
        FilterPool $filterPool
    ) {
        $this->collectionPool = $collectionPool;
        $this->filterPool = $filterPool;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionPool->getCollection($searchCriteria->getRequestName());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $this->filterPool->applyFilters($collection, $searchCriteria);
        return $collection;
    }
}