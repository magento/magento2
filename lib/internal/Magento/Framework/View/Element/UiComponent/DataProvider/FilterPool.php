<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class FilterPool
 */
class FilterPool
{
    /**
     * @var array
     */
    protected $filters = [];

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
     * @param string|int|array|null $condition
     * @param string|null|array $field
     * @param string $type
     * @return void
     */
    public function registerNewFilter($condition, $field, $type)
    {
        $this->filters[$type][sha1($field . serialize($condition))] = [
            'field' => $field,
            'condition' => $condition
        ];
    }

    /**
     * @param AbstractDb $collection
     * @return void
     */
    public function applyFilters(AbstractDb $collection)
    {
        foreach ($this->filters as $type => $filter) {
            if (isset($this->appliers[$type])) {
                /** @var $filterApplier FilterApplierInterface*/
                $filterApplier = $this->appliers[$type];
                $filterApplier->apply($collection, $filter);
            }
        }
    }
}
