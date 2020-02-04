<?php

namespace Alexx\Blog\Model\ResourceModel\BlogPosts\Grid;

use Alexx\Blog\Model\ResourceModel\BlogPosts\Collection as GridCollection;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Alexx\Blog\Model\ResourceModel\BlogPosts;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * BlogPosts GridCollection ResourceModel
 */
class Collection extends GridCollection implements SearchResultInterface
{
    private $_aggregations;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(Document::class, BlogPosts::class);
    }

    /**
     * GetAggregations
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * SetAggregations
     *
     * @param Magento\Framework\Api\Search\AggregationInterface $aggregations
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * GetAllIds
     *
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * GetSearchCriteria
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * SetSearchCriteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return Collection
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * GetTotalCount
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * SetTotalCount
     *
     * @param integer $totalCount
     * @return Collection
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * SetItems
     *
     * @param array $items
     * @return Collection
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
