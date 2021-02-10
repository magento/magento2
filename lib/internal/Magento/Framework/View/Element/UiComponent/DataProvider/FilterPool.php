<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

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
     * @param Collection $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria)
    {
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $filterApplier = $this->appliers[$filter->getConditionType()] ?? $this->appliers['regular'];
                $filterApplier->apply($collection, $filter);
            }
        }
    }
}
