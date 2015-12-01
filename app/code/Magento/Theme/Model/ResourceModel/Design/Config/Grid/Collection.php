<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Design\Config\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Design config grid collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @inheritDoc
     */
    public function setItems(array $items = null)
    {
        // TODO: Implement setItems() method.
    }

    /**
     * @inheritDoc
     */
    public function getAggregations()
    {
        // TODO: Implement getAggregations() method.
    }

    /**
     * @inheritDoc
     */
    public function setAggregations($aggregations)
    {
        // TODO: Implement setAggregations() method.
    }

    /**
     * @inheritDoc
     */
    public function getSearchCriteria()
    {
        // TODO: Implement getSearchCriteria() method.
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement setSearchCriteria() method.
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        // TODO: Implement getTotalCount() method.
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        // TODO: Implement setTotalCount() method.
    }
}
