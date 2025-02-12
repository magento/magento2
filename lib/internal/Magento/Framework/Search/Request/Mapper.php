<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Request;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\Aggregation\Metric;
use Magento\Framework\Search\Request\Aggregation\RangeBucket;
use Magento\Framework\Search\Request\Aggregation\TermBucket;
use Magento\Framework\Search\Request\Filter\Range;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Filter\Wildcard;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\Query\MatchQuery;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $rootQueryName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $queries
     * @param string $rootQueryName
     * @param array $aggregations
     * @param array $filters
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws StateException
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
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
     * @throws Exception
     * @throws InvalidArgumentException
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
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws StateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function mapQuery($queryName)
    {
        if (!isset($this->queries[$queryName])) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception('Query ' . $queryName . ' does not exist');
        } elseif (in_array($queryName, $this->mappedQueries)) {
            throw new StateException(new Phrase(
                'A cycle was found. The "%1" query is already used in the request hierarchy.',
                [$queryName]
            ));
        }
        $this->mappedQueries[] = $queryName;
        $query = $this->queries[$queryName];
        switch ($query['type']) {
            case QueryInterface::TYPE_MATCH:
                $query = $this->objectManager->create(
                    MatchQuery::class,
                    [
                        'name' => $query['name'],
                        'value' => $query['value'],
                        'boost' => $query['boost'] ?? 1,
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
                    // phpcs:ignore Magento2.Exceptions.DirectThrow
                    throw new Exception('Reference is not provided');
                }
                $query = $this->objectManager->create(
                    Filter::class,
                    [
                        'name' => $query['name'],
                        'boost' => $query['boost'] ?? 1,
                        'reference' => $reference,
                        'referenceType' => $referenceType
                    ]
                );
                break;
            case QueryInterface::TYPE_BOOL:
                $aggregatedByType = $this->aggregateQueriesByType($query['queryReference']);
                $query = $this->objectManager->create(
                    BoolExpression::class,
                    array_merge(
                        ['name' => $query['name'], 'boost' => $query['boost'] ?? 1],
                        $aggregatedByType
                    )
                );
                break;
            default:
                throw new InvalidArgumentException('Invalid query type');
        }
        return $query;
    }

    /**
     * Convert array to Filter instance
     *
     * @param string $filterName
     * @throws Exception
     * @return FilterInterface
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws StateException
     */
    private function mapFilter($filterName)
    {
        if (!isset($this->filters[$filterName])) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception('Filter ' . $filterName . ' does not exist');
        } elseif (in_array($filterName, $this->mappedFilters)) {
            throw new StateException(
                new Phrase(
                    'A cycle was found. The "%1" filter is already used in the request hierarchy.',
                    [$filterName]
                )
            );
        }
        $this->mappedFilters[] = $filterName;
        $filter = $this->filters[$filterName];
        switch ($filter['type']) {
            case FilterInterface::TYPE_TERM:
                $filter = $this->objectManager->create(
                    Term::class,
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value']
                    ]
                );
                break;
            case FilterInterface::TYPE_RANGE:
                $filter = $this->objectManager->create(
                    Range::class,
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'from' => $filter['from'] ?? null,
                        'to' => $filter['to'] ?? null
                    ]
                );
                break;
            case FilterInterface::TYPE_WILDCARD:
                $filter = $this->objectManager->create(
                    Wildcard::class,
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
                throw new InvalidArgumentException('Invalid filter type');
        }
        return $filter;
    }

    /**
     * Aggregate Filters by clause
     *
     * @param array $data
     * @return array
     * @throws StateException
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
     * @throws StateException
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
     * Validate queries and filters.
     *
     * @return void
     * @throws StateException
     */
    private function validate()
    {
        $this->validateQueries();
        $this->validateFilters();
    }

    /**
     * Check if queries are not used.
     *
     * @return void
     * @throws StateException
     */
    private function validateQueries()
    {
        $this->validateNotUsed(
            $this->queries,
            $this->mappedQueries,
            'Query %1 is not used in request hierarchy'
        );
    }

    /**
     * Validate elements that are not used.
     *
     * @param array $elements
     * @param string[] $mappedElements
     * @param string $errorMessage
     * @return void
     * @throws StateException
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
     * Check if filters are not used.
     *
     * @return void
     * @throws StateException
     */
    private function validateFilters()
    {
        $this->validateNotUsed(
            $this->filters,
            $this->mappedFilters,
            'Filter %1 is not used in request hierarchy'
        );
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
                    $arguments['parameters'] = array_column($bucketData['parameter'] ?? [], 'value', 'name');
                    $bucket = $this->objectManager->create(TermBucket::class, $arguments);
                    break;
                case BucketInterface::TYPE_RANGE:
                    $arguments['ranges'] = $this->mapRanges($bucketData);
                    $bucket = $this->objectManager->create(RangeBucket::class, $arguments);
                    break;
                case BucketInterface::TYPE_DYNAMIC:
                    $arguments['method'] = $bucketData['method'];
                    $bucket = $this->objectManager->create(DynamicBucket::class, $arguments);
                    break;
                default:
                    throw new StateException(new Phrase('The bucket type is invalid. Verify and try again.'));
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
                    Metric::class,
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
                    Aggregation\Range::class,
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
