<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Class FilterPool
 */
class FilterPool
{
    /**
     * @var array
     */
    protected $appliers;

    /**
     * @param array $appliers
     */
    public function __construct(array $appliers = [])
    {
        $this->appliers = $appliers;
    }

    /**
     * @param AbstractDb $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function applyFilters(AbstractDb $collection, SearchCriteriaInterface $criteria)
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
