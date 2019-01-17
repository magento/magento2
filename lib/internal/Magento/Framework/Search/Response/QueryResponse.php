<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Search\ResponseInterface;

/**
 * Search Response
 * @api
 */
class QueryResponse implements ResponseInterface
{
    /**
     * Document Collection
     *
     * @var Document[]
     */
    protected $documents;

    /**
     * Aggregation Collection
     *
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * Total count of collection
     *
     * @var int
     */
    protected $totalCount;

    /**
     * @param Document[] $documents
     * @param AggregationInterface $aggregations
     */
    public function __construct(array $documents, AggregationInterface $aggregations, int $size = 0)
    {
        $this->documents = $documents;
        $this->aggregations = $aggregations;
        $this->totalCount = $size;
    }

    /**
     * Countable: return count of fields in document
     * @return int
     */
    public function count()
    {
        return count($this->documents);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->documents);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
