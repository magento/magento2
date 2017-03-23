<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $filterPool;

    /**
     * @param CollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        FilterPool $filterPool
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filterPool = $filterPool;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->getReport($searchCriteria->getRequestName());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $this->filterPool->applyFilters($collection, $searchCriteria);
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            if ($sortOrder->getField()) {
                $collection->setOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        return $collection;
    }
}
