<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;

class SortingProcessor implements CollectionProcessorInterface
{
    /**
     * @var array
     */
    private $fieldMapping;

    /**
     * @var array
     */
    private $defaultOrders;

    /**
     * @param array $fieldMapping
     * @param array $defaultOrders
     */
    public function __construct(
        array $fieldMapping = [],
        array $defaultOrders = []
    ) {
        $this->fieldMapping = $fieldMapping;
        $this->defaultOrders = $defaultOrders;
    }

    /**
     * Apply Search Criteria Sorting Orders to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        if ($searchCriteria->getSortOrders()) {
            $this->applyOrders($searchCriteria->getSortOrders(), $collection);
        } elseif ($this->defaultOrders) {
            $this->applyDefaultOrders($collection);
        }
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
     * Apply sort orders to collection
     *
     * @param SortOrder[] $sortOrders
     * @param AbstractDb $collection
     * @return void
     */
    private function applyOrders(array $sortOrders, AbstractDb $collection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $this->getFieldMapping($sortOrder->getField());
            if (null !== $field) {
                $order = $sortOrder->getDirection() == SortOrder::SORT_ASC
                    ? Collection::SORT_ORDER_ASC
                    : Collection::SORT_ORDER_DESC;
                $collection->addOrder($field, $order);
            }
        }
    }

    /**
     * Apply default orders to collection
     *
     * @param AbstractDb $collection
     * @return void
     */
    private function applyDefaultOrders(AbstractDb $collection)
    {
        foreach ($this->defaultOrders as $field => $direction) {
            $field = $this->getFieldMapping($field);
            if (null !== $field) {
                $order = $direction == SortOrder::SORT_ASC
                    ? Collection::SORT_ORDER_ASC
                    : Collection::SORT_ORDER_DESC;
                $collection->addOrder($field, $order);
            }
        }
    }
}
