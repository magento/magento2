<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class Reporting
 * @since 2.0.0
 */
class Reporting implements ReportingInterface
{
    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $filterPool;

    /**
     * @param CollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @since 2.0.0
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
     * @since 2.0.0
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
