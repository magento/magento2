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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        AbstractDb $collection
    ) {
        $fields = [];
        $customFilters = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $isApplied = false;
            $applyLater = false;
            $customFilter = $this->getCustomFilterForField($filter->getField());
            if ($customFilter) {
                if ($filter->getConditionType() === 'eq') {
                    $customFilters = array_map(
                        function ($customFilter) {
                            if (count($values = $customFilter['values']) > 1) {
                                $filter = reset($customFilter['filter']);
                                $filter->setValue(implode(',', $values));
                                $filter->setConditionType('in');
                                $customFilter['filter'] = $filter;
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

            if (!$isApplied && !$applyLater) {
                $field = $this->getFieldMapping($filter->getField());
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = ['attribute' => $field, $condition => $filter->getValue()];
            }
        }

        if ($applyLater && count($customFilters)) {
            foreach ($customFilters as $field => $filter) {
                $customFilter = $this->getCustomFilterForField($field);
                /** @var Filter $filter */
                $filter = $filter['filter'];
                $customFilter->apply($filter, $collection);
            }
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
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
}
