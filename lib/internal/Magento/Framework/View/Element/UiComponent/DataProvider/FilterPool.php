<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
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
     * @var FilterApplierInterface[]
     */
    protected $appliers;

    /**
     * @param FilterApplierInterface[] $appliers
     */
    public function __construct(array $appliers = [])
    {
        $this->appliers = $appliers;
    }

    /**
     * Apply filters from search criteria
     *
     * @param Collection|AbstractDb $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria)
    {
        $groupedParts = $collection->getSelect()->getPart(Select::WHERE);
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $filterParts = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $filterApplier = $this->appliers[$filter->getConditionType()] ?? $this->appliers['regular'];
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
     * Remove were join condition in the beginning of applied filter
     *
     * @param string $part
     * @return string
     */
    private function preparePart(string $part): string
    {
        return preg_replace('/^(' . Select::SQL_OR . '|' . Select::SQL_AND . ')\s+/i', '', trim($part), 1);
    }
}
