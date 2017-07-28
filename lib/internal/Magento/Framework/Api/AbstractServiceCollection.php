<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Base for service collections
 * @since 2.0.0
 */
abstract class AbstractServiceCollection extends \Magento\Framework\Data\Collection
{
    /**
     * Filters on specific fields
     *
     * Each filter has the following structure
     * <pre>
     * [
     *     'field'     => $field,
     *     'condition' => $condition,
     * ]
     * </pre>
     * @see addFieldToFilter() for more information on conditions
     *
     * @var array
     * @since 2.0.0
     */
    protected $fieldFilters = [];

    /**
     * @var FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     * @since 2.0.0
     */
    protected $sortOrderBuilder;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @since 2.0.0
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        parent::__construct($entityFactory);
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Add field filter to collection
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * <pre>
     * - ["from" => $fromValue, "to" => $toValue]
     * - ["eq" => $equalValue]
     * - ["neq" => $notEqualValue]
     * - ["like" => $likeValue]
     * - ["in" => [$inValues]]
     * - ["nin" => [$notInValues]]
     * - ["notnull" => $valueIsNotNull]
     * - ["null" => $valueIsNull]
     * - ["moreq" => $moreOrEqualValue]
     * - ["gt" => $greaterValue]
     * - ["lt" => $lessValue]
     * - ["gteq" => $greaterOrEqualValue]
     * - ["lteq" => $lessOrEqualValue]
     * - ["finset" => $valueInSet]
     * - ["regexp" => $regularExpression]
     * - ["seq" => $stringValue]
     * - ["sneq" => $stringValue]
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * or
     * ['rate', 'tax_postcode']
     * [['from'=>"3",'to'=>'8.25'], ['like' =>'%91000%']];
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws LocalizedException if some error in the input could be detected.
     * @return $this
     * @since 2.0.0
     */
    public function addFieldToFilter($field, $condition)
    {
        if (is_array($field) && is_array($condition) && count($field) != count($condition)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase('When passing in a field array there must be a matching condition array.')
            );
        } elseif (is_array($field) && !count($field) > 0) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    'When passing an array of fields there must be at least one field in the array.'
                )
            );
        }
        $this->processFilters($field, $condition);
        return $this;
    }

    /**
     * Pre-process filters to create multiple groups in case of multiple conditions eg: from & to
     * @param string|array $field
     * @param string|int|array $condition
     * @return $this
     * @since 2.0.0
     */
    private function processFilters($field, $condition)
    {
        //test if we have multiple conditions per field
        $requiresMultipleFilterGroups = false;
        if (is_array($field) && is_array($condition)) {
            foreach ($condition as $cond) {
                if (is_array($cond) && count($cond) > 1) {
                    $requiresMultipleFilterGroups = true;
                    break;
                }
            }
        } elseif (is_array($condition)) {
            $requiresMultipleFilterGroups = true;
        }

        if ($requiresMultipleFilterGroups) {
            $this->addFilterGroupsForMultipleConditions($field, $condition);
        } else {
            $this->addFilterGroupsForSingleConditions($field, $condition);
        }
        return $this;
    }

    /**
     * Return a single filter group in case of single conditions
     * @param string|array $field
     * @param string|int|array $condition
     * @return $this
     * @since 2.0.0
     */
    private function addFilterGroupsForSingleConditions($field, $condition)
    {
        $this->fieldFilters[] = ['field' => $field, 'condition' => $condition];
        return $this;
    }

    /**
     * Return multiple filters groups in case of multiple conditions eg: from & to
     * @param string|array $field
     * @param array $condition
     * @return $this
     * @since 2.0.0
     */
    private function addFilterGroupsForMultipleConditions($field, $condition)
    {
        if (!is_array($field) && is_array($condition)) {
            foreach ($condition as $key => $value) {
                $this->fieldFilters[] = ['field' => $field, 'condition' => [$key => $value]];
            }
        } else {
            $cnt = 0;
            foreach ($condition as $cond) {
                if (is_array($cond)) {
                    //we Do want multiple groups in this case
                    foreach ($cond as $condKey => $condValue) {
                        $this->fieldFilters[] = [
                            'field' => array_slice($field, $cnt, 1, true),
                            'condition' => [$condKey => $condValue]
                        ];
                    }
                } else {
                    $this->fieldFilters[] = ['field' => array_slice($field, $cnt, 1, true), 'condition' => $cond];
                }
                $cnt++;
            }
        }
        return $this;
    }

    /**
     * Creates a search criteria DTO based on the array of field filters.
     *
     * @return SearchCriteria
     * @since 2.0.0
     */
    protected function getSearchCriteria()
    {
        foreach ($this->fieldFilters as $filter) {
            // array of fields, put filters in array to use 'or' group
            /** @var Filter[] $filterGroup */
            $filterGroup = [];
            if (!is_array($filter['field'])) {
                // just one field
                $filterGroup = [$this->createFilterData($filter['field'], $filter['condition'])];
            } else {
                foreach ($filter['field'] as $index => $field) {
                    $filterGroup[] = $this->createFilterData($field, $filter['condition'][$index]);
                }
            }
            $this->searchCriteriaBuilder->addFilters($filterGroup);
        }
        foreach ($this->_orders as $field => $direction) {
            /** @var SortOrder $sortOrder */
            /** @var string $direction */
            $direction = ($direction == 'ASC') ? SortOrder::SORT_ASC : SortOrder::SORT_DESC;
            $sortOrder = $this->sortOrderBuilder->setField($field)->setDirection($direction)->create();
            $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        }
        $this->searchCriteriaBuilder->setCurrentPage($this->_curPage);
        $this->searchCriteriaBuilder->setPageSize($this->_pageSize);
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Creates a filter DTO for given field/condition
     *
     * @param string $field Field for new filter
     * @param string|array $condition Condition for new filter.
     * @return Filter
     * @since 2.0.0
     */
    protected function createFilterData($field, $condition)
    {
        $this->filterBuilder->setField($field);

        if (is_array($condition)) {
            $this->filterBuilder->setValue(reset($condition));
            $this->filterBuilder->setConditionType(key($condition));
        } else {
            // not an array, just use eq as condition type and given value
            $this->filterBuilder->setConditionType('eq');
            $this->filterBuilder->setValue($condition);
        }
        return $this->filterBuilder->create();
    }
}
