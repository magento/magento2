<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SimpleBuilderInterface;

/**
 * Builder for SearchCriteria Service Data Object
 *
 * @api
 */
class SearchCriteriaBuilder implements SimpleBuilderInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @param ObjectFactory $objectFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Builds the SearchCriteria Data Object
     *
     * @return SearchCriteria
     */
    public function create()
    {
        //Initialize with empty array if not set
        if (empty($this->data[SearchCriteria::FILTER_GROUPS])) {
            $this->set(SearchCriteria::FILTER_GROUPS, []);
        }

        $dataObjectType = $this->getDataObjectType();
        $dataObject = $this->objectFactory->create($dataObjectType, ['data' => $this->data]);
        $this->data = [];

        return $dataObject;
    }

    /**
     * Return the Data type class name
     *
     * @return string
     */
    private function getDataObjectType()
    {
        $dataObjectType = '';
        $pattern = '/(?<data_object>.*?)Builder(\\Interceptor)?/';
        if (preg_match($pattern, get_class($this), $match)) {
            $dataObjectType = $match['data_object'];
        }

        return $dataObjectType;
    }

    /**
     * Return data Object data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Create a filter group based on the filter array provided and add to the filter groups
     *
     * @param Filter[] $filter
     * @return $this
     */
    public function addFilters(array $filter)
    {
        $this->data[SearchCriteria::FILTER_GROUPS][] = $this->filterGroupBuilder->setFilters($filter)->create();
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $conditionType
     * @return $this
     */
    public function addFilter($field, $value, $conditionType = 'eq')
    {
        $this->addFilters([
            $this->filterBuilder->setField($field)
                ->setValue($value)
                ->setConditionType($conditionType)
                ->create()
        ]);
        return $this;
    }

    /**
     * Set filter groups
     *
     * @param FilterGroup[] $filterGroups
     * @return $this
     */
    public function setFilterGroups(array $filterGroups)
    {
        return $this->set(SearchCriteria::FILTER_GROUPS, $filterGroups);
    }

    /**
     * Add sort order
     *
     * @param SortOrder $sortOrder
     * @return $this
     */
    public function addSortOrder($sortOrder)
    {
        if (!isset($this->data[SearchCriteria::SORT_ORDERS])) {
            $this->data[SearchCriteria::SORT_ORDERS] = [];
        }
        $this->data[SearchCriteria::SORT_ORDERS][] = $sortOrder;
        return $this;
    }

    /**
     * Set sort orders
     *
     * @param SortOrder[] $sortOrders
     * @return $this
     */
    public function setSortOrders(array $sortOrders)
    {
        return $this->set(SearchCriteria::SORT_ORDERS, $sortOrders);
    }

    /**
     * Set page size
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        return $this->set(SearchCriteria::PAGE_SIZE, $pageSize);
    }

    /**
     * Set current page
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage)
    {
        return $this->set(SearchCriteria::CURRENT_PAGE, $currentPage);
    }

    /**
     * Overwrite data in Object.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    private function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }
}
