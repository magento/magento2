<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Phrase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mapper
{
    /**
     * @var QueryInterface
     */
    private $rootQuery;

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $rootQueryName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $queries
     * @param string $rootQueryName
     * @param array $aggregations
     * @param array $filters
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $queries,
        $rootQueryName,
        array $aggregations = [],
        array $filters = []
    ) {
        $this->objectManager = $objectManager;
        $this->queries = $queries;
        $this->aggregations = $aggregations;
        $this->filters = $filters;
        $this->rootQueryName = $rootQueryName;
    }

    /**
     * Get Query Interface by name
     *
     * @return QueryInterface
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     */
    public function getRootQuery()
    {
        if (!$this->rootQuery) {
            $this->mappedQueries = [];
            $this->mappedFilters = [];
            $this->rootQuery = $this->mapQuery($this->rootQueryName);
            $this->validate();
        }
        return $this->rootQuery;
    }

    /**
     * Convert array to Query instance
     *
     * @param string $queryName
     * @return QueryInterface
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws StateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function mapQuery($queryName)
    {
        if (!isset($this->queries[$queryName])) {
            throw new \Exception('Query ' . $queryName . ' does not exist');
        } elseif (in_array($queryName, $this->mappedQueries)) {
            throw new StateException(
                new Phrase('Cycle found. Query %1 already used in request hierarchy', [$queryName])
            );
        }
        $this->mappedQueries[] = $queryName;
        $query = $this->queries[$queryName];
        switch ($query['type']) {
            case QueryInterface::TYPE_MATCH:
                $query = $this->objectManager->create(
                    \Magento\Framework\Search\Request\Query\Match::class,
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
                    \Magento\Framework\Search\Request\Query\Filter::class,
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
                    \Magento\Framework\Search\Request\Query\BoolExpression::class,
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
            throw new StateException(
                new Phrase('Cycle found. Filter %1 already used in request hierarchy', [$filterName])
            );
        }
        $this->mappedFilters[] = $filterName;
        $filter = $this->filters[$filterName];
        switch ($filter['type']) {
            case FilterInterface::TYPE_TERM:
                $filter = $this->objectManager->create(
                    \Magento\Framework\Search\Request\Filter\Term::class,
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value']
                    ]
                );
                break;
            case FilterInterface::TYPE_RANGE:
                $filter = $this->objectManager->create(
                    \Magento\Framework\Search\Request\Filter\Range::class,
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
                    \Magento\Framework\Search\Request\Filter\Wildcard::class,
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
                    \Magento\Framework\Search\Request\Filter\BoolExpression::class,
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
            throw new StateException(new Phrase($errorMessage, [$notUsedElements]));
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
     * Build BucketInterface[] from array
     *
     * @return array
     * @throws StateException
     */
    public function getBuckets()
    {
        $buckets = [];
        foreach ($this->aggregations as $bucketData) {
            $arguments = [
                'name' => $bucketData['name'],
                'field' => $bucketData['field'],
                'metrics' => $this->mapMetrics($bucketData),
            ];
            switch ($bucketData['type']) {
                case BucketInterface::TYPE_TERM:
                    $bucket = $this->objectManager->create(
                        \Magento\Framework\Search\Request\Aggregation\TermBucket::class,
                        $arguments
                    );
                    break;
                case BucketInterface::TYPE_RANGE:
                    $bucket = $this->objectManager->create(
                        \Magento\Framework\Search\Request\Aggregation\RangeBucket::class,
                        array_merge(
                            $arguments,
                            ['ranges' => $this->mapRanges($bucketData)]
                        )
                    );
                    break;
                case BucketInterface::TYPE_DYNAMIC:
                    $bucket = $this->objectManager->create(
                        \Magento\Framework\Search\Request\Aggregation\DynamicBucket::class,
                        array_merge(
                            $arguments,
                            ['method' => $bucketData['method']]
                        )
                    );
                    break;
                default:
                    throw new StateException(new Phrase('Invalid bucket type'));
                    break;
            }
            $buckets[] = $bucket;
        }
        return $buckets;
    }

    /**
     * Build Metric[] from array
     *
     * @param array $bucketData
     * @return array
     */
    private function mapMetrics(array $bucketData)
    {
        $metricObjects = [];
        if (isset($bucketData['metric'])) {
            $metrics = $bucketData['metric'];
            foreach ($metrics as $metric) {
                $metricObjects[] = $this->objectManager->create(
                    \Magento\Framework\Search\Request\Aggregation\Metric::class,
                    [
                        'type' => $metric['type']
                    ]
                );
            }
        }
        return $metricObjects;
    }

    /**
     * Build Range[] from array
     *
     * @param array $bucketData
     * @return array
     */
    private function mapRanges(array $bucketData)
    {
        $rangeObjects = [];
        if (isset($bucketData['range'])) {
            $ranges = $bucketData['range'];
            foreach ($ranges as $range) {
                $rangeObjects[] = $this->objectManager->create(
                    \Magento\Framework\Search\Request\Aggregation\Range::class,
                    [
                        'from' => $range['from'],
                        'to' => $range['to']
                    ]
                );
            }
        }
        return $rangeObjects;
    }
}
