<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\CombinedFilterGroup;
use Magento\Framework\Api\FilterGroupInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionProcessorBuilderInterface;

/**
 * Class AdvancedFilterProcessor
 * Collection processor that adds filters to collection based on passed search criteria
 *
 * Difference between FilterProcessor is that AdvancedFilterProcessor gives ability
 * to add filters using different combination strategies
 *
 * For example you can add such filters:
 *
 * Select * FROM some_table
 * WHERE
 *  field_1 = 10
 *  AND (
 *      field_2 in (1,2,3)
 *      OR
 *      field_3 like '%banana%'
 *  )
 *
 * @package Magento\Framework\Api\SearchCriteria\CollectionProcessor
 */
class AdvancedFilterProcessor implements CollectionProcessorInterface
{
    /**
     * @var CustomConditionProcessorBuilderInterface
     */
    private $customConditionProcessorBuilder;

    /**
     * @var CustomConditionInterface
     */
    private $defaultConditionProcessor;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param CustomConditionInterface $defaultConditionProcessor
     * @param ConditionManager $conditionManager
     * @param CustomConditionProcessorBuilderInterface $customConditionProcessorBuilder
     */
    public function __construct(
        CustomConditionInterface $defaultConditionProcessor,
        ConditionManager $conditionManager,
        CustomConditionProcessorBuilderInterface $customConditionProcessorBuilder
    ) {
        $this->defaultConditionProcessor = $defaultConditionProcessor;
        $this->conditionManager = $conditionManager;
        $this->customConditionProcessorBuilder = $customConditionProcessorBuilder;
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
            $conditions = $this->getConditionsFromFilterGroup($group, $collection);
            $collection->getSelect()->where($conditions);
        }
    }

    /**
     * Add FilterGroup to the collection
     *
     * @param FilterGroupInterface $filterGroup
     * @param AbstractDb $collection
     * @return string
     * @throws InputException
     */
    private function getConditionsFromFilterGroup(FilterGroupInterface $filterGroup, AbstractDb $collection)
    {
        $conditions = [];

        foreach ($filterGroup->getFilters() as $filter) {
            if ($filter instanceof CombinedFilterGroup) {
                $conditions[] = $this->getConditionsFromFilterGroup($filter, $collection);
                continue;
            }

            if ($filter instanceof Filter) {
                $conditions[] = $this->getConditionsFromFilter($filter);
                continue;
            }

            throw new InputException(
                __(sprintf('Undefined filter group "%s" passed in.', get_class($filter)))
            );
        }

        return $this->conditionManager->wrapBrackets(
            $this->conditionManager->combineQueries($conditions, $filterGroup->getCombinationMode())
        );
    }

    /**
     * @param Filter $filter
     * @return mixed
     */
    private function getConditionsFromFilter(Filter $filter)
    {
        if ($this->customConditionProcessorBuilder->hasProcessorForField($filter->getField())) {
            $customProcessor = $this->customConditionProcessorBuilder->getProcessorByField($filter->getField());
            return $customProcessor->build($filter);
        }

        return $this->defaultConditionProcessor->build($filter);
    }
}
