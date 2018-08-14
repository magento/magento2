<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class JoinProcessor implements CollectionProcessorInterface
{
    /**
     * @var CustomJoinInterface[]
     */
    private $joins;

    /**
     * @var array
     */
    private $fieldMapping;

    /**
     * @var array
     */
    private $appliedFields = [];

    /**
     * @param CustomJoinInterface[] $customFilters
     * @param array $fieldMapping
     */
    public function __construct(
        array $customJoins = [],
        array $fieldMapping = []
    ) {
        $this->joins = $customJoins;
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * Apply Search Criteria Filters to collection only if we need this
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        if ($searchCriteria->getFilterGroups()) {
            //Process filters
            foreach ($searchCriteria->getFilterGroups() as $group) {
                foreach ($group->getFilters() as $filter) {
                    if (!isset($this->appliedFields[$filter->getField()])) {
                        $this->applyCustomJoin($filter->getField(), $collection);
                        $this->appliedFields[$filter->getField()] = true;
                    }
                }
            }
        }

        if ($searchCriteria->getSortOrders()) {
            // Process Sortings
            foreach ($searchCriteria->getSortOrders() as $order) {
                if (!isset($this->appliedFields[$order->getField()])) {
                    $this->applyCustomJoin($order->getField(), $collection);
                    $this->appliedFields[$order->getField()] = true;
                }
            }
        }
    }

    /**
     * Apply join to collection
     *
     * @param string $field
     * @param AbstractDb $collection
     * @return void
     */
    private function applyCustomJoin($field, AbstractDb $collection)
    {
        $field = $this->getFieldMapping($field);
        $customJoin = $this->getCustomJoin($field);

        if ($customJoin) {
            $customJoin->apply($collection);
        }
    }

    /**
     * Return custom filters for field if exists
     *
     * @param string $field
     * @return CustomJoinInterface|null
     * @throws \InvalidArgumentException
     */
    private function getCustomJoin($field)
    {
        $filter = null;
        if (isset($this->joins[$field])) {
            $filter = $this->joins[$field];
            if (!($this->joins[$field] instanceof CustomJoinInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Custom join for %s must implement %s interface.',
                        $field,
                        CustomJoinInterface::class
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
