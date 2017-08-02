<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Class FilterPool
 *
 * @api
 * @since 2.0.0
 */
class FilterPool
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $appliers;

    /**
     * @param array $appliers
     * @since 2.0.0
     */
    public function __construct(array $appliers = [])
    {
        $this->appliers = $appliers;
    }

    /**
     * @param Collection $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     * @since 2.0.0
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria)
    {
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                /** @var $filterApplier FilterApplierInterface*/
                if (isset($this->appliers[$filter->getConditionType()])) {
                    $filterApplier = $this->appliers[$filter->getConditionType()];
                } else {
                    $filterApplier = $this->appliers['regular'];
                }
                $filterApplier->apply($collection, $filter);
            }
        }
    }
}
