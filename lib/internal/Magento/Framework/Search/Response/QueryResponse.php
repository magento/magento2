<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Search\ResponseInterface;

/**
 * Search Response
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
     * @param Document[] $documents
     * @param AggregationInterface $aggregations
     */
    public function __construct(array $documents, AggregationInterface $aggregations)
    {
        $this->documents = $documents;
        $this->aggregations = $aggregations;
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
}
