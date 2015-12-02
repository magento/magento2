<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;

class Interval implements IntervalInterface
{
    /**
     * Minimal possible value
     */
    const DELTA = 0.005;

    /**
     * @var array
     */
    protected $query;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @param array $query
     * @param ConnectionManager $connectionManager
     * @param string $fieldName
     */
    public function __construct(
        $query,
        ConnectionManager $connectionManager,
        $fieldName
    ) {
        $this->query = $query;
        $this->connectionManager = $connectionManager;
        $this->fieldName = $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        return [];
    }
}
