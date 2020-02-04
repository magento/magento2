<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\Helper;

use Magento\Framework\Api\Filter as Item;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Generate, add, and remove filters and their groups to and from a given Search Criteria object in a simple way.
 */
class Filter
{
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(FilterGroupBuilder $filterGroupBuilder, FilterBuilder $filterBuilder)
    {
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Generate filter based off given field name, condition type, and value
     *
     * @param string $field
     * @param string $condition
     * @param string|array $value
     * @return Item
     */
    public function generate(string $field, string $condition, $value) : Item
    {
        $this->filterBuilder->setField($field);
        $this->filterBuilder->setConditionType($condition);
        $this->filterBuilder->setValue($value);

        return $this->filterBuilder->create();
    }

    /**
     * Add a filter to a newly created filter group on a search criteria object
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Item $filter
     * @return SearchCriteriaInterface
     */
    public function add(SearchCriteriaInterface $searchCriteria, Item $filter) : SearchCriteriaInterface
    {
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->addFilter($filter)->create();
        $searchCriteria->setFilterGroups($filterGroups);

        return $searchCriteria;
    }

    /**
     * Remove a filter and its enclosing group when its name matches the given string
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $filterName
     * @return SearchCriteriaInterface
     */
    public function remove(SearchCriteriaInterface $searchCriteria, string $filterName) : SearchCriteriaInterface
    {
        $filterGroups = [];
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $isMatch = false;
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == $filterName) {
                    $isMatch = true;
                }
            }
            if ($isMatch) {
                continue;
            }

            $filterGroups[] = $filterGroup;
        }
        $searchCriteria->setFilterGroups($filterGroups);

        return $searchCriteria;
    }
}
