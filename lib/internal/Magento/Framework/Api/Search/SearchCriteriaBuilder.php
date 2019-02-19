<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Builder for SearchCriteria Service Data Object
 *
 * @api
 */
class SearchCriteriaBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @param ObjectFactory $objectFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        parent::__construct($objectFactory);
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Builds the SearchCriteria Data Object
     *
     * @return SearchCriteria
     */
    public function create()
    {
        foreach ($this->filters as $filter) {
            $this->data[SearchCriteria::FILTER_GROUPS][] = $this->filterGroupBuilder->setFilters([])
                ->addFilter($filter)
                ->create();
        }
        $this->data[SearchCriteria::SORT_ORDERS] = [$this->sortOrderBuilder->create()];
        return parent::create();
    }

    /**
     * Create a filter group based on the filter array provided and add to the filter groups
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function addSortOrder($field, $direction)
    {
        $this->sortOrderBuilder->setDirection($direction)
            ->setField($field);
        return $this;
    }

    /**
     * Set page size
     *
     * @param int $pageSize
     * @return $this
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
     */
    public function setCurrentPage($currentPage)
    {
        return $this->_set(SearchCriteria::CURRENT_PAGE, $currentPage);
    }
}
