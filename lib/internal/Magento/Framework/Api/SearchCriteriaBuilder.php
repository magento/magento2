<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Search\FilterGroupBuilder;

/**
 * Builder for SearchCriteria Service Data Object
 * @since 2.0.0
 */
class SearchCriteriaBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var FilterGroupBuilder
     * @since 2.0.0
     */
    protected $_filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * @param ObjectFactory $objectFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @since 2.0.0
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        parent::__construct(
            $objectFactory
        );
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Builds the SearchCriteria Data Object
     *
     * @return SearchCriteria
     * @since 2.0.0
     */
    public function create()
    {
        //Initialize with empty array if not set
        if (empty($this->data[SearchCriteria::FILTER_GROUPS])) {
            $this->_set(SearchCriteria::FILTER_GROUPS, []);
        }
        return parent::create();
    }

    /**
     * Create a filter group based on the filter array provided and add to the filter groups
     *
     * @param \Magento\Framework\Api\Filter[] $filter
     * @return $this
     * @since 2.0.0
     */
    public function addFilters(array $filter)
    {
        $this->data[SearchCriteria::FILTER_GROUPS][] = $this->_filterGroupBuilder->setFilters($filter)->create();
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $conditionType
     * @return $this
     * @since 2.0.0
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
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     * @since 2.0.0
     */
    public function setFilterGroups(array $filterGroups)
    {
        return $this->_set(SearchCriteria::FILTER_GROUPS, $filterGroups);
    }

    /**
     * Add sort order
     *
     * @param SortOrder $sortOrder
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setSortOrders(array $sortOrders)
    {
        return $this->_set(SearchCriteria::SORT_ORDERS, $sortOrders);
    }

    /**
     * Set page size
     *
     * @param int $pageSize
     * @return $this
     * @since 2.0.0
     */
    public function setPageSize($pageSize)
    {
        return $this->_set(SearchCriteria::PAGE_SIZE, $pageSize);
    }

    /**
     * Set current page
     *
     * @param int $currentPage
     * @return $this
     * @since 2.0.0
     */
    public function setCurrentPage($currentPage)
    {
        return $this->_set(SearchCriteria::CURRENT_PAGE, $currentPage);
    }
}
