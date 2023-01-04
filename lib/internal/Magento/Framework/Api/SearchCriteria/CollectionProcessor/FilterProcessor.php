<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

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
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        AbstractDb $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $isApplied = false;
            $customFilter = $this->getCustomFilterForField($filter->getField());
            if ($customFilter) {
                $isApplied = $customFilter->apply($filter, $collection);
            }

            if (!$isApplied) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $this->getFieldMapping($filter->getField());

                if ($condition === 'fulltext') {
                    // NOTE: This is not a fulltext search, but the best way to search something when
                    // a SearchCriteria with "fulltext" condition is provided over a MySQL table
                    // (see https://github.com/magento-engcom/msi/issues/1221)
                    $condition = 'like';
                    $filter->setValue('%' . $filter->getValue() . '%');
                }

                $conditions[] = [$condition => $filter->getValue()];
            }
        }

        $this->checkFromTo($fields, $conditions);

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
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

    /**
     * Check filtergoup for type from & to
     *
     * @param string[] $fields
     * @param array<string[]> $conditions
     * @return void
     */
    private function checkFromTo(&$fields, &$conditions)
    {
        $_fields = array_unique($fields);
        $_conditions = [];
        foreach ($conditions as $condition) {
            $_conditions[array_key_first($condition)] = reset($condition);
        }
        if ((count($_fields) == 1) && (count($_conditions) == 2)
            && isset($_conditions['from']) && isset($_conditions['to'])
        ) {
            $fields = $_fields;
            $conditions = [$_conditions];
        }
    }
}
