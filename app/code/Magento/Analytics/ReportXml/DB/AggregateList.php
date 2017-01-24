<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB;

/**
 * Class AggregateList
 *
 * List of aggregated functions
 */
class AggregateList
{
    /**
     * @var array
     */
    private $aggregates = [
        'sum',
        'count',
        'min',
        'max',
        'avg',
        'group_concat'
    ];

    /**
     * AggregateList constructor.
     *
     * @param array $aggregates
     */
    public function __construct(
        $aggregates = []
    ) {
        $this->aggregates = array_merge($this->aggregates, $aggregates);
    }

    /**
     * Returns list of functions
     *
     * @return string[]
     */
    public function getList()
    {
        return array_keys($this->aggregates);
    }
}
