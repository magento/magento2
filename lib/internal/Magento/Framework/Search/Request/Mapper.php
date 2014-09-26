<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Request;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Search\Request\Query\Filter;

class Mapper
{
    /**
     * @var array
     */
    private $queries;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var string[]
     */
    private $mappedQueries;

    /**
     * @var string[]
     */
    private $mappedFilters;

    /**
     * @var array
     */
    private $aggregations;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * @var QueryInterface
     */
    private $rootQuery = null;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param array $queries
     * @param string $rootQueryName
     * @param array $aggregations
     * @param array $filters
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        array $queries,
        $rootQueryName,
        array $aggregations = [],
        array $filters = []
    ) {
        $this->objectManager = $objectManager;
        $this->queries = $queries;
        $this->aggregations = $aggregations;
        $this->filters = $filters;

        $this->rootQuery = $this->get($rootQueryName);
    }

    /**
     * Get Query Interface by name
     *
     * @param string $queryName
     * @return QueryInterface
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     */
    private function get($queryName)
    {
        $this->mappedQueries = [];
        $this->mappedFilters = [];
        $query = $this->mapQuery($queryName);
        $this->validate();
        return $query;
    }

    /**
     * Convert array to Query instance
     *
     * @param string $queryName
     * @return QueryInterface
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     */
    private function mapQuery($queryName)
    {
        if (!isset($this->queries[$queryName])) {
            throw new \Exception('Query ' . $queryName . ' does not exist');
        } elseif (in_array($queryName, $this->mappedQueries)) {
            throw new StateException('Cycle found. Query %1 already used in request hierarchy', [$queryName]);
        }
        $this->mappedQueries[] = $queryName;
        $query = $this->queries[$queryName];
        switch ($query['type']) {
            case QueryInterface::TYPE_MATCH:
                $query = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Query\Match',
                    [
                        'name' => $query['name'],
                        'value' => $query['value'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'matches' => $query['match']
                    ]
                );
                break;
            case QueryInterface::TYPE_FILTER:
                if (isset($query['queryReference'][0])) {
                    $reference = $this->mapQuery($query['queryReference'][0]['ref']);
                    $referenceType = Filter::REFERENCE_QUERY;
                } elseif (isset($query['filterReference'][0])) {
                    $reference = $this->mapFilter($query['filterReference'][0]['ref']);
                    $referenceType = Filter::REFERENCE_FILTER;
                } else {
                    throw new \Exception('Reference is not provided');
                }
                $query = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Query\Filter',
                    [
                        'name' => $query['name'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'reference' => $reference,
                        'referenceType' => $referenceType
                    ]
                );
                break;
            case QueryInterface::TYPE_BOOL:
                $aggregatedByType = $this->aggregateQueriesByType($query['queryReference']);
                $query = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Query\Bool',
                    array_merge(
                        ['name' => $query['name'], 'boost' => isset($query['boost']) ? $query['boost'] : 1],
                        $aggregatedByType
                    )
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid query type');
        }
        return $query;
    }

    /**
     * Convert array to Filter instance
     *
     * @param string $filterName
     * @throws \Exception
     * @return FilterInterface
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     */
    private function mapFilter($filterName)
    {
        if (!isset($this->filters[$filterName])) {
            throw new \Exception('Filter ' . $filterName . ' does not exist');
        } elseif (in_array($filterName, $this->mappedFilters)) {
            throw new StateException('Cycle found. Filter %1 already used in request hierarchy', [$filterName]);
        }
        $this->mappedFilters[] = $filterName;
        $filter = $this->filters[$filterName];
        switch ($filter['type']) {
            case FilterInterface::TYPE_TERM:
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Term',
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value']
                    ]
                );
                break;
            case FilterInterface::TYPE_RANGE:
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Range',
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'from' => isset($filter['from']) ? $filter['from'] : null,
                        'to' => isset($filter['to']) ? $filter['to'] : null
                    ]
                );
                break;
            case FilterInterface::TYPE_WILDCARD:
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Wildcard',
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value']
                    ]
                );
                break;
            case FilterInterface::TYPE_BOOL:
                $aggregatedByType = $this->aggregateFiltersByType($filter['filterReference']);
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Bool',
                    array_merge(
                        ['name' => $filter['name']],
                        $aggregatedByType
                    )
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid filter type');
        }
        return $filter;
    }

    /**
     * Aggregate Filters by clause
     *
     * @param array $data
     * @return array
     */
    private function aggregateFiltersByType($data)
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value['clause']][$value['ref']] = $this->mapFilter($value['ref']);
        }
        return $list;
    }

    /**
     * Aggregate Queries by clause
     *
     * @param array $data
     * @return array
     */
    private function aggregateQueriesByType($data)
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value['clause']][$value['ref']] = $this->mapQuery($value['ref']);
        }
        return $list;
    }

    /**
     * @return void
     * @throws StateException
     */
    private function validate()
    {
        $this->validateQueries();
        $this->validateFilters();
    }

    /**
     * @return void
     * @throws StateException
     */
    private function validateQueries()
    {
        $this->validateNotUsed($this->queries, $this->mappedQueries, 'Query %1 is not used in request hierarchy');
    }

    /**
     * @param array $elements
     * @param string[] $mappedElements
     * @param string $errorMessage
     * @return void
     * @throws \Magento\Framework\Exception\StateException
     */
    private function validateNotUsed($elements, $mappedElements, $errorMessage)
    {
        $allElements = array_keys($elements);
        $notUsedElements = implode(', ', array_diff($allElements, $mappedElements));
        if (!empty($notUsedElements)) {
            throw new StateException($errorMessage, [$notUsedElements]);
        }
    }

    /**
     * @return void
     * @throws StateException
     */
    private function validateFilters()
    {
        $this->validateNotUsed($this->filters, $this->mappedFilters, 'Filter %1 is not used in request hierarchy');
    }

    /**
     * @return QueryInterface
     */
    public function getRootQuery()
    {
        return $this->rootQuery;
    }

    /**
     * Build BucketInterface[] from array
     *
     * @return array
     * @throws StateException
     */
    public function getBuckets()
    {
        $buckets = array();
        foreach ($this->aggregations as $bucketData) {
            $arguments =
                [
                    'name' => $bucketData['name'],
                    'field' => $bucketData['field'],
                    'metrics' => $this->mapMetrics($bucketData['metric'])
                ];
            switch ($bucketData['type']) {
                case BucketInterface::TYPE_TERM:
                    $bucket = $this->objectManager->create(
                        'Magento\Framework\Search\Request\Aggregation\TermBucket',
                        $arguments
                    );
                    break;
                case BucketInterface::TYPE_RANGE:
                    $bucket = $this->objectManager->create(
                        'Magento\Framework\Search\Request\Aggregation\RangeBucket',
                        array_merge(
                            $arguments,
                            ['ranges' => $this->mapRanges($bucketData['range'])]
                        )
                    );
                    break;
                default:
                    throw new StateException('Invalid bucket type');
            }
            $buckets[] = $bucket;
        }
        return $buckets;
    }

    /**
     * Build Metric[] from array
     *
     * @param array $metrics
     * @return array
     */
    private function mapMetrics(array $metrics)
    {
        $metricObjects = array();
        foreach ($metrics as $metric) {
            $metricObjects[] = $this->objectManager->create(
                'Magento\Framework\Search\Request\Aggregation\Metric',
                [
                    'type' => $metric['type']
                ]
            );
        }
        return $metricObjects;
    }

    /**
     * Build Range[] from array
     *
     * @param array $ranges
     * @return array
     */
    private function mapRanges(array $ranges)
    {
        $rangeObjects = array();
        foreach ($ranges as $range) {
            $rangeObjects[] = $this->objectManager->create(
                'Magento\Framework\Search\Request\Aggregation\Range',
                [
                    'from' => $range['from'],
                    'to' => $range['to']
                ]
            );
        }
        return $rangeObjects;
    }
}
