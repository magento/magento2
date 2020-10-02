<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Zend_Db_Select_Exception;

/**
 *  SearchCriteria FilterProcessor
 */
class FilterProcessor implements CollectionProcessorInterface
{
    /**
     * @var CustomFilterInterface[]
     */
    private $customFilters;

    /**
     * @var array
     */
    private $fieldMapping;

    /**
     * @param CustomFilterInterface[] $customFilters
     * @param array $fieldMapping
     */
    public function __construct(
        array $customFilters = [],
        array $fieldMapping = []
    ) {
        $this->customFilters = $customFilters;
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * Apply Search Criteria Filters to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
    }

    /**
     * Add FilterGroup to the collection
     *
     * @param FilterGroup $filterGroup
     * @param AbstractDb $collection
     * @return void
     * @throws Zend_Db_Select_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        AbstractDb $collection
    ) {
        $fields = [];
        $customFilters = [];
        $applyLater = false;
        foreach ($filterGroup->getFilters() as $filter) {
            $isApplied = false;
            $customFilter = $this->getCustomFilterForField($filter->getField());
            if ($customFilter) {
                if ($filter->getConditionType() === 'eq') {
                    $customFilters = array_map(
                        function ($customFilter) {
                            if (count($values = $customFilter['values']) > 1) {
                                $filter = reset($customFilter['filter']);
                                $filter->setValue(implode(',', $values));
                                $filter->setConditionType('in');
                                $customFilter['filter'] = [$filter];
                            }

                            return $customFilter;
                        },
                        array_merge_recursive(
                            $customFilters,
                            [$filter->getField() => [
                                'filter' => [clone $filter],
                                'values' => [$filter->getValue()]
                            ]]
                        )
                    );

                    $applyLater = true;
                }

                if (!$applyLater) {
                    $isApplied = $customFilter->apply($filter, $collection);
                }
            }

            if (!$isApplied && !$customFilter) {
                $field = $this->getFieldMapping($filter->getField());
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = ['attribute' => $field, $condition => $filter->getValue()];
            }
        }

        $whereParts = $collection->getSelect()->getPart(Select::WHERE);
        $whereSql = $this->applyFilters($collection, $fields, $customFilters);
        $collection->getSelect()->setPart(Select::WHERE, $whereParts);
        $collection->getSelect()->where($whereSql);
    }

    /**
     * Apply filters and retrieve `where` conditions
     *
     * @param AbstractDb $collection
     * @param array $fields
     * @param array $customFilters
     * @return string
     * @throws Zend_Db_Select_Exception
     */
    private function applyFilters(
        AbstractDb $collection,
        array $fields,
        array $customFilters
    ): string {
        $select = $collection->getSelect();
        $select->reset(Select::WHERE);
        $whereParts = [];
        if (count($fields)) {
            $collection->addFieldToFilter($fields);
            $whereParts[] = $select->getPart(Select::WHERE)[0];
            $select->reset(Select::WHERE);
        }

        if (count($customFilters)) {
            foreach ($customFilters as $field => $filter) {
                $customFilter = $this->getCustomFilterForField($field);
                /** @var Filter $filter */
                $filter = reset($filter['filter']);
                $customFilter->apply($filter, $collection);
                $whereCondition = $select->getPart(Select::WHERE);
                if (is_array($whereCondition) && count($whereCondition)) {
                    $whereParts[] = $whereCondition[0];
                    $select->reset(Select::WHERE);
                }
            }
        }
        $resultCondition = '';

        if (count($whereParts)) {
            $resultCondition = '(' . implode(') ' . Select::SQL_OR . ' (', $whereParts) . ')';
        }

        return $resultCondition;
    }

    /**
     * Return custom filters for field if exists
     *
     * @param string $field
     * @return CustomFilterInterface|null
     * @throws \InvalidArgumentException
     */
    private function getCustomFilterForField($field)
    {
        $filter = null;
        if (isset($this->customFilters[$field])) {
            $filter = $this->customFilters[$field];
            if (!($this->customFilters[$field] instanceof CustomFilterInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Filter for %s must implement %s interface.',
                        $field,
                        CustomFilterInterface::class
                    )
                );
            }
        }
        return $filter;
    }

    /**
     * Return mapped field name
     *
     * @param string $field
     * @return string
     */
    private function getFieldMapping($field)
    {
        return $this->fieldMapping[$field] ?? $field;
    }
}
