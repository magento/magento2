<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\CombinedFilterGroup;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionProviderInterface;
use Magento\Framework\Phrase;

/**
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
 */
class AdvancedFilterProcessor implements CollectionProcessorInterface
{
    /**
     * @var CustomConditionProviderInterface
     */
    private $customConditionProvider;

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
     * @param CustomConditionProviderInterface $customConditionProvider
     */
    public function __construct(
        CustomConditionInterface $defaultConditionProcessor,
        ConditionManager $conditionManager,
        CustomConditionProviderInterface $customConditionProvider
    ) {
        $this->defaultConditionProcessor = $defaultConditionProcessor;
        $this->conditionManager = $conditionManager;
        $this->customConditionProvider = $customConditionProvider;
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
            $conditions = $this->getConditionsFromFilterGroup($group);
            $collection->getSelect()->where($conditions);
        }
    }

    /**
     * Add FilterGroup to the collection
     *
     * @param CombinedFilterGroup $filterGroup
     * @return string
     * @throws InputException
     */
    private function getConditionsFromFilterGroup(CombinedFilterGroup $filterGroup): string
    {
        $conditions = [];

        foreach ($filterGroup->getFilters() as $filter) {
            if ($filter instanceof CombinedFilterGroup) {
                $conditions[] = $this->getConditionsFromFilterGroup($filter);
                continue;
            }

            if ($filter instanceof Filter) {
                $conditions[] = $this->getConditionsFromFilter($filter);
                continue;
            }

            throw new InputException(
                new Phrase('Undefined filter group "%1" passed in.', [get_class($filter)])
            );
        }

        return $this->conditionManager->wrapBrackets(
            $this->conditionManager->combineQueries($conditions, $filterGroup->getCombinationMode())
        );
    }

    /**
     * @param Filter $filter
     * @return string
     * @throws InputException
     */
    private function getConditionsFromFilter(Filter $filter): string
    {
        if ($this->customConditionProvider->hasProcessorForField($filter->getField())) {
            $customProcessor = $this->customConditionProvider->getProcessorByField($filter->getField());
            return $customProcessor->build($filter);
        }

        return $this->defaultConditionProcessor->build($filter);
    }
}
