<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;

/**
 * Filter poll apply filters from search criteria
 *
 * @api
 * @since 100.0.2
 */
class FilterPool
{
    /**
     * @param FilterApplierInterface[] $appliers
     */
    public function __construct(protected array $appliers = [])
    {
    }

    /**
     * Apply filters from search criteria.
     *
     * @param Collection|AbstractDb $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria)
    {
        // MC-24195
        if (!$collection instanceof AbstractDb) {
            $this->applyFiltersToCollection($collection, $criteria);
            return;
        }

        $this->applyFiltersToDbCollection($collection, $criteria);
    }

    /**
     * Apply filters without touching Select (non-database collections).
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    private function applyFiltersToCollection(
        Collection $collection,
        SearchCriteriaInterface $criteria
    ): void {
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $this->getApplier($filter->getConditionType())->apply($collection, $filter);
            }
        }
    }

    /**
     * Apply filters and regroup WHERE so OR applies within each filter group.
     *
     * @param AbstractDb $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    private function applyFiltersToDbCollection(
        AbstractDb $collection,
        SearchCriteriaInterface $criteria
    ): void {
        $groupedParts = $collection->getSelect()->getPart(Select::WHERE);
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $filterParts = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $filterApplier = $this->getApplier($filter->getConditionType());
                $filterApplier->apply($collection, $filter);
                $whereParts = $collection->getSelect()->getPart(Select::WHERE);
                if (is_array($whereParts) && count($whereParts)) {
                    $appliedParts = array_diff($whereParts, $groupedParts);
                    foreach ($appliedParts as $part) {
                        $filterParts[] = $this->preparePart($part);
                    }
                }
                $collection->getSelect()->reset(Select::WHERE);
                $collection->getSelect()->setPart(Select::WHERE, $groupedParts);
            }
            if (count($filterParts)) {
                $resultCondition = '((' . implode(') ' . Select::SQL_OR . ' (', $filterParts) . '))';
                $groupedParts[] = (count($groupedParts) ? Select::SQL_AND : '') . ' ' . $resultCondition;
                $collection->getSelect()->setPart(Select::WHERE, $groupedParts);
            }
        }
        if (count($groupedParts)) {
            $collection->getSelect()->setPart(Select::WHERE, $groupedParts);
        }
    }

    /**
     * Resolve filter applier for the given condition type.
     *
     * @param string|null $conditionType
     * @return FilterApplierInterface
     */
    private function getApplier(?string $conditionType): FilterApplierInterface
    {
        return $this->appliers[$conditionType] ?? $this->appliers['regular'];
    }

    /**
     * Remove where join condition in the beginning of applied filter
     *
     * @param string $part
     * @return string
     */
    private function preparePart(string $part): string
    {
        return preg_replace('/^(' . Select::SQL_OR . '|' . Select::SQL_AND . ')\s+/i', '', trim($part), 1);
    }
}
